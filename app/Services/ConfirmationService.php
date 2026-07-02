<?php

namespace App\Services;

use App\Http\Controllers\ActivityController;
use App\Models\Ballot;
use App\Models\BallotConfirmation;
use App\Models\NonMemberAccount;
use App\Models\Stockholder;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfirmationService
{


    public function store(Ballot $ballotInfo, array $userSubmittedData, bool $isValidBallot, string $message): BallotConfirmation
    {

        $stockholderOnlineBallotService = new StockholderOnlineBallotService();
        $proxyVotingBallotService = new ProxyVotingBallotService();

        $availableVotes = $ballotInfo->ballotType === 'person' ?
            $stockholderOnlineBallotService->getAvailableVotes($ballotInfo->revoked, Auth::user()) :
            $proxyVotingBallotService->getAvailableVotes(Auth::user());

        $availableVotesJson = json_encode($availableVotes);
        $userSubmittedData['message'] = $message;

        $ballotConfirmation = BallotConfirmation::create([
            'ballotId'       => $ballotInfo->ballotId,
            'ballotType'     => $ballotInfo->ballotType,
            'isValidBallot'  => $isValidBallot,
            'data'           => json_encode($userSubmittedData),
            'availableVotes' => $availableVotesJson,
            'email'          => Auth::user()->email,
            'remarks'        => $message,
            'ip'             => request()->ip(),
            'createdBy'      => Auth::id()
        ]);

        return $ballotConfirmation;
    }


    public static function ensureBallotConfirmationIsValid(Ballot $ballotInfo, int $confirmationId): BallotConfirmation
    {
        $votingType = UtilityService::getVotingType($ballotInfo->ballotType);
        $confirmation = BallotConfirmation::findOrFail($confirmationId);

        $activityCode = $ballotInfo->ballotType === 'person' ? '00092' : '00093';

        if ($ballotInfo->ballotId !== $confirmation->ballotId) {
            Log::error('Mismatch between ballot and confirmation data', [
                'ballotId' => $ballotInfo->ballotId,
                'confirmationId' => $confirmationId,
            ]);

            ActivityController::log([
                'activityCode' => $activityCode,
                'remarks' => 'Mismatch between ballot and confirmation data during submission attempt',
                'userId' => Auth::id(),
                'ballotId' => $ballotInfo->ballotId,
                'confirmationId' => $confirmationId,
                'email' => Auth::user()->email,
                'accountNo' => Auth::user()->account_no,
            ]);

            throw new Exception('The ballot confirmation data does not match the ballot. Please reload the page to generate a new ballot and try again.');
        }

        if ($confirmation->isValidBallot === 0) {

            Log::error('Attempt to submit an invalid ballot confirmation data', [
                'confirmationId' => $confirmationId,
                'ballotId' => $ballotInfo->ballotId,
            ]);

            ActivityController::log([
                'activityCode' => $activityCode,
                'remarks' => 'Alert: Attempted to submit an invalid ballot confirmation data',
                'userId' => Auth::id(),
                'ballotId' => $ballotInfo->ballotId,
                'confirmationId' => $confirmationId,
                'email' => Auth::user()->email,
                'accountNo' => Auth::user()->account_no,
            ]);
            throw new Exception('The ballot confirmation data is marked as invalid. Please reload the page to generate a new ballot and try again.');
        }

        Log::info("{$votingType}: Ballot confirmation data is valid for confirmation ID " . $confirmationId, ['confirmationId' => $confirmationId]);
        return $confirmation;
    }



    /**
     * Create a new BallotConfirmation record when the available votes have changed since the user generated their ballot.
     * This method is called when the user attempts to submit their ballot, but the available votes have changed since they generated their ballot.
     * It creates a new BallotConfirmation record with the updated available votes and a message indicating that the available votes have changed.
     * Sets the isValidBallot field to false to indicate that the ballot is no longer valid.
     */
    public static function createAvailableVoteChangeRecord(Ballot $ballot, array $userSubmittedData): BallotConfirmation
    {


        $votingType = UtilityService::getVotingType($ballot->ballotType);

        $message = "{$votingType}: Your available accounts have changed since you generated your ballot. Please reload the page.";

        $confirmationService = new ConfirmationService();
        $ballotConfirmation = $confirmationService->store($ballot, $userSubmittedData, false, $message);

        return $ballotConfirmation;
    }
}
