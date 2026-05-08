<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EApp;
use App\Models\NonMemberAccount;
use App\Models\ProxyAmendment;
use App\Models\ProxyAmendmentHistory;
use App\Models\ProxyBoardOfDirector;
use App\Models\ProxyBoardOfDirectorHistory;
use App\Models\StockholderAccount;
use App\Models\User;
use App\Stockholder;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProxyService
{


    protected $settings = [];

    public function __construct()
    {
        $this->settings = ConfigService::getConfig();
    }





    public static function getHistory($request, int $id, string $proxyType)
    {

        switch ($proxyType) {
            case 'BOD':
                return ProxyBoardOfDirectorHistory::with(['stockholderAccount.user', 'stockholderAccount.stockholder', 'cancellationBy'])->where('accountId', $id)->get();
            case 'Amendment':
                return ProxyAmendmentHistory::with(['stockholderAccount.user', 'stockholderAccount.stockholder', 'cancellationBy'])->where('accountId', $id)->get();
            default:
                return collect();
        }
    }



    public static function processHistory(Request $request, int $id)
    {

        $proxyType = $request->input('proxyType');

        if (!in_array($proxyType, ['BOD', 'Amendment'])) {
            Log::error("Invalid proxy type: $proxyType");
            throw new Exception("Invalid proxy type: $proxyType");
        }


        $histories = self::getHistory($request, $id, $proxyType);


        $historyArr = [];

        foreach ($histories as $history) {
            $historyArr[] = [
                'id' => $proxyType === 'BOD' ? $history->proxyBodId : $history->proxyAmendmentId,
                'formNo' => $proxyType === 'BOD' ? $history->proxyBodFormNo : $history->proxyAmendmentFormNo,
                'assignorName' => $history->assignorName,
                'assignorType' => null,
                'assigneeName' => $history->assigneeName,
                'assigneeType' => null,
                'status' => $history->status,
                'cancellationReason' => $history->reason,
                'cancellationBy' => $history->cancellationBy ? $history->cancellationBy->adminAccount->firstName . ' ' . $history->cancellationBy->adminAccount->lastName : null,
                'cancelledAt' => $history->cancelledAt,
                'remarks' => $history->remarks,
                'createdAt' => $history->createdAt,
            ];
        }

        return $historyArr;
    }

    public function getAssignorByAccountNo(int $assignorUserId, int $stockholderAccountId): User
    {
        try {

            $stockholderAccount = StockholderAccount::with('stockholder')->where('accountId', $stockholderAccountId)->first();

            if (!$stockholderAccount || !$stockholderAccount->stockholder) {
                throw new Exception("Account or stockholder not found for account ID {$stockholderAccountId}");
            }

            $targetAccountNo = $stockholderAccount->stockholder->accountNo;
            $assignorUser = self::findAssignorByAccountNumber($assignorUserId, $stockholderAccountId, $targetAccountNo);


            if (!$assignorUser) {
                throw new Exception("No assignor user found with account ID '{$stockholderAccountId}' and user ID {$assignorUserId}");
            }

            if ($stockholderAccount->stockholder->accountType === 'indv') {
                return $stockholderAccount->stockholder->user;
            }

            return $assignorUser;
        } catch (Exception $e) {

            throw new Exception("Failed to fetch assignor user for account ID '{$stockholderAccountId}' with user ID {$assignorUserId}: " . $e->getMessage());
        }
    }

    private static function findAssignorByAccountNumber(int $assignorUserId, int $stockholderAccountId, string $targetAccountNo): ?User
    {


        if (!$targetAccountNo) {
            Log::error("Account number not found", ['accountId' => $stockholderAccountId]);
            throw new Exception("Account number not found for account ID {$stockholderAccountId}");
        }

        // Try to find as stockholder first
        $assignorUser = User::where('id', $assignorUserId)
            ->where('role', 'stockholder')
            ->whereHas('stockholder', function ($query) use ($targetAccountNo) {
                $query->where('accountNo', $targetAccountNo);
            })
            ->first();

        // If not stockholder, try corporate representative
        if (!$assignorUser) {
            $assignorUser = User::where('id', $assignorUserId)
                ->where('role', 'corp-rep')
                ->whereHas('stockholderAccount.stockholder', function ($query) use ($targetAccountNo) {
                    $query->where('accountNo', $targetAccountNo);
                })
                ->first();
        }

        return $assignorUser;
    }

    public function validateAssignee(Request $request): User
    {


        $assigneeUser = User::whereIn('role', ['stockholder', 'corp-rep', 'non-member'])->findOrFail($request->assignee);

        // Check GM restriction for non-members and stockholders
        if ($assigneeUser->role !== 'non-member' && $this->settings['amendment_restricted_to_gm'] === 1) {
            throw new ValidationErrorException("Only the General Manager can be assigned as the amendment proxyholder.");
        }


        // Non-member specific validations
        if ($assigneeUser->role === 'non-member') {
            $this->validateNonMemberAssignee($assigneeUser);
        }


        // General email validation for all assignees
        if ($assigneeUser->email === null) {
            throw new ValidationErrorException("Assignee does not have a registered email address.");
        }

        return $assigneeUser;
    }

    private function validateNonMemberAssignee(User $assignee): void
    {
        $nonmemberInfo = $assignee->nonMemberAccount()->withTrashed()->first();

        if (!$nonmemberInfo) {
            throw new ValidationErrorException("Assignee non-member account not found.");
        }

        if ($nonmemberInfo->trashed()) {
            throw new ValidationErrorException("Assignment to an inactive non-member is not allowed. Please reactivate the non-member to proceed.");
        }

        if ($nonmemberInfo->isGM !== 1) {
            throw new ValidationErrorException("The amendment proxyholder is restricted to the General Manager.");
        }
    }

    public function validateAssignor(Request $request): User
    {

        $submittedAssignorUserId = $request->assignor;
        $submittedAccountIdToAssign = $request->accountToAssign;

        $stockholderAccountToAssign = StockholderAccount::find($submittedAccountIdToAssign);

        if (!$stockholderAccountToAssign) {
            throw new ValidationErrorException("The stockholder account to assign was not found.");
        }


        $assignorUser = $this->getAssignorByAccountNo($submittedAssignorUserId, $submittedAccountIdToAssign);

        if ($assignorUser->role === 'corp-rep') {

            if ($assignorUser->email === null) {
                throw new ValidationErrorException("Unable to assign proxy. The assignor does not have a registered email address.");
            }

            if ($assignorUser->email !== $stockholderAccountToAssign->user->email) {

                throw new ValidationErrorException("Proxy assignment failed. Corporate representatives must assign shares under their own registered name.");
            }
        }

        return $assignorUser;
    }

    public function getAuditAction(int $action)
    {
        if (!in_array($action, [0, 1])) {
            throw new ValidationErrorException("Invalid audit action.");
        }

        return $action == 1 ? 'verify' : 'unverify';
    }

    public function validateAuditRule(ProxyAmendment|ProxyBoardOfDirector $proxy, string $action)
    {

        if ($proxy->auditedBy === null and  $action === 'unverify') {

            throw new ValidationErrorException("Verification status cannot be revoked because the proxy has not yet been verified.");
        }

        if ($proxy->auditedBy !== null and  $action === 'verify') {
            throw new ValidationErrorException("You are trying to verify a proxy that has already been verified.");
        }
    }

    public function verifyProxy(ProxyAmendment|ProxyBoardOfDirector $proxy)
    {

        $permissionCheck = $proxy instanceof ProxyAmendment ? 'verify amendment proxy' : 'verify bod proxy';


        if (Auth::user()->cannot($permissionCheck)) {
            throw new ValidationErrorException("You are not authorized to verify this " . ($proxy instanceof ProxyAmendment ? "amendment" : "BOD") . " proxy.");
        }

        $proxy->auditedBy = Auth::id();
        $proxy->auditedAt = now();
        $proxy->save();
    }


    public function unverifyProxy(ProxyAmendment|ProxyBoardOfDirector $proxy)
    {

        $permissionCheck = $proxy instanceof ProxyAmendment ? 'remove amendment proxy audit' : 'remove bod proxy audit';

        if (Auth::user()->cannot($permissionCheck)) {
            throw new ValidationErrorException("You do not have permission to revoke the verified status for this " . ($proxy instanceof ProxyAmendment ? "amendment" : "BOD") . " proxy.");
        }

        $proxy->auditedBy = null;
        $proxy->auditedAt = null;
        $proxy->save();
    }
}
