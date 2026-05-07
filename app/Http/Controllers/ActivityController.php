<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoadActivityRequest;
use App\Models\ActivityCode;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\UtilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;





use Exception;

class ActivityController extends Controller
{


    //temporary workaround for Lani Layco's access to activity logs
    public $allowedlevelOneUserId = [3070];




    public function index(Request $request)
    {

        try {


            return view('admin.activities');
        } catch (Exception $e) {

            Log::error('Failed to load activity logs: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e
            ]);


            return view('errors.response', ['code' => 500, 'message' => $e->getMessage()]);
        }
    }



    //done 2021-09-04

    public function load_users(Request $request)
    {

        try {

            $arrUsers = [];

            $users = User::with(['stockholderAccount',  'nonMemberAccount' => function ($query) {
                $query->withTrashed();
            }, 'adminAccount' => function ($query) {
                $query->withTrashed();
            }, 'stockholder'])->selectRaw('users.id, users.role')->get();

            foreach ($users as $user) {

                $name = 'def';
                $accountNo = 'defAccountNo';
                $suffix = '';


                switch ($user->role) {

                    case 'superadmin':
                        $name = $user->adminAccount->firstName ?? '';
                        $displayName = $name;
                        break;

                    case 'admin':

                        $name = $user->adminAccount->firstName . ' ' . $user->adminAccount->lastName;
                        $displayName = $name;

                        break;

                    case 'stockholder':

                        $name = $user->stockholder->stockholder;
                        $accountNo = $user->stockholder->accountNo;
                        $displayName = $accountNo . ' - ' . $name;
                        break;

                    case 'corp-rep':

                        $name = $user->stockholderAccount->corpRep;
                        $accountNo = $user->stockholderAccount->stockholder->accountNo;
                        $suffix = $user->stockholderAccount->suffix;
                        $displayName = $accountNo . '-' . $suffix . ' - ' . $name;

                        break;

                    case 'non-member':

                        $name = $user->nonMemberAccount->firstName . ' ' . $user->nonMemberAccount->lastName;
                        $accountNo = $user->nonMemberAccount->nonmemberAccountNo;
                        $displayName = $accountNo . ' - ' . $name;

                        break;
                }

                $arrUsers[] = array(
                    'id' => $user->id,
                    'role' => $user->role,
                    'name' => $name,
                    'accountNo' => $accountNo,
                    'suffix' => $suffix,
                    'displayName' => $displayName
                );
            }

            $activityCodes = ActivityCode::selectRaw('DISTINCT UPPER(category) AS category, UPPER(action) AS action')
                ->orderBy('category', 'ASC')
                ->orderBy('action', 'ASC')
                ->get();

            $categories = $activityCodes->pluck('category')->unique();
            $actions = $activityCodes->pluck('action')->unique();


            return array('users' => $arrUsers, 'categories' => $categories, 'actions' => $actions);
        } catch (Exception $e) {

            Log::error('Failed to load users for activity logs: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e
            ]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    public function load_activity(LoadActivityRequest $request)
    {

        try {

            // Log::channel('evoting')->info('Loaded activity logs', ['id' => Auth::user()->id, 'user' => Auth::user()->adminAccount->firstName . ' ' . Auth::user()->adminAccount->lastName]);

            if (Auth::user()->level == 1) {

                return response()->json([], 403);
            }


            $validator = Validator::make($request->all(), [
                'user'              => 'nullable|numeric|integer',
                'account_type'      => 'nullable|string|in:corp,indv',
                'role'              => 'nullable|string|in:stockholder,corp-rep,non-member',
                'category'          => 'nullable|string|max:50',
                'action'            => 'nullable|string|max:50',
                'per_page'          => 'nullable|numeric|integer|min:1|max:500'
            ]);



            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());

                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            DB::enableQueryLog();

            //variable filters
            $userId = $request->user;
            $accountType = $request->account_type;
            $role = $request->role;
            $category = $request->category;
            $action = $request->action;

            $logs = ActivityLog::with([
                'user:id,email,role',
                'user.stockholder',
                'user.stockholderAccount.stockholder',
                'user.adminAccount',
                'user.nonMemberAccount'
            ])->leftJoin('users', 'users.id', '=', 'activity_logs.createdBy')
                ->leftJoin('candidates', 'candidates.candidateId', '=', 'activity_logs.candidateId')
                ->leftJoin('stockholders', 'stockholders.userId', '=', 'users.id')
                ->leftJoin('stockholder_accounts', 'stockholder_accounts.userId', '=', 'users.id')
                ->leftJoin('admin_accounts', 'admin_accounts.userId', '=', 'users.id')
                ->leftJoin('nonmember_accounts', 'nonmember_accounts.userId', '=', 'users.id')
                ->leftJoin('activity_codes', 'activity_codes.activityCode', '=', 'activity_logs.activityCode')
                ->leftJoin('documents', 'documents.documentId', '=', 'activity_logs.documentId')
                ->selectRaw(
                    '
                                activity_logs.logId, activity_logs.activityCode, activity_logs.remarks, activity_logs.userId, activity_logs.ballotId, activity_logs.accountNo, activity_logs.email, activity_logs.ip, activity_logs.createdAt, DATE_FORMAT(activity_logs.createdAt, "%Y-%m-%d %H:%i:%s") AS createdAtFormatted, activity_logs.data,
                                activity_codes.activity, activity_codes.action, activity_codes.category, activity_codes.severity,
                                users.id, users.role, 
                                CONCAT(candidates.firstName, " ", candidates.lastName) as candidateName, 
                                stockholders.stockholder,
                                CONCAT(nonmember_accounts.firstName, " ", nonmember_accounts.lastName) as nonmemberName,
                                CONCAT(admin_accounts.firstName, " ", admin_accounts.lastName) as adminName,
                                stockholder_accounts.corpRep, stockholder_accounts.accountKey,
                                documents.title, documents.documentId
                                '

                )
                ->when($userId !== null, function ($query) use ($userId) {
                    return $query->where('activity_logs.userId', $userId);
                })
                ->when($accountType !== null, function ($query) use ($accountType) {
                    return $query->where(function ($query) use ($accountType) {
                        $query->whereHas('user.stockholder', function ($subquery) use ($accountType) {
                            $subquery->where('accountType', $accountType);
                        })->orWhereHas('user.stockholderAccount.stockholder', function ($subquery) use ($accountType) {
                            $subquery->where('accountType', $accountType);
                        });
                    });
                })
                ->when($role !== null, function ($query) use ($role) {

                    return $query->whereHas('user', function ($subquery) use ($role) {
                        $subquery->where('role', $role);
                    });
                })
                ->when($category !== null, function ($query) use ($category) {
                    return $query->where('activity_codes.category', $category);
                })
                ->when($action !== null, function ($query) use ($action) {
                    return $query->where('activity_codes.action', $action);
                })
                ->when(!Auth::user()->hasRole('superadmin'), function ($query) {
                    // Exclude ballot summary activities for non-superadmin users
                    $excludedCodes = ['00071', '00072', '00090', '00091']; // Add more codes as needed
                    return $query->whereNotIn('activity_logs.activityCode', $excludedCodes);
                })
                ->orderBy('logId', 'DESC')
                ->paginate($request->per_page);


            // ActivityController::log(
            //     [
            //         'activityCode' => '00022',
            //         'userId' => Auth::user()->id,
            //     ]
            // );

            return response()->json(['data' => $logs]);
        } catch (Exception $e) {
            UtilityService::logServerError($request, $e, 'Failed to load activity logs');

            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public static function log($data)
    {

        try {

            $data['ip']         = request()->ip();
            $data['createdBy']  = Auth::check() === true ? Auth::user()->id : null;
            $data['createdAt']  = EApp::datetime();
            $data['email']      = array_key_exists('email', $data) ? $data['email'] : (Auth::check() ? Auth::user()->email : null);
            // $data['accountNo']  = array_key_exists('accountNo', $data) ? $data['accountNo'] : (Auth::check() ? Auth::user()->accountNo : null);
            // $data['accountId']  = array_key_exists('accountId', $data) ? $data['accountId'] : (Auth::check() ? ($authUser->stockholderAccount === null ? null : $authUser->stockholderAccount->accountId) : null);

            ActivityLog::insert($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
