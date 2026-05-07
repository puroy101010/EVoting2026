<?php

namespace App\Services;

use App\Http\Controllers\ActivityController;
use App\Models\NonMemberAccount;
use App\Models\ProxyAmendmentHistory;
use App\Models\ProxyBoardOfDirectorHistory;
use App\Models\StockholderAccount;
use App\Models\User;
use App\Stockholder;

use Exception;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProxyService
{

    public static function getHistory($request, $id, $proxyType)
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



    public static function processHistory($request, $id)
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

    public static function getAssignorByAccountNo(int $assignorUserId, int $stockholderAccountId): User
    {
        try {
            $assignorUser = self::findAssignorByAccountNumber($assignorUserId, $stockholderAccountId);
            $targetAccountNo = StockholderAccount::where('accountId', $stockholderAccountId)->value('accountNo');

            if (!$assignorUser) {
                Log::error("Assignor not found", ['userId' => $assignorUserId, 'accountId' => $stockholderAccountId]);
                throw new Exception("No assignor user found with account ID '{$stockholderAccountId}' and user ID {$assignorUserId}");
            }

            if ($targetAccountNo->stockholder->accountType === 'indv') {
                return $targetAccountNo->stockholder->user;
            }

            return $assignorUser;
        } catch (Exception $e) {
            Log::error("Failed to fetch assignor user", ['userId' => $assignorUserId, 'accountId' => $stockholderAccountId, 'error' => $e->getMessage()]);
            throw new Exception("Failed to fetch assignor user for account ID '{$stockholderAccountId}' with user ID {$assignorUserId}: " . $e->getMessage());
        }
    }

    private static function findAssignorByAccountNumber(int $assignorUserId, int $stockholderAccountId): ?User
    {
        $targetAccountNo = StockholderAccount::where('accountId', $stockholderAccountId)->value('accountNo');

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
}
