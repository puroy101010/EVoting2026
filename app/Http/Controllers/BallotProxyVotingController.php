<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

use App\Models\Ballot;
use App\Models\ProxyAmendment;
use App\Models\ProxyBoardOfDirector;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Candidate;
use App\Models\Amendment;
use App\Models\Agenda;
use Illuminate\Validation\Rule;
use App\Models\BallotConfirmation;
use Carbon\Carbon;
use App\Models\BallotDetail;
use App\Models\BallotAgenda;
use App\Models\BallotAmendment;
use App\Models\UsedBoardOfDirectorAccount;
use App\Models\UsedAmendmentAccount;
use App\Models\Attendance;


class BallotProxyVotingController extends Controller
{

    private $ballotInfo;
    private $availableVotes;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }



    public function ballot($id)
    {

        try {

            $ballotInfo = Ballot::where('isSubmitted', false)->where('ballotType', 'proxy')->where('isViewed', false)->first();

            if ($ballotInfo === null) {

                return redirect('user');
            }

            $userInfo = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')->findOrFail(Auth::id());


            $ballotInfo->isViewed = true;
            $ballotInfo->save();

            return view('user.proxy-voting-form', [

                'ballotId' => $ballotInfo->ballotId,
                'ballotNo' => $ballotInfo->ballotNo,
                'userInfo' => $userInfo,
                'availableVotesBod' => $ballotInfo->availableVotesBod,
                'availableVotesAmendment' => $ballotInfo->availableVotesAmendment,
                'availableSharesBod' => count(json_decode($ballotInfo->availableBodAccounts)),
                'availableSharesAmendment' => count(json_decode($ballotInfo->availableAmendmentAccounts)),
                'candidates'    => Candidate::orderBy('type', 'asc')->orderBy('lastName', 'asc')->get(),
                'amendments'    => Amendment::all(),
                'agendas'    => Agenda::all()

            ]);
        } catch (Exception $e) {


            Log::critical($e);
        }
    }


    public function getAvailableVotes()
    {

        try {


            $user = Auth::user();

            $userInfo = User::with(['stockholder', 'stockholderAccount', 'stockholderAccount.stockholder'])
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->findOrFail(Auth::id());


            switch ($user->role) {
                case 'stockholder':

                    $availableVotesBod = ProxyBoardOfDirector::where('assigneeId', $userInfo->id)
                        ->whereDoesntHave('usedAccount')
                        ->whereHas('stockholderAccount', function ($query) {
                            $query->where('isDelinquent', 0);
                        })
                        ->pluck('accountId');

                    $availableVotesAmendment = [];

                    break;

                case 'corp-rep':

                    $corpRepAccounts = User::leftJoin('stockholder_accounts', 'stockholder_accounts.userId', '=', 'users.id')
                        ->leftJoin('stockholders', 'stockholders.stockholderId', '=', 'stockholder_accounts.stockholderId')
                        ->selectRaw('stockholder_accounts.accountId, users.id')
                        ->where('users.email', Auth::user()->email)
                        ->where('stockholders.accountNo', Auth::user()->stockholderAccount->stockholder->accountNo)
                        ->get();


                    $assigneeAccountIds = [];

                    foreach ($corpRepAccounts as $corpRepAccount) {

                        array_push($assigneeAccountIds, $corpRepAccount->id);
                    }


                    $availableVotesBod   = ProxyBoardOfDirector::whereIn('assigneeId', $assigneeAccountIds)
                        ->whereDoesntHave('usedAccount')
                        ->whereHas('stockholderAccount', function ($query) {
                            $query->where('isDelinquent', 0);
                        })
                        ->pluck('accountId');


                    $availableVotesAmendment = [];

                    break;

                case 'non-member':
                    $availableVotesBod = ProxyBoardOfDirector::where('assigneeId', $userInfo->id)
                        ->whereDoesntHave('usedAccount')
                        ->whereHas('stockholderAccount', function ($query) {
                            $query->where('isDelinquent', 0);
                        })
                        ->pluck('accountId');


                    $availableVotesAmendment = [];


                    if ($userInfo->nonMemberAccount->isGM === 1) {
                        $availableVotesAmendment = ProxyAmendment::where('assigneeId', $userInfo->id)
                            ->whereDoesntHave('usedAccount')
                            ->whereHas('stockholderAccount', function ($query) {
                                $query->where('isDelinquent', 0);
                            })
                            ->pluck('accountId');
                    }

                    break;
            }



            return array(
                'bod' => $availableVotesBod->toArray(),
                'amendment' => is_array($availableVotesAmendment) ? [] : $availableVotesAmendment->toArray()
            );
        } catch (Exception $e) {

            throw new Exception($e);
        }
    }



    public function generateBallot()
    {

        $ballotId = EApp::generate_id('ballots', 'ballotId');

        $lastBallotNo = Ballot::selectRaw('MAX(CAST(trim(LEADING "0" FROM ballotNo) AS UNSIGNED)) AS lastBallotNo')
            ->where('ballotType', 'proxy')
            ->first();

        $ballotNo = $lastBallotNo === null ? '0001' : $lastBallotNo->lastBallotNo + 1;

        $ballotNo = str_pad($ballotNo, 4, "0", STR_PAD_LEFT);



        return [
            'ballotNo' => $ballotNo,
            'ballotId' => $ballotId
        ];
    }




    public function getSummaryDetailsBod($data)
    {

        $arrayColumns = array_column($data['bod'], 'candidateId');

        $candidates = Candidate::get();

        $candidateArr = [];

        foreach ($candidates as $candidate) {

            $key = array_search($candidate->candidateId, $arrayColumns);

            $candidateArr[] = array(

                'name' => $candidate->firstName . ' ' . $candidate->lastName,
                'vote' => $data['bod'][$key]['vote']

            );
        }


        return $candidateArr;
    }





    public function getSummaryDetailsAmendment($data)
    {

        $arrayColumns = array_column($data['amendment'], 'amendmentId');

        $amendments = Amendment::get();

        $amendmentArr = [];

        foreach ($amendments as $amendment) {

            $key = array_search($amendment->amendmentId, $arrayColumns);

            $amendmentArr[] = array(

                'amendment' => $amendment->amendmentDesc,
                'vote' => $data['amendment'][$key]

            );
        }


        return $amendmentArr;
    }





    public function getSummaryDetailsAgenda($data)
    {

        $arrayColumns = array_column($data['agenda'], 'agendaId');

        $agendas = Agenda::get();

        $agendataArr = [];

        foreach ($agendas as $agenda) {

            $key = array_search($agenda->agendaId, $arrayColumns);

            $agendataArr[] = array(

                'agenda' => $agenda->agendaDesc,
                'vote' => $data['agenda'][$key]

            );
        }


        return $agendataArr;
    }



    function insertConfirmation($data, $isValidBallot)
    {

        $confirmationId = EApp::generate_id('ballot_confirmations', 'confirmationId');

        BallotConfirmation::insert([
            'confirmationId' => $confirmationId,
            'ballotId'       => $this->ballotInfo->ballotId,
            'ballotType'        => 'proxy',
            'isValidBallot'     => $isValidBallot,
            'data'              => json_encode($data),
            'availableVotes'    => json_encode($this->availableVotes),
            'createdBy'         => Auth::id()

        ]);


        return $confirmationId;
    }


    public function validConfimation($unusedVotes, $confirmation, $data)
    {


        $message = 'You are about to submit the ballot. Once confirmed, it will be submitted and cannot be undone. A confirmation email will be sent to your registered email address.';


        if ($unusedVotes > 0) {

            $message = "You still have $unusedVotes vote(s) that haven't been distributed. If you confirm, the form will be submitted, and the remaining undistributed votes will be marked as unused votes and will no longer be available.";
        }


        $data = array_merge(['message' => $message], $data);


        if (json_encode($data) !== $confirmation->data) {


            // Log::debug(json_encode($data));
            // Log::debug($confirmation->data);

            $message = 'The user tried to submit a ballot, but the votes did not match the confirmation data.';

            ActivityController::log([
                'activityCode' => '00093',
                'remarks' => $message,
                'ballotId' => $this->ballotInfo->ballotId
            ]);

            return $message;
        }


        return true;
    }






    public function submit(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'ballotId' => 'required',
                'confirmationId' => 'required',

                'amendment' => config('evoting.enableAmendment') === true ? 'required|array' : 'nullable|array',
                'amendment.*.amendmentId' => ['required', 'integer', Rule::exists('amendments')->whereNull('deletedAt')],
                'amendment.*.yes' => ['nullable', 'boolean'],
                'amendment.*.no' => ['nullable', 'boolean'],

                'agenda' => 'required|array',
                'agenda.*.agendaId' => ['required', 'integer', Rule::exists('agendas')->whereNull('deletedAt')],
                'agenda.*.favor' => ['nullable', 'boolean'],
                'agenda.*.notFavor' => ['nullable', 'boolean'],
                'agenda.*.abstain' => ['nullable', 'boolean'],

                'bod' => 'required|array',
                'bod.*.candidateId' => ['required', 'integer', Rule::exists('candidates')->whereNull('deletedAt')],
                'bod.*.vote' => ['nullable', 'integer']

            ]);


            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());
                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }



            $this->setBallotPropery($request->ballotId);

            $loggerDetails = ['id' => Auth::id(), 'email' => Auth::user()->email, 'ballotId' => $request->ballotId];

            Log::channel('ballot')->info('Submitting ballot', $loggerDetails);

            $data  = array('bod' => $request->input('bod'),  'amendment' => $request->amendment, 'agenda' => $request->agenda);




            $checkDate = $this->proxyVotingDay(['ballotId' => $request->ballotId, 'activityCode' => '00081'], 'Stockholder Online');

            if ($checkDate !== true) {

                Log::channel('ballot')->info($checkDate, $loggerDetails);
                return response()->json(['message' => $checkDate], 400);
            }


            $checkRecordChanges = $this->checkRecordChanges($data, '00085');

            if ($checkRecordChanges !== true) {

                Log::channel('ballot')->info('There have been changes to the accounts. Please refresh the page.', $loggerDetails);
                return response()->json(['message' => 'There have been changes to the accounts. Please refresh the page.'], 400);
            }

            $confirmation = BallotConfirmation::where('isValidBallot', true)->findOrFail($request->confirmationId);

            if ((config('evoting.enableAmendment') === true)) {
                $this->validateAmendmentForm($request->amendment);
            }

            $this->validateAgendaForm($request->agenda);

            $totalBodVotes = $this->getTotalBodVotes($request->bod); // total votes submitted by the user
            $unusedVotes = $this->ballotInfo->availableVotesBod - $totalBodVotes;

            $validDistributedVotes = $this->validateDistributedVotes($unusedVotes, $totalBodVotes, $data);


            if ($validDistributedVotes !== true) {

                return response()->json(['message' => $validDistributedVotes], 400);
            }


            // this is used in casting votes only            
            if ($this->validConfimation($unusedVotes, $confirmation, $data) !== true) {

                return response()->json(['message' => EApp::SERVER_ERROR], 500);
            }

            DB::beginTransaction();

            $this->update($request->confirmationId, $unusedVotes);




            $insertBod = [];

            foreach ($request->bod as $bod) {

                if ($bod['vote'] < 0) {
                    return response()->json(['message' => 'Vote is considered invalid if it\'s not a positive number'], 400);
                }


                $insertBod[] = array(
                    'vote' => $bod['vote'],
                    'candidateId' => $bod['candidateId'],
                    'ip' => $request->ip(),
                    'ballotId' => $this->ballotInfo->ballotId,
                    'createdBy' => Auth::id()
                );
            }


            $insertAmendment = [];

            if (config('evoting.enableAmendment') === true) {

                foreach ($request->amendment as $amendment) {

                    if ((int) $amendment['yes'] ===  0 && (int) $amendment['no'] === 0) {
                        continue;
                    }

                    $insertAmendment[] = array(
                        'yes' => $amendment['yes'],
                        'no' => $amendment['no'],
                        'amendmentId' => $amendment['amendmentId'],
                        'ballotId' => $this->ballotInfo->ballotId,
                        'createdBy' => Auth::id()
                    );
                }
            }



            $insertAgenda = [];

            foreach ($request->agenda as $agenda) {

                if ((int) $agenda['favor'] ===  0 && (int) $agenda['notFavor'] === 0 && (int) $agenda['abstain'] === 0) {
                    continue;
                }

                $insertAgenda[] = array(
                    'favor' => $agenda['favor'],
                    'notFavor' => $agenda['notFavor'],
                    'abstain' => $agenda['abstain'],
                    'agendaId' => $agenda['agendaId'],
                    'ballotId' => $this->ballotInfo->ballotId,
                    'createdBy' => Auth::id()
                );
            }



            if (count($insertBod) === 0 && count($insertAmendment) === 0 && count($insertAgenda) === 0) {

                return response()->json(['message' => "No available votes. Please contact your admin."]);
            }

            BallotDetail::insert($insertBod);
            BallotAmendment::insert($insertAmendment);
            BallotAgenda::insert($insertAgenda);
            // UsedAccountBod::insert();



            if ($this->insertUsedAccounts($confirmation) !== true) {

                return response()->json([], 500);
            }

            Log::channel('ballot')->info('Ballot submitted', $loggerDetails);


            ActivityController::log(['activityCode' => '00095', 'ballotId' => $this->ballotInfo->ballotId, 'userId' => Auth::id()]);



            DB::commit();

            if (config('evoting.enableSendVoteConfirmation') === true) {

                VotingConfirmationController::Send_Voting_Confirmation(Auth::user()->email);

                Log::channel('ballot')->info('Email confirmation has been sent', $loggerDetails);
            }


            return response()->json([
                'message' => 'Your vote has been successfully submited.'
            ], 200);
        } catch (Exception $e) {

            Log::channel('ballot')->critical($e->getMessage());
            Log::critical($e);
            return response()->json([], 500);
        }
    }

    public function checkRecordChanges($userSubmittedData, $activityCode)
    {

        $availableAccounts =  $this->getAvailableVotes();


        if ($this->ballotInfo->availableAccounts !== json_encode($availableAccounts)) {

            $message = 'There have been changes to the accounts. Please refresh the page.';

            $confirmationId = $this->insertConfirmation(array_merge(array(['message' => $message]), $userSubmittedData), false);

            ActivityController::log([
                'activityCode' => $activityCode,
                'remarks' => $message,
                'ballotId' => $this->ballotInfo->ballotId,
                'confirmationId' =>  $confirmationId

            ]);


            return $message;
        }


        return true;
    }


    public function validateAmendmentForm($amendmentData)
    {

        foreach ($amendmentData as $amendment) {

            $selectedVotes = (int) $amendment['yes'] + $amendment['no'];

            if ($selectedVotes > 1) {



                throw new Exception('Form validation error: Each amendment must have only one vote, either "yes" or "no", but not both.');
            }
        }

        return true;
    }



    public function validateAgendaForm($agendaData)
    {

        if (count($this->availableVotes['bod']) > 0) {

            foreach ($agendaData as $agenda) {

                $selectedVotes = (int) $agenda['favor'] + (int) $agenda['notFavor'] + (int) $agenda['abstain'];

                if ($selectedVotes !== 1) {

                    throw new Exception('Form validation error: Each agenda must have only one vote, either "favor", "not favor", or "abstain", but not multiple.');
                }
            }
        }

        return true;
    }



    public function getTotalBodVotes($bodData)
    {

        $totalBodVotes = 0;

        foreach ($bodData as $bod) {

            $totalBodVotes += (int)$bod['vote'];
        }


        return $totalBodVotes;
    }


    public function validateDistributedVotes($unusedVotes, $totalBodVotes, $data)
    {

        if ($unusedVotes < 0) {

            $message = "The total distributed votes should not exceed $totalBodVotes votes.";

            $confirmationId = $this->insertConfirmation(array_merge(array('message' => $message), $data), false);

            ActivityController::log(['activityCode' => '00091', 'remarks' => $message, 'ballotId' => $this->ballotInfo->ballotId, 'confirmationId' => $confirmationId]);

            return $message;
        }


        return true;
    }




    public function proxyVotingDay($log, $formType)
    {

        $currentDateTime = Carbon::now();

        $setting = AppController::app_setting();

        $startDateTime = $setting['vote_by_proxy_start'];
        $endDateTime = $setting['vote_by_proxy_end'];

        if ($startDateTime === null || $endDateTime === null) {
            $message = 'The administrator has yet to set the voting period for Proxy Voting. If you think this is an error, please contact your admin.';

            $insertLog = [

                'remarks' => $message,

            ];
            ActivityController::log(array_merge($insertLog, $log));

            return $message;
        }


        // Log::info('proxyVotingDay method', [
        //     'start' => $startDateTime,
        //     'end' => $endDateTime,
        //     'now' => $currentDateTime
        // ]);

        if (!$currentDateTime->between(Carbon::parse($startDateTime), Carbon::parse($endDateTime))) {

            $formattedStartDateTime = Carbon::parse($startDateTime)->format('F j, Y, \a\t g:i A');
            $formattedEndDateTime = Carbon::parse($endDateTime)->format('F j, Y, \a\t g:i A');

            if (Carbon::parse($startDateTime) > $currentDateTime) {

                $message = "The $formType Voting period will begin on $formattedStartDateTime and continue until $formattedEndDateTime.";
            }

            if (Carbon::parse($endDateTime) < $currentDateTime) {

                $formattedEndDateTime = Carbon::parse($endDateTime)->format('F j, Y, \a\t g:i A');

                $message = "The period for $formType Voting ended on $formattedEndDateTime";
            }



            $insertLog = [

                'remarks' => $message,

            ];
            ActivityController::log(array_merge($insertLog, $log));

            return $message;
        }

        return true;
    }

    public function recordUnusedVotes($unusedVotes, $userSubmittedDate)
    {

        $message = "You still have $unusedVotes vote(s) that haven't been distributed. If you confirm, the form will be submitted, and the remaining undistributed votes will be marked as unused votes and will no longer be available.";

        $confirmationId = $this->insertConfirmation(array_merge(array('message' => $message), $userSubmittedDate), true);

        ActivityController::log([
            'activityCode' => '00091',
            'remarks' => $message,
            'ballotId' => $this->ballotInfo->ballotId,
            'confirmationId' =>  $confirmationId

        ]);

        return $confirmationId;
    }


    public function setBallotPropery($ballotId)
    {

        $this->ballotInfo =  Ballot::where('isSubmitted', false)->where('ballotType', 'proxy')->where('createdBy', Auth::id())->findOrFail($ballotId);

        $this->availableVotes = $this->getAvailableVotes();
    }


    public function summary(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'ballotId' => 'required',
                'amendment' => config('evoting.enableAmendment') === true ? 'required|array' : 'nullable|array',
                'amendment.*.amendmentId' => ['required', 'integer', Rule::exists('amendments')->whereNull('deletedAt')],
                'amendment.*.yes' => ['nullable', 'boolean'],
                'amendment.*.no' => ['nullable', 'boolean'],

                'agenda' => 'required|array',
                'agenda.*.agendaId' => ['required', 'integer', Rule::exists('agendas')->whereNull('deletedAt')],
                'agenda.*.favor' => ['nullable', 'boolean'],
                'agenda.*.notFavor' => ['nullable', 'boolean'],
                'agenda.*.abstain' => ['nullable', 'boolean'],

                'bod' => 'required|array',
                'bod.*.candidateId' => ['required', 'integer', Rule::exists('candidates')->whereNull('deletedAt')],
                'bod.*.vote' => ['nullable', 'integer']

            ]);


            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());
                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            $this->setBallotPropery($request->ballotId);

            $loggerDetails = ['id' => Auth::id(), 'email' => Auth::user()->email, 'ballotId' => $request->ballotId];



            $data  = array('bod' => $request->input('bod'),  'amendment' => $request->amendment, 'agenda' => $request->agenda);
            Log::channel('ballot')->info('Requesting summary', array_merge($loggerDetails, ['data' => $data]));


            $checkDate = $this->proxyVotingDay(['ballotId' => $request->ballotId, 'activityCode' => '00080'], 'Proxy Voting');

            if ($checkDate !== true) {

                return response()->json(['message' => $checkDate, 'loadUrl' => 'user'], 400);
            }


            foreach ($request->bod as $bod) {

                if ($bod['vote'] < 0) {

                    Log::channel('ballot')->warning('Vote is considered invalid if it\'s not a positive number', array_merge($loggerDetails, ['data' => $data]));

                    return response()->json(['message' => 'Vote is considered invalid if it\'s not a positive number'], 400);
                }
            }

            // Generate a summary to be displayed on the user's screen
            $bodSummary = $this->getSummaryDetailsBod($data);



            $amendmentSummary = [];
            if (config('evoting.enableAmendment') === true) {

                $amendmentSummary = $this->getSummaryDetailsAmendment($data);
            }



            $agendaSummary = $this->getSummaryDetailsAgenda($data);


            $summary = array('bod' => $bodSummary, 'amendment' => $amendmentSummary, 'agenda' => $agendaSummary); //summary from the submitted data


            $checkRecordChanges = $this->checkRecordChanges($data, '00088');

            if ($checkRecordChanges !== true) {

                $msg = 'There have been changes to the accounts. Please refresh the page.';
                Log::channel('ballot')->warning($msg, $loggerDetails);
                ActivityController::log(['activityCode' => '00091', 'remarks' => $msg, 'ballotId' => $request->ballotId]);

                return response()->json(['message' => $msg, 'loadUrl' => 'user'], 400);
            }


            if ((config('evoting.enableAmendment') === true)) {
                $this->validateAmendmentForm($request->amendment);
            }


            $this->validateAgendaForm($request->agenda);



            $totalBodVotes = $this->getTotalBodVotes($request->bod); // total votes submitted by the user
            $unusedVotes = $this->ballotInfo->availableVotesBod - $totalBodVotes;



            $validDistributedVotes = $this->validateDistributedVotes($unusedVotes, $totalBodVotes, $data);


            if ($validDistributedVotes !== true) {

                return response()->json(['message' => $validDistributedVotes], 400);
            }

            if ($unusedVotes > 0) {

                $message = "You still have $unusedVotes vote(s) that haven't been distributed. If you confirm, the form will be submitted, and the remaining undistributed votes will be marked as unused votes and will no longer be available.";

                $response = array_merge(
                    [
                        'message' => $message,
                        'bodVotes' => $totalBodVotes,
                        'unusedVotes' => $unusedVotes,
                        'confirmationId' => $this->recordUnusedVotes($unusedVotes, $data)
                    ],
                    $summary
                );

                return response()->json($response, 200);
            }



            $message = 'You are about to submit the ballot. Once confirmed, it will be submitted and cannot be undone. A confirmation email will be sent to your registered email address.';

            $confirmationId = $this->insertConfirmation(array_merge(array('message' => $message), $data), true);

            Log::channel('ballot')->info($message, ['id' => Auth::user()->id, 'email' => Auth::user()->email, 'ballotId' => $request->ballotId]);
            Log::channel('ballot')->info('Summary for proxy voting has been provided', $loggerDetails);

            ActivityController::log(['activityCode' => '00091', 'remarks' => $message, 'confirmationId' => $confirmationId, 'ballotId' => $request->ballotId]);

            return response()->json([

                'bod' => $bodSummary,
                'amendment' => $amendmentSummary,
                'agenda' => $agendaSummary,
                'bodVotes' => $totalBodVotes,
                'unusedVotes' => $unusedVotes,
                'confirmationId' => $confirmationId,
                'message' => $message
            ]);
        } catch (Exception $e) {


            Log::critical($e);
            return response()->json([], 500);
        }
    }

    public function store(Request $request)
    {
        try {


            $loggerDetails =  ['id' => Auth::user()->id, 'email' => Auth::user()->email];

            Log::channel('ballot')->info('Requested ballot form for proxy voting', $loggerDetails);

            $setting = AppController::app_setting();

            $userInfo   = User::with('stockholder', 'stockholderAccount', 'stockholderAccount.stockholder')->findOrFail(Auth::id());

            $checkBallotEmail = Ballot::where('email', $userInfo->email)->where('ballotType', 'proxy')->where('isSubmitted', true);


            if ($checkBallotEmail->exists()) {

                $msg = "Sorry, you cannot proceed as you have already submitted your vote. If you believe this is an error, please contact your administrator.";

                Log::channel('ballot')->info($msg, $loggerDetails);
                ActivityController::log(['activityCode' => '00091', 'remarks' => $msg, 'userId' => Auth::user()->id]);
                return response()->json(['message' => $msg], 400);
            }



            $availableVotes =  $this->getAvailableVotes();

            if (count($availableVotes['bod']) === 0 && count($availableVotes['amendment']) === 0) {

                $msg = 'You don\'t have any votes available for proxy voting';

                Log::channel('ballot')->info($msg, $loggerDetails);
                ActivityController::log(['activityCode' => '00091', 'remarks' => $msg, 'userId' => Auth::user()->id]);
                return response()->json(['message' => $msg], 400);
            }


            $ballot = $this->generateBallot();


            DB::beginTransaction();

            $storeBallot = Ballot::insert([
                'ballotId'                  => $ballot['ballotId'],
                'ballotNo'                  => $ballot['ballotNo'],
                'ballotKey'                 => $ballot['ballotNo'] . '-proxy',
                'ballotType'                => 'proxy',
                'email'                     => Auth::user()->email,
                'ip'                        => $request->ip(),
                'authorizedVoter'           => $userInfo->vote_in_person,
                'role'                      => $userInfo->role,
                'availableVotesBod'         => count($availableVotes['bod']) * $setting['votes_per_share'],
                'availableVotesAmendment'   => count($availableVotes['amendment']),
                'availableAccounts'         => json_encode($availableVotes),
                'availableBodAccounts'       => json_encode($availableVotes['bod']),
                'availableAmendmentAccounts' => json_encode($availableVotes['amendment']),
                'castedVotes'               => null,
                'revoked'                   => 'both',
                'isSubmitted'               => false,
                'confirmationId'            => null,
                'createdBy'                 => $userInfo->id

            ]);


            if ($storeBallot === true) {

                Log::channel('ballot')->info('Ballot for proxy voting has been created', array_merge($loggerDetails, [
                    'ballotId' => $ballot['ballotId']
                ]));


                $attendance = [];

                foreach ($availableVotes['bod'] as $availableVote) {
                    $attendance[] = array(
                        'accountId' => $availableVote,
                        'ballotId' => $ballot['ballotId'],
                        'voteType' => 'bod',
                        'createdBy' => Auth::id()
                    );
                }
                foreach ($availableVotes['amendment'] as $availableVote) {
                    $attendance[] = array(
                        'accountId' => $availableVote,
                        'ballotId' => $ballot['ballotId'],
                        'voteType' => 'amendment',
                        'createdBy' => Auth::id()
                    );
                }

                $dbInsertAttendance = Attendance::insert($attendance);


                if ($dbInsertAttendance !== true) {

                    Log::channel('ballot')->info('Failed to create an attendance for proxy voting', array_merge($loggerDetails, [
                        'ballotId' => $ballot['ballotId']
                    ]));

                    return response()->json([], 500);
                }


                Log::channel('ballot')->info('Attendance for proxy voting has been created', array_merge($loggerDetails, [
                    'ballotId' => $ballot['ballotId']
                ]));


                ActivityController::log(['activityCode' => '00048', 'ballotId' => $ballot['ballotId'], 'userId' => $userInfo->id]);

                DB::commit();

                return response()->json(['ballotId' => $ballot['ballotId']], 200);
            }

            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        } catch (Exception $e) {

            Log::channel('ballot')->critical('Failed to create a ballot for proxy voting.', ['id' => Auth::user()->id, 'email' => Auth::user()->email, 'error' => $e->getMessage(), 'data' => $e]);

            Log::critical($e);

            return response()->json([], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function insertUsedAccounts($confirmation)
    {

        $data = json_decode($confirmation->availableVotes, true);

        $usedBod = false;
        $usedAmendment = false;

        $insertUsedBod = [];

        if (array_key_exists('bod', $data)) {
            foreach ($data['bod'] as $accountId) {
                $insertUsedBod[] = [
                    'ballotId' => $confirmation->ballotId,
                    'accountId' => $accountId,
                    'createdBy' => Auth::id()
                ];
            }

            $usedBod = UsedBoardOfDirectorAccount::insert($insertUsedBod);

            if ($usedBod === false) {
                throw new Exception("Failed to insert used bod accounts.");
            }
        }



        $insertUsedAmendment = [];
        if (array_key_exists('amendment', $data)) {

            foreach ($data['amendment'] as $accountId) {
                $insertUsedAmendment[] = [
                    'ballotId' => $confirmation->ballotId,
                    'accountId' => $accountId,
                    'createdBy' => Auth::id()
                ];
            }

            $usedAmendment = UsedAmendmentAccount::insert($insertUsedAmendment);

            if ($usedAmendment === false) {
                throw new Exception("Failed to insert used amendment accounts.");
            }
        }

        if (count($insertUsedBod) === 0 && count($insertUsedAmendment) === 0) {

            throw new Exception("Available votes in ballot confirmation is empty.");
        }

        return true;
    }

    public function update($confirmationId, $unusedVotesBod)
    {
        $update = $this->ballotInfo->update([

            'isSubmitted' => true,
            'submittedAt' => Carbon::now(),
            'confirmationId' => $confirmationId,
            'unusedVotesBod' => $unusedVotesBod

        ]);

        if ($update === false) {

            Log::critical("Failed to update ballot details after submitting the form.", ['id' => auth()->id()]);

            throw new Exception("ERROR");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
