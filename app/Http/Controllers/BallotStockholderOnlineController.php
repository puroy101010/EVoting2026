<?php

namespace App\Http\Controllers;


use App\Models\Agenda;
use App\Models\BallotStockholderOnline;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\Amendment;
use App\Models\Attendance;
use App\Models\Ballot;
use App\Models\BallotAgenda;
use App\Models\BallotAmendment;
use App\Models\BallotConfirmation;
use App\Models\BallotDetail;
use App\Models\Configuration;
use App\Models\StockholderAccount;
use App\Models\UsedAmendmentAccount;
use App\Models\UsedBoardOfDirectorAccount;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\Rule;



class BallotStockholderOnlineController extends Controller
{

    private $ballotInfo;
    private $availableVotes;


    // done 2023-09-18


    // done 2023-09-18









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





    // done 2023-09-23
    function insertConfirmation($data, $isValidBallot)
    {

        $confirmationId = EApp::generate_id('ballot_confirmations', 'confirmationId');

        BallotConfirmation::insert([
            'confirmationId' => $confirmationId,
            'ballotId'       => $this->ballotInfo->ballotId,
            'ballotType'        => 'person',
            'isValidBallot'     => $isValidBallot,
            'data'              => json_encode($data),
            'availableVotes'    => json_encode($this->availableVotes),
            'createdBy'         => Auth::id()

        ]);

        return $confirmationId;
    }




    //done 2023-09-18
    public function validateAmendmentForm($amendmentData)
    {

        foreach ($amendmentData as $amendment) {

            $selectedVotes = (int) $amendment['yes'] + (int) $amendment['no'];

            if ($selectedVotes > 1) {

                throw new Exception('Form validation error: Each amendment must have only one vote, either "yes" or "no", but not both.');
            }
        }

        return true;
    }

    //done 2023-09-18

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





    public function stockholderOnlineDay($log, $formType)
    {

        $currentDateTime = Carbon::now();

        $setting = AppController::app_setting();

        $startDateTime = $setting['vote_in_person_start'];
        $endDateTime = $setting['vote_in_person_end'];

        if ($startDateTime === null || $endDateTime === null) {
            $message = 'The administrator has yet to set the voting period for Stockholder Online. If you think this is an error, please contact your admin.';

            $insertLog = [

                'remarks' => $message,

            ];

            Log::channel('ballot')->info($message);
            ActivityController::log(array_merge($insertLog, $log));

            return $message;
        }


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


            Log::channel('ballot')->info($message);

            $insertLog = [

                'remarks' => $message,

            ];
            ActivityController::log(array_merge($insertLog, $log));

            return $message;
        }

        return true;
    }


    // done 2023-09-18

    public function checkRecordChanges($userSubmittedData, $activityCode)
    {

        $availableAccounts['bod'] =  $this->getAvailableVotes('bod');
        $availableAccounts['amendment'] =  $this->getAvailableVotes('amendment');
        $availableAccounts['both'] =  $this->getAvailableVotes('both');
        $availableAccounts['none'] =  $this->getAvailableVotes('none');

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





    public function getTotalBodVotes($bodData)
    {

        $totalBodVotes = 0;

        foreach ($bodData as $bod) {

            $totalBodVotes += (int)$bod['vote'];
        }


        return $totalBodVotes;
    }






    // done 23-09-2023
    public function recordUnusedVotes($unusedVotes, $userSubmittedDate)
    {

        $message = "You still have $unusedVotes vote(s) that haven't been distributed. If you confirm, the form will be submitted, and the remaining undistributed votes will be marked as unused votes and will no longer be available.";

        $confirmationId = $this->insertConfirmation(array_merge(array('message' => $message), $userSubmittedDate), true);

        Log::channel("ballot")->info($message, ["id" => Auth::user()->id, "email" => Auth::user()->email]);

        ActivityController::log([
            'activityCode' => '00090',
            'remarks' => $message,
            'ballotId' => $this->ballotInfo->ballotId,
            'confirmationId' =>  $confirmationId

        ]);

        return $confirmationId;
    }


    public function validateDistributedVotes($unusedVotes, $totalBodVotes, $data)
    {

        if ($unusedVotes < 0) {

            $message = "The total distributed votes should not exceed $totalBodVotes votes.";

            $confirmationId = $this->insertConfirmation(array_merge(array('message' => $message), $data), false);

            ActivityController::log(['activityCode' => '00090', 'remarks' => $message, 'ballotId' => $this->ballotInfo->ballotId, 'confirmationId' => $confirmationId]);

            return $message;
        }


        return true;
    }

    public function summary(Request $request)
    {

        try {



            $this->setBallotPropery($request->ballotId);


            $loggerDetails = [
                'id' => Auth::id(),
                'email' => Auth::user()->email,
                'ballotId' => $request->ballotId
            ];

            $data  = array(
                'bod' => $request->input('bod'),
                'amendment' => $request->amendment,
                'agenda' => $request->agenda
            );

            Log::channel('ballot')->info('Summary is requested', array_merge($loggerDetails, ['data' => $data]));

            $checkDate = $this->stockholderOnlineDay(['ballotId' => $request->ballotId, 'activityCode' => '00077'], 'Stockholder Online');

            if ($checkDate !== true) {
                return response()->json(['message' => $checkDate, 'loadUrl' => 'user/vote'], 400);
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

            $checkRecordChanges = $this->checkRecordChanges($data, '00082');

            if ($checkRecordChanges !== true) {

                $msg = 'There have been changes to the accounts. Please refresh the page.';
                Log::channel('ballot')->warning($msg, $loggerDetails);
                ActivityController::log(['activityCode' => '00090', 'remarks' => $msg, 'ballotId' => $request->ballotId]);

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
            Log::channel('ballot')->info('Summary for stockholder online voting has been provided', $loggerDetails);


            ActivityController::log(['activityCode' => '00090', 'remarks' => $message, 'confirmationId' => $confirmationId, 'ballotId' => $request->ballotId]);

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

            Log::channel('ballot')->critical($e->getMessage(), $loggerDetails);
            Log::critical($e);
            return response()->json([], 500);
        }
    }



    public function setBallotPropery($ballotId)
    {
        $this->ballotInfo =  Ballot::where('isSubmitted', false)->where('ballotType', 'person')->where('createdBy', Auth::id())->findOrFail($ballotId);

        $this->availableVotes = $this->getAvailableVotes($this->ballotInfo->revoked);
    }




    // done 2023-09-19
    public function validConfimation($unusedVotes, $confirmation, $data)
    {

        $message = 'You are about to submit the ballot. Once confirmed, it will be submitted and cannot be undone. A confirmation email will be sent to your registered email address.';

        if ($unusedVotes > 0) {

            $message = "You still have $unusedVotes vote(s) that haven't been distributed. If you confirm, the form will be submitted, and the remaining undistributed votes will be marked as unused votes and will no longer be available.";
        }


        $data = array_merge(['message' => $message], $data);

        if (json_encode($data) !== $confirmation->data) {

            $message = 'The user tried to submit a ballot, but the votes did not match the confirmation data.';

            ActivityController::log([
                'activityCode' => '00092',
                'remarks' => $message,
                'ballotId' => $this->ballotInfo->ballotId
            ]);

            return $message;
        }


        return true;
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


    public function submit(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [

                'ballotId' => 'required',
                'confirmationId' => 'required',

                'amendment' =>  config('evoting.enableAmendment') === true ? 'required|array' : 'nullable|array',
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


            $checkDate = $this->stockholderOnlineDay(['ballotId' => $request->ballotId, 'activityCode' => '00078'], 'Stockholder Online');

            if ($checkDate !== true) {

                Log::channel('ballot')->info($checkDate, $loggerDetails);

                return response()->json(['message' => $checkDate], 400);
            }


            $checkRecordChanges = $this->checkRecordChanges($data, '00083');

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

                if ((int) $bod['vote'] === 0) {

                    continue;
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

            if ($this->insertUsedAccounts($confirmation) !== true) {

                return response()->json([], 500);
            }


            Log::channel('ballot')->info('Ballot submitted', $loggerDetails);


            ActivityController::log(['activityCode' => '00094', 'ballotId' => $this->ballotInfo->ballotId, 'userId' => Auth::id()]);


            DB::commit();


            if (config('evoting.enableSendVoteConfirmation') === true) {

                VotingConfirmationController::Send_Voting_Confirmation(Auth::user()->email);

                Log::channel('ballot')->info('Email confirmation has been sent', $loggerDetails);
            }


            return response()->json([
                'message' => 'Your vote has been successfully submitted.'
            ], 200);
        } catch (Exception $e) {

            Log::channel('ballot')->critical($e->getMessage());

            Log::critical($e);
            return response()->json([], 500);
        }
    }






    /**
     * Display the specified resource.
     *
     * @param  \App\BallotStockholderOnline  $ballotStockholderOnline
     * @return \Illuminate\Http\Response
     */
    public function show(BallotStockholderOnline $ballotStockholderOnline)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BallotStockholderOnline  $ballotStockholderOnline
     * @return \Illuminate\Http\Response
     */
    public function edit(BallotStockholderOnline $ballotStockholderOnline)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\BallotStockholderOnline  $ballotStockholderOnline
     * @return \Illuminate\Http\Response
     */
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
     * @param  \App\BallotStockholderOnline  $ballotStockholderOnline
     * @return \Illuminate\Http\Response
     */
    public function destroy(BallotStockholderOnline $ballotStockholderOnline)
    {
        //
    }




    public function ballot($id) {}
}
