<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class VotingController extends Controller
{

    // done 2022-09-16
    public static function validate_in_person($revoked)
    {

        try {

            $setting = EApp::setting();

            if ($setting === null) {

                return view('errors.response', ['code' => 500, 'message' => null]);
            }

            if ($setting->vote_in_person_start === null) {


                \Log::channel('evoting')->info('VOTE IN PERSON: The date for voting in person has not yet been posted by the system administrator.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: The date for voting in person has not yet been posted by the system administrator.']);


                return view('errors.response', ['code' => 500, 'message' => "The date for voting in person has not yet been posted by the system administrator."]);
            }


            $startFormat = date('F d, Y g:i:s A', strtotime($setting->vote_in_person_start));
            $endFormat = date('F d, Y g:i:s A', strtotime($setting->vote_in_person_end));


            if (!EApp::between_date($setting->vote_in_person_start, $setting->vote_in_person_end)) {


                \Log::channel('evoting')->info('VOTE IN PERSON: ' . "Voting in person is available from $startFormat. "  . " to " . $endFormat, ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: ' . "Voting in person is available from $startFormat. "  . " to ". $endFormat]);

                return view('errors.response', ['code' => 400, 'message' => "Voting in person is available from $startFormat. "  . " to " . $endFormat]);
            }


            $user = Auth::user();

            $withProxy = false;


            if ($user->role !== 'stockholder' and $user->role !== 'corp-rep') {

                \Log::channel('evoting')->alert('Only stockholders and corporate representatives are allowed to vote in person', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                return array('code' => 403, 'message' => 'Only stockholders and corporate representatives are allowed to vote in person.');
            }



            $accountInfo = DB::table('users')
                ->selectRaw('accountNo, accountKey, voteInPerson')
                ->where('accountNo', $user->accountNo)->where('role', 'stockholder')
                ->first();


            if ($accountInfo === null) {


                \Log::channel('evoting')->critical('VOTE IN PERSON: Account not found.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Account not found.']);

                return view('errors.response', ['code' => 400, 'message' => 'Account not found.']);
            }




            if (DB::table('ballots')->where('email', $user->email)->where('status', 1)->where('ballotType', 'person')->exists()) {


                \Log::channel('evoting')->info('VOTE IN PERSON: Sorry you have already submitted your ballot.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Sorry, you have already submitted your ballot.']);

                return array('code' => 400, 'message' => 'Sorry, you have already submitted your ballot.');
            }



            $stockholderVoted = DB::table('ballots')
                ->where('accountKey', $accountInfo->accountKey)
                ->where('status', 1)
                ->where('ballotType', 'person')
                ->exists();



            if ($stockholderVoted === true) {


                \Log::channel('evoting')->alert('VOTE IN PERSON: Stockholder has already voted.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Stockholder has already voted.']);

                return array('code' => 400, 'message' => 'Stockholder has already voted.');
            }




            if ($user->accountType === 'corp') {


                if ($accountInfo->voteInPerson === 'stockholder' and $user->role !== 'stockholder') {


                    \Log::channel('evoting')->alert('VOTE IN PERSON: Only the stockholder is allowed to vote.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                    // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Only the stockholder is allowed to vote.']);

                    return array('code' => 403, 'message' => "Only the stockholder is allowed to vote.");
                }

                if ($accountInfo->voteInPerson === 'corp-rep' and $user->role !== 'corp-rep') {


                    \Log::channel('evoting')->alert('VOTE IN PERSON: Only the corporate representative is allowed to vote.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                    // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Only the corporate representative is allowed to vote.']);


                    return array('code' => 403, 'message' => "Only the corporate representative is allowed to vote.");
                }
            }



            if ($user->role === 'stockholder') {


                $listOfAvailableVotes = DB::table('users')
                    ->selectRaw('accountKey, proxyAssignee')
                    ->where('accountNo', $user->accountNo)
                    ->where('isDelinquent', 0)
                    ->whereIn('role', ['corp-rep'])
                    ->whereNotIn('accountKey', function ($query) {
                        $query->select('accountKey')->from('used_accounts');
                    })
                    ->get();


                $noOfIssuedProxy = DB::table('users')
                    ->where('accountNo', $user->accountNo)
                    ->where('isDelinquent', 0)
                    ->whereIn('role', ['corp-rep'])
                    ->where('proxyAssignee', '!=', null)
                    ->whereNotIn('accountKey', function ($query) {
                        $query->select('accountKey')->from('used_accounts');
                    })
                    ->count();


                $noOfAvailableVotes = count($listOfAvailableVotes);
            } else {


                $listOfAvailableVotes = DB::table('users')
                    ->selectRaw('accountKey, proxyAssignee')
                    ->where('accountNo', $user->accountNo)
                    ->where('isDelinquent', 0)
                    ->whereIn('role', ['corp-rep'])
                    ->where('email', $user->email)
                    ->whereNotIn('accountKey', function ($query) {
                        $query->select('accountKey')->from('used_accounts');
                    })
                    ->get();



                $noOfIssuedProxy = DB::table('users')
                    ->where('accountNo', $user->accountNo)
                    ->where('isDelinquent', 0)
                    ->whereIn('role', ['corp-rep'])
                    ->where('email', $user->email)
                    ->where('proxyAssignee', '!=', null)
                    ->whereNotIn('accountKey', function ($query) {
                        $query->select('accountKey')->from('used_accounts');
                    })
                    ->count();



                $noOfAvailableVotes = count($listOfAvailableVotes);
            }


            if ($noOfAvailableVotes === 0) {


                \Log::channel('evoting')->alert('VOTE IN PERSON: No votes available.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: No votes available.']);

                return array('code' => 400, 'message' => 'No votes available.');
            }


            if ($noOfIssuedProxy !== 0) {

                $withProxy = true;
            }


            if ($revoked === 1 and $noOfIssuedProxy < 1) {


                \Log::channel('evoting')->alert('VOTE IN PERSON: Ballot form: Revoked. No proxy is issued.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Ballot form: Revoked. No proxy is issued.']);

                return array('code' => 400, 'message' => 'Ballot form: Revoked. No proxy is issued.');
            }



            if ($revoked === 0) {

                $noOfAvailableVotes = $noOfAvailableVotes - $noOfIssuedProxy;
            }

            return array(

                'code'                  => 200,
                'listOfAvailableVotes'  => $listOfAvailableVotes,
                'noOfAvailableVotes'    => $noOfAvailableVotes,
                'noOfIssuedProxy'       => $noOfIssuedProxy,
                'authorizedVoter'       => $accountInfo->voteInPerson,
                'withProxy'             => $withProxy

            );
        } catch (Exception $e) {


            \Log::channel('evoting')->alert('VOTE IN PERSON | Exception: ' . $e->getMessage(), ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

            // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Exception']);

            return array('code' => 500, 'message' => null);
        }
    }

    // done 2022-09-16
    public static function validate_proxy()
    {

        try {

            $setting    = EApp::setting();
            $dateTime   = EApp::datetime();


            if ($setting->vote_by_proxy_start === null) {


                \Log::channel('evoting')->info('VOTE BY PROXY: The date for voting by proxy has not yet been posted by the system administrator.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: The date for voting by proxy has not yet been posted by the system administrator.']);


                return view('errors.response', ['code' => 500, 'message' => "The date for voting by proxy has not yet been posted by the system administrator."]);
            }


            $startFormat = date('F d, Y g:i:s A', strtotime($setting->vote_by_proxy_start));
            $endFormat   = date('F d, Y g:i:s A', strtotime($setting->vote_by_proxy_end));




            if (!EApp::between_date($setting->vote_by_proxy_start, $setting->vote_by_proxy_end)) {


                \Log::channel('evoting')->info("Voting by proxy is available from $startFormat to  $endFormat.", ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: ' . "Voting by proxy is available from $startFormat to  $endFormat"]);

                return view('errors.response', ['code' => 400, 'message' => "Voting by proxy is available from $startFormat to  $endFormat."]);
            }



            $user = Auth::user();


            if ($user->role !== 'stockholder' and $user->role !== 'corp-rep'  and $user->role !== 'non-member') {


                \Log::channel('evoting')->critical('Only stockholders, corporate representatives and non members are allowed to vote by proxy.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


                return array('code' => 403, 'message' => 'VOTE BY PROXY: Only stockholders, corporate representatives and non members are allowed to vote by proxy.');
            }


            $accountInfo = DB::table('users')
                ->selectRaw('accountNo, accountKey, voteInPerson')
                ->where('accountNo', $user->accountNo)->whereIn('role', ['stockholder', 'non-member'])
                ->first();




            if ($accountInfo === null) {


                \Log::channel('evoting')->critical('VOTE BY PROXY: Account not found', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Account not found.']);

                return view('errors.response', ['code' => 400, 'message' => 'Account not found.']);
            }



            if (DB::table('ballots')->where('email', $user->email)->where('status', 1)->where('ballotType', 'proxy')->exists()) {

                \Log::channel('evoting')->critical('VOTE BY PROXY: Sorry you have already submitted your ballot.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Sorry, you have already submitted your ballot.']);

                return array('code' => 400, 'message' => 'Sorry, you have already submitted your ballot.');
            }



            if ($user->role === 'stockholder' or $user->role === 'non-member') {

                if ($user->role === 'stockholder' and $user->accountType === 'indv') {

                    $corpRepUserIds = EApp::obj_to_array(DB::table('users')->select('id')->where('accountNo', $user->accountNo)->get());

                    $listOfAvailableVotes = DB::table('users')
                        ->selectRaw('accountKey')
                        ->whereIn('proxyAssignee', $corpRepUserIds)
                        ->where('isDelinquent', 0)
                        ->whereIn('role', ['corp-rep'])
                        ->whereNotIn('accountKey', function ($query) {
                            $query->select('accountKey')->from('used_accounts');
                        })
                        ->get();

                    $noOfAvailableVotes = count($listOfAvailableVotes);
                } else {

                    $listOfAvailableVotes = DB::table('users')
                        ->selectRaw('accountKey')
                        ->where('proxyAssignee', $user->id)
                        ->where('isDelinquent', 0)
                        ->whereIn('role', ['corp-rep'])
                        ->whereNotIn('accountKey', function ($query) {
                            $query->select('accountKey')->from('used_accounts');
                        })
                        ->get();


                    $noOfAvailableVotes = count($listOfAvailableVotes);
                }
            } else {


                $corpRepUserIds = EApp::obj_to_array(DB::table('users')->select('id')->where('accountNo', $user->accountNo)->where('email', $user->email)->get());

                $listOfAvailableVotes = DB::table('users')
                    ->selectRaw('accountKey')
                    ->whereIn('proxyAssignee', $corpRepUserIds)
                    ->where('isDelinquent', 0)
                    ->whereIn('role', ['corp-rep'])
                    ->whereNotIn('accountKey', function ($query) {
                        $query->select('accountKey')->from('used_accounts');
                    })
                    ->get();

                $noOfAvailableVotes = count($listOfAvailableVotes);
            }

            if ($noOfAvailableVotes === 0) {


                \Log::channel('evoting')->info('VOTE BY PROXY: No votes available', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: No votes available.']);

                return array('code' => 400, 'message' => 'No votes available.');
            }



            if ($noOfAvailableVotes < 1) {


                \Log::channel('evoting')->info('VOTE BY PROXY: No votes available', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: No votes available.']);

                return array('code' => 400, 'message' => 'No votes available.');
            }

            return array(
                'code'                  => 200,
                'listOfAvailableVotes'  => $listOfAvailableVotes,
                'noOfAvailableVotes'    => $noOfAvailableVotes,
            );
        } catch (Exception $e) {


            \Log::channel('evoting')->critical('VOTE BY PROXY:  Exception | ' . $e->getMessage(), ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


            ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Exception']);


            return array('code' => 500, 'message' => null);
        }
    }

    // done 2022-09-16
    public function vote_in_person_form(Request $request)
    {

        try {

            $user       = Auth::user();
            $dateTime   = EApp::datetime();
            $revoked    = $request->route()->parameter('revoked') === null ? 0 : 1;


            $validateBallot  = VotingController::validate_in_person($revoked);

            if ($validateBallot['code'] !== 200) {

                return view('errors.response', ['code' => $validateBallot['code'], 'message' => $validateBallot['message']]);
            }


            $noOfAvailableVotes = $validateBallot['noOfAvailableVotes'];

            $candidates         = DB::table('candidates')
                ->selectRaw('candidateId, firstName, lastName, middleName')
                ->where('status', 1)
                ->orderBy('lastName', 'ASC')
                ->orderBy('firstName', 'ASC')->get();


            if (count($candidates) === 0) {

                \Log::channel('evoting')->critical('Candidates not found', ["userId" => Auth::user()->id]);

                // ActivityController::log(["code" => "00046", "remarks" => "Candidates not found"]);

                return view('errors.response', ['code' => 500, 'message' => 'Candidates not found']);
            }



            $amendments  = DB::table('amendments')->where('status', 1)->get();

            if (count($amendments) === 0) {

                \Log::channel('evoting')->critical('Amendment not found', ["userId" => Auth::user()->id]);

                // ActivityController::log(["code" => "00046", "remarks" => "Amendment not found"]);

                return view('errors.response', ['code' => 500, 'message' => 'Amendment not found.']);
            }



            $lastBallotNo = DB::table('ballots')
                ->selectRaw('MAX(CAST(trim(LEADING "0" FROM ballotNo) AS UNSIGNED)) AS lastBallotNo')
                ->where('ballotType', 'person')
                ->first();


            $ballotNo = str_pad(($lastBallotNo === null ? '0001' : $lastBallotNo->lastBallotNo + 1), 4, "0", STR_PAD_LEFT);


            if ($user->email === null) {

                \Log::channel('evoting')->alert('VOTE IN PERSON: Email not found', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                return view('errors.response', ['code' => 400, 'message' => 'Email not found']);
            }


            DB::beginTransaction();


            $dbBallots = DB::table('ballots')
                ->insert([
                    'ballotNo'          => $ballotNo,
                    'accountKey'        => $user->accountKey,
                    'ballotId'          => $ballotNo . '-person',
                    'userId'            => $user->id,
                    'revoked'           => $revoked,
                    'ballotType'        => 'person',
                    'email'             => $user->email,
                    'authorizedVoter'   => $validateBallot['authorizedVoter'],
                    'role'              => $user->role,
                    'votesAvailable'    => $noOfAvailableVotes,
                    'votesUsed'         => null,
                    'ip'                => request()->ip(),
                    'createdAt'         => $dateTime,
                    'status'            => 0

                ]);




            if ($dbBallots !== true) {

                // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Failed to insert ballot no.']);

                \Log::channel('evoting')->alert('VOTE IN PERSON: Failed to insert ballot no.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                return view('errors.response', ['code' => 500, 'message' => null]);
            }

            $param = array(
                "revoked"           => $revoked,
                "with_proxy"        => $validateBallot['withProxy'],
                'ballot_id'         => $ballotNo . '-person',
                "ballot_no"         => $ballotNo,
                "total_share"       => $noOfAvailableVotes,
                "total_no_of_vote"  => $noOfAvailableVotes * 3,
                "candidates"        => $candidates,
                "amendments"        => $amendments,
                "amendment_break"   => 11
            );


            // ActivityController::log(['code' => '00047', 'accountNo' => $user->accountNo, 'accountKey' => $user->accountKey, 'email' => $user->email, 'ballotId' => $ballotNo . '-person']);

            \Log::channel('evoting')->info('VOTE BY PROXY: Generated a ballot no. ' . $ballotNo, ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


            DB::commit();

            return view('user.cast_vote_in_person', $param);
        } catch (Exception $e) {


            return $e->getMessage();

            // ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Exception']);

            \Log::channel('evoting')->critical('VOTE IN PERSON: Exception | ' . $e->getMessage(), ["userId" => Auth::user()->id, "email" => Auth::user()->email]);
        }
    }

    // done 2022-09-16
    public function vote_by_proxy_form(Request $request)
    {

        try {

            $user       = Auth::user();
            $dateTime   = EApp::datetime();


            $validateBallot  = VotingController::validate_proxy();

            if ($validateBallot['code'] !== 200) {

                return view('errors.response', ['code' => $validateBallot['code'], 'message' => $validateBallot['message']]);
            }


            $noOfAvailableVotes     = $validateBallot['noOfAvailableVotes'];
            $lastBallotNo           = DB::table('ballots')->selectRaw('MAX(CAST(trim(LEADING "0" FROM ballotNo) AS UNSIGNED)) AS lastBallotNo')->where('ballotType', 'proxy')->first();


            $amendments = DB::table('amendments')->where('status', 1)->get();

            if (count($amendments) === 0) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Amendment not found']);

                return view('errors.response', ['code' => 500, 'message' => 'Amendment not found']);
            }


            $candidates = DB::table('candidates')->selectRaw('candidateId, firstName, lastName, middleName')->where('status', 1)->orderBy('lastName', 'ASC')->orderBy('firstName', 'ASC')->get();

            if (count($candidates) === 0) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Candidates not found']);

                return view('errors.response', ['code' => 500, 'message' => 'Candidates not found']);
            }


            $ballotNo = str_pad(($lastBallotNo === null ? '0001' : $lastBallotNo->lastBallotNo + 1), 4, "0", STR_PAD_LEFT);


            if ($user->email === null) {

                return view('errors.response', ['code' => 400, 'message' => 'Email not found']);
            }


            DB::beginTransaction();


            $dbBallots = DB::table('ballots')->insert([
                'ballotNo'          => $ballotNo,
                'accountKey'        => $user->accountKey,
                'ballotId'          => $ballotNo . '-proxy',
                'userId'            => $user->id,
                'ballotType'        => 'proxy',
                'email'             => $user->email,
                'role'              => $user->role,
                'votesAvailable'    => $noOfAvailableVotes,
                'votesUsed'         => null,
                'ip'                => request()->ip(),
                'createdAt'         => $dateTime,
                'status'            => 0

            ]);

            if ($dbBallots !== true) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Error inserting ballot form.']);

                return view('errors.response', ['code' => 500, 'message' => null]);
            }


            $param = array(

                'ballot_id'         => $ballotNo . '-proxy',
                "ballot_no"         => $ballotNo,
                "total_share"       => $noOfAvailableVotes,
                "total_no_of_vote"  => $noOfAvailableVotes * 3,
                "candidates"        => $candidates,
                "amendments"        => $amendments,
                "amendment_break"   => 11

            );


            ActivityController::log(['code' => '00048', 'accountNo' => $user->accountNo, 'accountKey' => $user->accountKey, 'email' => $user->email, 'ballotId' => $ballotNo . '-proxy']);

            DB::commit();

            return view('user.cast_vote_proxy', $param);
        } catch (Exception $e) {

            \Log::channel('evoting')->critical($e->getMessage(), ["userId" => Auth::user()->id]);

            ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Exception']);

            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    // semi complete
    public function vote_in_person(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'revoked'           => 'required|in:0,1',
                'ballot_id'         => 'required',
                'available'         => 'nullable|numeric|integer',
                'data'              => 'nullable|array',
                'amendment'         => 'nullable|array',
                'confirm_id'        => 'required|numeric'

            ]);


            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());

                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            $dateTime = EApp::datetime();

            $user       = Auth::user();
            $ballotId   = $request->input('ballot_id');
            $data       = $request->input('data');
            $amendments = $request->input('amendment');
            $confirmId  = $request->input('confirm_id');
            $available  = (int)$request->input('available');
            $revoked    = (int)$request->input('revoked');

            $votesUsed  = 0;
            $insertVote = [];

            $validateBallot = VotingController::validate_in_person($revoked);


            if ($validateBallot['code'] !== 200) {

                return response()->json(['message' => $validateBallot['message']], $validateBallot['code']);
            }


            $confirmInfo = DB::table("confirmations")->where("confirmationId", $confirmId)->orderBy("createdAt", "asc")->first();


            if ($confirmInfo === null) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Confirmation ID not found.']);

                return response()->json(["message" => "Confirmation ID not found."], 400);
            }


            if (($validateBallot['noOfAvailableVotes'] * 3) !== $available) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: There are changes in the system.']);

                return response()->json(['message' => 'There are changes in the system. Please reload the page.'], 400);
            }



            if ($data != null) {

                foreach ($data as $voteDetail) {

                    $candidateId = $voteDetail['id'];

                    if ($candidateId === null or trim($voteDetail['id']) === "") {

                        ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Candidate ID is null or empty.']);

                        return response()->json(['message' => 'Candidate ID is null or empty'], 500);
                    }


                    try {

                        $voteCount = EApp::valid_int($voteDetail['vote']);
                    } catch (Exception $e) {

                        ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Vote count is not a valid number.']);

                        return response()->json(['message' => 'Vote count is not a valid number.'], 400);
                    }


                    if ($voteCount > 0) {

                        $votesUsed = $votesUsed + $voteCount;

                        $insertVote[] = array(
                            'userId'        => $user->id,
                            'ballotId'      => $ballotId,
                            'candidateId'   => $candidateId,
                            'vote'          => $voteCount
                        );

                        $confirmData["c"][] = array(
                            "id"    => (int)$candidateId,
                            "v"     => (int)$voteCount,
                        );
                    }
                }
            }


            $insertAmendment = [];


            $confirmData = [];

            foreach ($amendments as $amendment) {

                $amendmentInFavor   = (int)$amendment["inFavor"];
                $amendmentNotFavor  = (int)$amendment["notFavor"];
                $amendmentAbstain   = (int)$amendment["abstain"];


                if (($amendmentInFavor + $amendmentNotFavor + $amendmentAbstain) !== 1) {

                    return response()->json(["message" => EApp::SERVER_ERROR], 500);
                }


                $insertAmendment[] = array(

                    "ballotId"      => $ballotId,
                    "amendmentId"   =>  $amendment["id"],
                    "inFavor"       => $amendmentInFavor * 3,
                    "notFavor"      => $amendmentNotFavor * 3,
                    "abstain"       => $amendmentAbstain * 3,
                    "userId"        => $user->id,
                    "createdAt"     => $dateTime,
                    "status"        => 1

                );

                $confirmData["a"][] = array(

                    "id" => (int)$amendment["id"],
                    "i" => $amendmentInFavor,
                    "n" => $amendmentNotFavor,
                    "a" => $amendmentAbstain

                );
            }


            if (count($insertAmendment) === 0) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Amendment is required.']);

                return response()->json(['message' => 'Amendment is required.'], 400);
            }


            if ($votesUsed > ($validateBallot['noOfAvailableVotes'] * 3)) {


                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: The total number of votes for candidates should not be greater than the total available votes.']);

                return response()->json(['message' => 'The total number of votes should not be greater than the total available votes.'], 400);
            }



            if ($revoked === 1 and $validateBallot['withProxy'] === false) {


                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE IN PERSON: Ballot Form: revoked. No proxy is issued.']);

                return response()->json(['message' => 'Ballot Form: revoked. No proxy is issued.']);
            }



            $usedAccounts = [];

            foreach ($validateBallot['listOfAvailableVotes'] as $listOfAvailableVotes) {

                if ($revoked === 1) {

                    $usedAccounts[]     = array(

                        'ballotId'      => $ballotId,
                        'accountKey'    => $listOfAvailableVotes->accountKey,
                        'userId'        => $user->id,
                        'createdAt'     => $dateTime,

                    );
                } else {

                    if ($listOfAvailableVotes->proxyAssignee === null) {

                        $usedAccounts[]     = array(
                            'ballotId'      => $ballotId,
                            'accountKey'    => $listOfAvailableVotes->accountKey,
                            'userId'        => $user->id,
                            'createdAt'     => $dateTime,

                        );
                    }
                }
            }



            if (count($usedAccounts) === 0) {


                ActivityController::log(['code' => '00044', 'remarks' => 'VOTE IN PERSON: No votes available.']);

                return response()->json(['message' => 'No available votes.'], 400);
            }



            DB::beginTransaction();

            $dbUpdateBallot = DB::table('ballots')
                ->where('ballotId', $ballotId)
                ->where('accountKey', $user->accountKey)
                ->where('userId', $user->id)
                ->where('revoked', $revoked)
                ->where('ballotType', 'person')
                ->where('email', $user->email)
                ->where('authorizedVoter', $validateBallot['authorizedVoter'])
                ->where('role', $user->role)
                ->where('votesAvailable', $validateBallot['noOfAvailableVotes'])
                ->where('status', 0)
                ->update([
                    'status'        => 1,
                    'votesUsed'     => $votesUsed,
                    'submittedAt'   => $dateTime,
                    'confirmationId' => $confirmId
                ]);

            if ($dbUpdateBallot !== 1) {

                ActivityController::log(['code' => '00046', 'remarks' => "An error encountered while updating the ballot"]);

                return response()->json([], 500);
            }


            if ($data !== null) {

                $insertBallotDetails = DB::table('ballot_details')->insert($insertVote);

                if ($insertBallotDetails  !== true) {

                    ActivityController::log(['code' => '00046', 'remarks' => "An error encountered while inserting the candidates"]);

                    return response()->json([], 500);
                }
            }


            $dbInsertUsedAccount = DB::table('used_accounts')->insert($usedAccounts);


            if ($dbInsertUsedAccount !== true) {

                ActivityController::log(['code' => '00046', 'remarks' => "An error encountered while inserting used votes."]);

                return response()->json([], 500);
            }


            $dbInsertAmendment = DB::table('ballot_amendments')->insert($insertAmendment);


            if ($dbInsertAmendment !== true) {

                ActivityController::log(['code' => '00046', 'remarks' => "An error encountered while inserting the amendments."]);

                return response()->json([], 500);
            }


            ActivityController::log(['code' => '00044', 'accountNo' => $user->accountNo, 'accountKey' => $user->accountKey, 'email' => $user->email, 'ballotId' => $ballotId]);

            DB::commit();

            VotingConfirmationController::Send_Voting_Confirmation($user->email);

            return response()->json(['message' => 'Your vote has been submitted successfully.'], 200);
        } catch (Exception $e) {


            ActivityController::log(['code' => '00046', 'remarks' => $e->getMessage()]);

            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    // done 2022/09/16

    public static function confirm_ballot($remarks, $ballotId, $data)
    {

        try {


            $confirmId  = EApp::generate_id("confirmations", "confirmationId");

            $ballotInfo = DB::table('ballots')->where('ballotId', $ballotId)->first();

            $code = $ballotInfo->ballotType == "person" ? "00071" : "00072";


            $dbConfirm = DB::table("confirmations")->insert(
                [
                    "confirmationId" => $confirmId,
                    "ballotId"       => $ballotId,
                    "ballotType"      => $ballotInfo->ballotType,
                    "data"             => json_encode($data),
                    "remarks"       => $remarks,
                    "email"             => Auth::user()->email,
                    "ip"            => request()->ip(),
                    "userId"        => Auth::user()->id

                ]
            );

            if ($dbConfirm !== true) {

                throw "Error occured when updating confirm ballot";
            }


            ActivityController::log(['code' => $code, 'ballotId' => $ballotId, 'remarks' => $remarks, "confirmationId" => $confirmId]);

            return $confirmId;
        } catch (Exception $e) {

            throw EApp::SERVER_ERROR;
        }
    }


    // done 2022-09-16
    public function vote_confirmation(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'revoked'           => 'required|in:0,1',
                'ballot_id'         => 'required',
                'available'         => 'nullable|numeric|integer',
                'candidate'         => 'nullable|array',
                'amendment'         => 'nullable|array',
                'form_type'         => 'in:person,proxy'

            ]);



            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());

                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            $dateTime = EApp::datetime();

            $user           = Auth::user();
            $ballotId       = $request->input('ballot_id');
            $candidateData  = $request->input('canidate');
            $amendmentData  = $request->input('amendment');
            $available      = (int)$request->input('available');
            $revoked        = (int)$request->input('revoked');

            $votesUsed      = 0;
            $insertVote     = [];
            $candidatesPool = [];


            $amendmentBreak = 11;
            // $amendmentBreak = 11;
            $summaryCandidates  = "";
            $amendmentSummary   = "";
            $amendCounter       = 0;
            $amendSummaryOne    = "";
            $amendSummaryTwo    = "";


            $ballotInfo = DB::table("ballots")->where("ballotId", $ballotId)->first();

            $logPrefix  = $ballotInfo->ballotType == "person" ? "VOTE IN PERSON: " : "VOTE BY PROXY: ";


            $validateBallot = $ballotInfo->ballotType == "person" ? VotingController::validate_in_person($revoked) : VotingController::validate_proxy();



            if ($validateBallot['code'] !== 200) {

                return response()->json(['message' => $validateBallot['message']], $validateBallot['code']);
            }


            if (($validateBallot['noOfAvailableVotes'] * 3) !== $available) {

                ActivityController::log(['code' => '00046', 'ballotId' => $ballotId, 'remarks' => $logPrefix . 'There are changes in the system. ']);

                return response()->json(['message' => 'There are changes in the system. Please reload the page.'], 400);
            }




            $dbCandidates       = DB::table("candidates")->where("status", "1")->get();
            $arrDbCandidates    = [];

            foreach ($dbCandidates as $dbCandidate) {

                $arrDbCandidates[$dbCandidate->candidateId] = $dbCandidate;
            }


            $dbAmendments       = DB::table("amendments")->where("status", "1")->get();
            $arrDbAmendments    = [];

            foreach ($dbAmendments as $dbAmendment) {

                $arrDbAmendments[$dbAmendment->amendmentId] = $dbAmendment;
            }


            $arrAmendmentData = [];

            foreach ($amendmentData as $amendmentDetail) {

                $arrAmendmentData[$amendmentDetail["id"]] = $amendmentDetail;
            }



            $confirmData = [];

            foreach ($arrDbAmendments as $amendId => $amendment) {

                $amendCounter++;

                $amendmentInFavor   = $arrAmendmentData[$amendId]["inFavor"];
                $amendmentNotFavor  = $arrAmendmentData[$amendId]["notFavor"];
                $amendmentAbstain   = $arrAmendmentData[$amendId]["abstain"];


                if (($amendmentInFavor +  $amendmentNotFavor + $amendmentAbstain) != 1) {

                    ActivityController::log(['code' => '00046', 'ballotId' => $ballotId, 'remarks' => $logPrefix . 'Invalid amendment data']);

                    return response()->json(["message" => "Invalid amendment details!"], 400);
                }

                $confirmData["a"][] = array(

                    "id" => $amendId,
                    "i" => $amendmentInFavor,
                    "n" => $amendmentNotFavor,
                    "a" => $amendmentAbstain

                );


                $amendmentSummary = '<tr>
                                        <td class="td-ammend-desc">' . $amendment->amendmentDesc . '</td>
                                        <td class="td-radio"><input class="custom-radio" type="radio" ' . ($amendmentInFavor  == 1 ? "checked" : "") . ' disabled></td>
                                        <td class="td-radio"><input class="custom-radio" type="radio" ' . ($amendmentNotFavor  == 1 ? "checked" : "") . ' disabled></td>
                                        <td class="td-radio"><input class="custom-radio" type="radio" ' . ($amendmentAbstain   == 1 ? "checked" : "") . ' disabled></td>
                                    </tr>';



                if ($amendCounter <= $amendmentBreak) {

                    $amendSummaryOne .= $amendmentSummary;
                } else {

                    $amendSummaryTwo .= $amendmentSummary;
                }
            }



            if ($candidateData !== null) {

                foreach ($candidateData as $candidateDetail) {

                    $candidateId = $candidateDetail['id'];

                    if ($candidateId === null or trim($candidateDetail['id']) === "") {

                        ActivityController::log(['code' => '00046', 'ballotId' => $ballotId, 'remarks' => $logPrefix . 'Candidate ID is null or empty.']);

                        return response()->json(['message' => 'Candidate ID is null or empty'], 500);
                    }


                    try {

                        $voteCount = EApp::valid_int($candidateDetail['vote']);
                    } catch (Exception $e) {

                        ActivityController::log(['code' => '00046', 'ballotId' => $ballotId, 'remarks' => $logPrefix . 'Vote count is not a valid number. [value = ' . htmlspecialchars($candidateDetail["vote"]) . ', id = ' . $candidateDetail['id'] . ']']);

                        return response()->json(['message' => 'Vote count is not a valid number.'], 400);
                    }


                    if ($voteCount > 0) {

                        $votesUsed = $votesUsed + $voteCount;

                        $confirmData["c"][] = array(
                            "id" => $candidateId,
                            "v" => $voteCount,
                        );

                        $summaryCandidates .= '<tr><td class="td-label-cand"">' . $arrDbCandidates[$candidateId]->firstName . ' ' . $arrDbCandidates[$candidateId]->middleName . ' ' . $arrDbCandidates[$candidateId]->lastName . '</td><td class="td-label-cand text-center">' . $voteCount . '</td></tr>';

                        $insertVote[] = array(
                            'candidateId'   => $candidateId,
                            'vote'          => $voteCount
                        );
                    }


                    if (in_array($candidateDetail["id"], $candidatesPool)) {

                        ActivityController::log(['code' => '00046', 'ballotId' => $ballotId, 'remarks' => $logPrefix . 'Duplicate candidates are detected. [id=' . $candidateDetail["id"] . ']']);

                        return response()->json(['message' => 'Duplicate candidates are detected.'], 400);
                    }

                    array_push($candidatesPool, $candidateDetail["id"]);
                }
            }


            $arr = array(

                "candidate"         => $summaryCandidates == '' ? '<tr class="text-center"><td  colspan="2">You didn\'t vote to any candidates</td></tr>' : $summaryCandidates,
                "amendment_first"   => $amendSummaryOne,
                "amendment_second"  => $amendSummaryTwo

            );



            if (count($insertVote) === 0) {

                $arr["message"]     = "You didn't cast a vote to any candidates.";
                $arr["code"]        = "warning";
                $arr["confirm_id"]  = VotingController::confirm_ballot($arr["message"], $ballotId, $confirmData);

                return response()->json($arr, 200);
            }



            if (($validateBallot['noOfAvailableVotes'] * 3) < $votesUsed) {

                $arr["message"]     =  "The total votes for candidates should not be greater than " . ($validateBallot['noOfAvailableVotes'] * 3) . " votes.";
                $arr["code"]        = "error";
                $arr["confirm_id"]  = VotingController::confirm_ballot($arr["message"], $ballotId, $confirmData);

                return response()->json($arr, 200);
            }


            if ($votesUsed < ($validateBallot['noOfAvailableVotes'] * 3)) {

                $arr["message"]     =  "You still have " . (($validateBallot['noOfAvailableVotes'] * 3) - $votesUsed) . " vote(s) left. Are you sure?";
                $arr["code"]        = "warning";
                $arr["confirm_id"]  = VotingController::confirm_ballot($arr["message"], $ballotId, $confirmData);

                return response()->json($arr, 200);
            }



            if ($revoked === 1 and $validateBallot['withProxy'] === false) {

                ActivityController::log(['code' => '00071', 'ballotId' => $ballotId, 'remarks' => 'Ballot type is revoked but no proxy is issued.']);
                return response()->json(['message' => 'Ballot typee is revoked but no proxy is issued.'], 400);
            }


            $arr["message"]     = "Ballot is complete.";
            $arr["code"]        = "success";
            $arr["confirm_id"]  = VotingController::confirm_ballot($arr["message"], $ballotId, $confirmData);

            return response()->json($arr, 200);
        } catch (Exception $e) {


            ActivityController::log(['code' => '00046', 'ballotId' => $ballotId, 'remarks' => 'VOTE CONFIRMATION ERROR']);

            \Log::channel('evoting')->critical($e->getMessage(), ["userId" => Auth::user()->id, "ballotId" => $ballotId]);

            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }


    // done 2022-09-17
    public function vote_by_proxy(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'ballot_id'         => 'required',
                'available'         => 'required|numeric|integer',
                'data'              => 'nullable|array',
                'amendment'         => 'nullable|array',
                'confirm_id'        => 'required|numeric|integer'

            ]);


            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());

                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            $dateTime = EApp::datetime();

            $user       = Auth::user();
            $ballotId   = $request->input('ballot_id');
            $data       = $request->input('data');
            $amendments = $request->input('amendment');
            $available  = (int)$request->input('available');
            $confirmId  = $request->input('confirm_id');


            $votesUsed  = 0;
            $insertVote = [];


            $validateBallot = VotingController::validate_proxy();


            if ($validateBallot['code'] !== 200) {

                return response()->json(['message' => $validateBallot['message']], $validateBallot['code']);
            }


            if (($validateBallot['noOfAvailableVotes'] * 3) !== $available) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: There are changes in the system.']);

                return response()->json(['message' => 'There are changes in the system. Please reload the page.'], 400);
            }


            $confirmData = [];

            $insertAmendment = [];

            foreach ($amendments as $amendment) {

                $amendmentInFavor   = (int)$amendment["inFavor"];
                $amendmentNotFavor  = (int)$amendment["notFavor"];
                $amendmentAbstain   = (int)$amendment["abstain"];


                if (($amendmentInFavor + $amendmentNotFavor + $amendmentAbstain) !== 1) {

                    return response()->json(["message" => EApp::SERVER_ERROR], 500);
                }

                $insertAmendment[] = array(

                    "ballotId"      => $ballotId,
                    "amendmentId"   => $amendment["id"],
                    "inFavor"       => $amendmentInFavor * 3,
                    "notFavor"      => $amendmentNotFavor * 3,
                    "abstain"       => $amendmentAbstain * 3,
                    "userId"        => $user->id,
                    "createdAt"     => $dateTime,
                    "status"        => 1

                );

                $confirmData["a"][] = array(

                    "id" => (int)$amendment["id"],
                    "i" => $amendmentInFavor,
                    "n" => $amendmentNotFavor,
                    "a" => $amendmentAbstain

                );
            }


            if ($data !== null) {

                foreach ($data as $voteDetail) {

                    $candidateId = $voteDetail['id'];

                    if ($candidateId === null or trim($voteDetail['id']) === "") {

                        ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Candidate ID is null or empty.']);

                        return response()->json(['message' => 'Candidate ID is null or empty'], 500);
                    }


                    try {

                        $voteCount = EApp::valid_int($voteDetail['vote']);
                    } catch (Exception $e) {

                        ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Vote count is not a valid number.']);

                        return response()->json(['message' => 'Vote count is not a valid number.'], 400);
                    }


                    if ($voteCount > 0) {

                        $votesUsed = $votesUsed + $voteCount;

                        $insertVote[] = array(
                            'userId'        => $user->id,
                            'ballotId'      => $ballotId,
                            'candidateId'   => $candidateId,
                            'vote'          => $voteCount
                        );


                        $confirmData["c"][] = array(
                            "id"    => (int)$candidateId,
                            "v"     => (int)$voteCount,
                        );
                    }
                }
            }


            $confirmInfo = DB::table("confirmations")->where("confirmationId", $confirmId)->orderBy("createdAt", "asc")->first();


            if ($confirmInfo === null) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: Confirmation ID not found.']);

                return response()->json(["message" => "Confirmation ID not found."], 400);
            }


            if (count($insertAmendment) === 0) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: At least one vote for candidate/amendment is required.']);

                return response()->json(['message' => 'Amendment is required.'], 400);
            }


            if ($votesUsed > ($validateBallot['noOfAvailableVotes'] * 3)) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: The total number of votes for candidates should not be greater than the total available votes.']);

                return response()->json(['message' => 'The total number of votes should not be greater than the total available votes.'], 400);
            }

            $usedAccounts = [];

            foreach ($validateBallot['listOfAvailableVotes'] as $listOfAvailableVotes) {

                $usedAccounts[]     = array(
                    'ballotId'      => $ballotId,
                    'accountKey'    => $listOfAvailableVotes->accountKey,
                    'userId'        => $user->id,
                    'createdAt'     => $dateTime,
                );
            }

            if (count($usedAccounts) === 0) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PERSON: No votes available.']);

                return response()->json(['message' => 'No available votes.'], 400);
            }


            DB::beginTransaction();

            $dbUpdateBallot = DB::table('ballots')
                ->where('ballotId', $ballotId)
                ->where('accountKey', $user->accountKey)
                ->where('userId', $user->id)
                ->where('ballotType', 'proxy')
                ->where('email', $user->email)
                ->where('role', $user->role)
                ->where('votesAvailable', $validateBallot['noOfAvailableVotes'])
                ->where('status', 0)
                ->update([
                    'status'        => 1,
                    'votesUsed'     => $votesUsed,
                    'submittedAt'   => $dateTime,
                    'confirmationId' =>  $confirmId
                ]);


            if ($dbUpdateBallot !== 1) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: An error occured while updating ballot form.']);

                return response()->json([], 500);
            }


            $dbInsertAmendment   = DB::table('ballot_amendments')->insert($insertAmendment);
            $dbInsertUsedAccount = DB::table('used_accounts')->insert($usedAccounts);


            if ($data !== null) {

                $insertBallotDetails = DB::table('ballot_details')->insert($insertVote);

                if ($insertBallotDetails !== true) {

                    ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: An error occured while inserting candidates.']);

                    return response()->json([], 500);
                }
            }


            if ($dbInsertAmendment !== true) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: An error occured while inserting amendments.']);

                return response()->json([], 500);
            }


            if ($dbInsertUsedAccount !== true) {

                ActivityController::log(['code' => '00046', 'remarks' => 'VOTE BY PROXY: An error occured while inserting used accounts.']);

                return response()->json([], 500);
            }


            ActivityController::log(['code' => '00045', 'accountNo' => $user->accountNo, 'accountKey' => $user->accountKey, 'email' => $user->email, 'ballotId' => $ballotId]);

            DB::commit();

            VotingConfirmationController::Send_Voting_Confirmation($user->email);

            return response()->json(['message' => 'Your vote has been submitted successfully.'], 200);
        } catch (Exception $e) {

            \Log::channel('evoting')->critical($e->getMessage(), ["userId" => Auth::user()->id]);

            ActivityController::log(['code' => '00046', 'remarks' => "VOTE BY PROXY: Exception"]);

            return response()->json(['message' => EApp::SERVER_ERROR], 500);
        }
    }

    public function ballots()
    {

        try {

            $candidates  =  DB::table('candidates')->where('status', 1)->get();

            $ballots = DB::table('ballots')

                ->selectRaw('
                            ballots.id, ballots.ballotNo, ballots.revoked, ballots.email, ballots.role, ballots.votesAvailable, ballots.votesUsed, ballots.createdAt, ballots.submittedAt, ballots.status, ballots.ballotType, ballot_details.candidateId, ballot_details.vote, u.accountType
                            ')
                ->leftJoin('ballot_details', 'ballot_details.ballotId', '=', 'ballots.ballotId')
                ->leftJoin('users AS u', 'u.id', '=', 'ballots.userId')
                ->orderBy('ballots.id', 'ASC')
                // ->where('ballots.ballotType', 'person')
                ->get();


            $processBallot = [];


            $masterListCanidates = [];


            foreach ($ballots as $ballot) {

                foreach ($candidates as $candidate) {

                    if ($candidate->candidateId === $ballot->candidateId) {

                        $masterListCanidates[] = array('ballotId' => $ballot->id, 'candidateId' => $ballot->candidateId, 'vote' => $ballot->vote);
                    }
                }


                $processBallot[$ballot->id] = array(

                    'id'            => $ballot->id,
                    'ballotNo'      => $ballot->ballotNo,
                    'ballotType'    => $ballot->ballotType,
                    'revoked'       => $ballot->revoked,
                    'role'          => $ballot->role,
                    'status'        => $ballot->status,
                    'accountType'   => $ballot->accountType,
                    'votesAvailable' => $ballot->votesAvailable,
                    'votesUsed'     => $ballot->votesUsed,

                );
            }



            ActivityController::log(['code' => '00055']);

            return view('admin.ballots', ['processBallot' => $processBallot, 'candidates' => $candidates, 'candidateVotes' => $masterListCanidates]);
        } catch (Exception $e) {

            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    public function view_confirmation(Request $request)
    {


        try {


            $confirmId = $request->route()->parameter('id');


            $confirmationDetails = DB::table('confirmations')->where('confirmationId', $confirmId)->first();


            $ballotInfo = DB::table('ballots')->where('ballotId', $confirmationDetails->ballotId)->first();


            $arrConfirmCandidates = json_decode($confirmationDetails->data, true);

            $dbCandidates = DB::table("candidates")->where('status', '1')->get();


            $arrDbCandidates = [];

            foreach ($dbCandidates as $dbCandidate) {


                $arrDbCandidates[$dbCandidate->candidateId] = (array)$dbCandidate;
            }



            $dbAmendments = DB::table('amendments')->where('status', '1')->get();






            $arrDbAmendments = [];

            foreach ($dbAmendments as $dbAmendment) {


                $arrDbAmendments[$dbAmendment->amendmentId] = $dbAmendment;
            }



            $summaryAmendments = [];

            foreach ($arrConfirmCandidates["a"] as $amendment) {


                $summaryAmendments[] = array(
                    "amendment" => $arrDbAmendments[$amendment["id"]]->amendmentDesc,
                    "i" => $amendment["i"],
                    "n" => $amendment["n"],
                    "a" => $amendment["a"],
                );
            }

            $summary = [];

            if (isset($arrConfirmCandidates["c"])) {


                foreach ($arrConfirmCandidates["c"] as $candidate) {


                    $summary[] = array(
                        "name" => $arrDbCandidates[$candidate["id"]]["firstName"] . " " . $arrDbCandidates[$candidate["id"]]["middleName"] . " " . $arrDbCandidates[$candidate["id"]]["lastName"],
                        "vote" =>  $candidate["v"]
                    );
                }
            }



            ActivityController::log(["code" => "00074", "remarks" => "Viewed ballot summary confirmation", "ballotId" => $ballotInfo->ballotId]);



            return view('admin.ballot_form_confirmation', ["candidates" => $summary, 'amendments' => $summaryAmendments, "ballot" => $ballotInfo, "remarks" => $confirmationDetails->remarks]);
        } catch (Exception $e) {



            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }
    // findme

    public function view_ballot_form(Request $request)
    {

        try {


            $id = $request->route()->parameter('id');

            $ballotInfo = DB::table('ballots')->where('id', $id)->first();


            if ($ballotInfo === null) {


                return view('errors.response', ['message' => 'Bad Request', 'code' => 400]);
            }


            $ballotDetails = DB::table('ballot_details')->leftJoin('candidates', 'candidates.candidateId', '=', 'ballot_details.candidateId')->where('ballotId', $ballotInfo->ballotId)->get();

            $ballotAmendmentDetails = DB::table("ballot_amendments")->where("ballotId", $ballotInfo->ballotId)->get();



            $dbAmendments = DB::table("amendments")->where('status', 1)->get();

            $arrAmendments = [];

            foreach ($dbAmendments as $dbAmendment) {

                $arrAmendments[$dbAmendment->amendmentId] = $dbAmendment;
            }


            $arrBallotDetails =  [];

            foreach ($ballotAmendmentDetails as $amendDetail) {

                $arrBallotDetails[] = array(

                    "amendment" => $arrAmendments["$amendDetail->amendmentId"]->amendmentDesc,
                    "inFavor"  => ($amendDetail->inFavor > 0) ? "checked" : "",
                    "notFavor"  => ($amendDetail->notFavor > 0) ? "checked" : "",
                    "abstain"   => ($amendDetail->abstain > 0) ? "checked" : "",

                );
            }

            ActivityController::log(["code" => "00073", "remarks" => "Viewed ballot form", "ballotId" => $ballotInfo->ballotId]);


            return view('admin.ballot_vote_in_person', ['ballot' => $ballotInfo, 'ballotDetails' => $ballotDetails, "amendments" => $arrBallotDetails]);
        } catch (Exception $e) {

            return view('errors.response', ['code' => 500, 'message' => null]);
        }
    }

    // done 2022-08-26
    public function check_date_in_person(Request $request)
    {

        try {

            $setting = EApp::setting();


            if ($setting === null) {

                return response()->json(['message' => EApp::SERVER_ERROR], 500);
            }


            if ($setting->vote_in_person_start === null) {


                \Log::channel('evoting')->info('The date for voting in person has not yet been posted by the system administrator.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


                ActivityController::log(['code' => '00046', 'remarks' => 'The date for voting in person has not yet been posted by the system administrator.']);


                return response()->json(['message' => "The date for voting in person has not yet been posted by the system administrator."], 400);
            }


            $startFormat = date('F d, Y g:i:s A', strtotime($setting->vote_in_person_start));
            $endFormat   = date('F d, Y g:i:s A', strtotime($setting->vote_in_person_end));



            if (!EApp::between_date($setting->vote_in_person_start, $setting->vote_in_person_end)) {


                \Log::channel('evoting')->info('VOTE IN PERSON: ' . "Voting in person is available from $startFormat  to  $endFormat.", ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                ActivityController::log(['code' => '00046', 'remarks' => "Voting in person is available from $startFormat to $endFormat."]);

                return response()->json(['message' =>  "Voting in person is available from $startFormat  to  $endFormat."], 400);
            }

            return response()->json(['message' => 'Success', 'code' => 200], 200);
        } catch (Exception $e) {

            \Log::channel('evoting')->critical('VOTE IN PERSON: Exception | ' . $e->getMessage(), ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

            return response()->json(['message' => EApp::SERVER_ERROR], 200);
        }
    }

    // done 2022-08-26
    public function check_date_proxy()
    {

        try {

            $setting    = EApp::setting();


            if ($setting->vote_by_proxy_start === null) {


                \Log::channel('evoting')->info('The date for voting by proxy has not yet been posted by the system administrator.', ["userId" => Auth::user()->id, "email" => Auth::user()->email]);


                ActivityController::log(['code' => '00046', 'remarks' => 'The date for voting by proxy has not yet been posted by the system administrator.']);


                return response()->json(['message' => "The date for voting by proxy has not yet been posted by the system administrator."], 400);
            }


            $startFormat = date('F d, Y g:i:s A', strtotime($setting->vote_by_proxy_start));
            $endFormat   = date('F d, Y g:i:s A', strtotime($setting->vote_by_proxy_end));


            if (!EApp::between_date($setting->vote_by_proxy_start, $setting->vote_by_proxy_end)) {


                \Log::channel('evoting')->info("Voting by proxy is available from $startFormat to  $endFormat.", ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

                ActivityController::log(['code' => '00046', 'remarks' => "Voting by proxy is available from $startFormat  to  $endFormat."]);

                return response()->json(['message' => "Voting by proxy is available from $startFormat to  $endFormat."], 400);
            }

            return response()->json(['message' => 'Success', 'code' => 200], 200);
        } catch (Exception $e) {

            \Log::channel('evoting')->critical('VOTE BY PORXY: Exception | ' . $e->getMessage(), ["userId" => Auth::user()->id, "email" => Auth::user()->email]);

            return response()->json(['message' => EApp::SERVER_ERROR], 200);
        }
    }

    public function ballots_result()
    {

        $arrDbCandidates = [];

        $dbCandidates = DB::table("candidates")->where("status", "1")->orderBy("lastName", "asc")->orderBy("middleName", "asc")->get();


        foreach ($dbCandidates as $dbCandidate) {

            $arrDbCandidates[$dbCandidate->candidateId] = array(

                "candidateId" => $dbCandidate->candidateId,
                "candidate" => $dbCandidate->firstName . " " . $dbCandidate->middleName . " " . $dbCandidate->lastName

            );
        }


        $dbBallots = DB::table("ballots")->where("status", "1")->orderBy("ballots.id")->get();


        $arrDbBallots = [];



        foreach ($dbBallots as $dbBallot) {

            $arrDbBallots[$dbBallot->ballotType][] = array(

                "id" => $dbBallot->id,
                "ballotNo" => $dbBallot->ballotNo,
                "accountKey" => $dbBallot->accountKey

            );
        }



        echo '<table><thead><th>Ballot #</th><th>Account No</th><th>Account Key</th><th>Emaail</th><th>Date Submitted</th><th>Revoked</th><th>Available Votes</th><th>Unused</th>';


        foreach ($arrDbCandidates as $candidateHeader) {

            echo '<th>' . $candidateHeader["candidate"] . '</th>';
        }

        echo '</thead>';




        echo '</table>';


        echo '<pre>';
        // print_r($dbCandidates);


        print_r($dbBallots);

        print_r($arrDbBallots);
        echo '</pre>';
    }


    public function void_vote(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'id'                => 'required|numeric',
                'void'              => 'required|numeric',
                'remarks'           => 'required|string',

            ]);



            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());

                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            $id             = $request->input('id');
            $voidVote       = $request->input('void');
            $remarks        = $request->input('remarks');


            $ballotDetails = DB::table("ballot_details")->where("id", $id)->first();

            if ($ballotDetails === null) {

                return response()->json(["message" => "Details not found"], 400);
            }

            if (($ballotDetails->vote - $voidVote) < 0) {

                return response()->json(["message" => "Value is not valid"], 400);
            }


            $dbUpdate = DB::table('ballot_details')->where('id', $id)->update([
                'voidedVote'     => $voidVote,
                'voidRemarks'    => htmlspecialchars($remarks)
            ]);


            if ($dbUpdate !== 1) {

                return response()->json(["message" => "An error encountered while updating the record"], 500);
            }


            ActivityController::log(["code" => "00075", "remarks" => "Voided a vote [id: " . $id . ", void: " . $voidVote . ", new value: " . ($ballotDetails->vote - $voidVote) . "]. Reason: " . htmlspecialchars($remarks), "ballotId" => $ballotDetails->ballotId, "ballotDetailsId" => $id]);

            return response()->json(["message" => "Vote has been successfully voided."], 200);
        } catch (Exception $e) {

            return response()->json(["message" => EApp::SERVER_ERROR], 500);
        }
    }


    public function void_amendment(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'id'                => 'required|string',
                'void'              => 'required|numeric',
                'remarks'           => 'required|string',

            ]);



            if ($validator->fails()) {

                $err = EApp::obj_to_array($validator->errors());

                return response()->json(["message" => $err[array_key_first($err)][0], "field" => array_key_first($err)], 400);
            }


            $id             = $request->input('id');
            $voidVote       = $request->input('void');
            $remarks        = $request->input('remarks');


            $ballotDetails = DB::table("ballot_details")->where("ballotId", $id)->first();

            if ($ballotDetails === null) {

                return response()->json(["message" => "Details not found"], 400);
            }


            //update validation to what is allowed to be voided
            // if(($ballotDetails->vote - $voidVote) < 0) {

            //     return response()->json(["message" => "Value is not valid"], 400);

            // }


            $dbUpdate = DB::table('ballot_amendments')->where('ballotId', $id)->update([

                'voidedVote'     => $voidVote,

            ]);


            if ($dbUpdate === 0) {

                return response()->json(["message" => "An error encountered while updating the record"], 500);
            }


            ActivityController::log(["code" => "00075", "remarks" => "Voided an amendment vote [ballotID: " . $id . ", void: " . $voidVote . "]. Reason: " . htmlspecialchars($remarks), "ballotId" => $id]);

            return response()->json(["message" => "Amendment vote has been successfully voided."], 200);
        } catch (Exception $e) {

            return response()->json(["message" => EApp::SERVER_ERROR], 500);
        }
    }
}
