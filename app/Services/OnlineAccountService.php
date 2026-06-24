<?php

namespace App\Services;


use App\Http\Controllers\ActivityController;

use App\Models\ProxyAmendment;
use App\Models\ProxyAmendmentCancelled;
use App\Models\ProxyAmendmentHistory;
use App\Models\ProxyBoardOfDirector;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use  App\Http\Requests\EditOnlineAccountRequest;
use App\Http\Requests\ShowOnlineAccountRequest;

class OnlineAccountService
{

    /**
     * Display a listing of all online accounts grouped and reconciled by email.
     */
    public function index(Request $request): \Illuminate\View\View
    {



        $onlineAccounts = $this->getOnlineAccountsQuery()->get();
        $accountsByEmailAndName = $this->groupAccountsByEmailAndName($onlineAccounts);

        // Extract unique email and its first account name
        $uniqueEmailsAndNames = $this->extractUniqueEmailsWithNames($accountsByEmailAndName);

        return view('admin.online-accounts', [
            'onlineAccounts' => $uniqueEmailsAndNames
        ]);
    }

    public function update(EditOnlineAccountRequest $request, string $oldEmail)
    {
        try {
            $validatedData = $request->validated();
            $newName = $validatedData['name'];
            $newEmail = $validatedData['email'];

            $affectedUsers = User::where('email', $oldEmail)
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->get();

            if ($affectedUsers->isEmpty()) {
                return response()->json(['message' => 'No accounts found for this email'], 404);
            }

            UserService::validateEmailConflicts($affectedUsers->pluck('id')->toArray(), $newEmail, $newName);

            DB::beginTransaction();

            try {
                $this->updateProxyRecords($oldEmail, $newEmail, $newName);
                $logs = $this->prepareActivityLogs($affectedUsers, $newEmail, $newName);
                if (empty($logs)) {
                    return response()->json(['message' => 'No changes detected to update'], 200);
                }
                $this->executeUserUpdates($affectedUsers, $newEmail, $newName);
                $this->createActivityLogs($logs);

                DB::commit();

                return response()->json(['message' => 'Accounts updated successfully'], 200);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            AppHelper::logServerError("An error occurred while updating online account for email: {$oldEmail}. Error: " . $e->getMessage(), $e);
            return response()->json(['error' => 'Failed to update accounts'], 500);
        }
    }

    /**
     * Capture old values before updating a user account.
     */
    private function captureOldValues(User $user): array
    {
        return [
            'email' => $user->email,
            'name' => $user->authorized_signatory,
        ];
    }

    /**
     * Prepare activity logs for all affected users.
     */
    private function prepareActivityLogs(Collection $affectedUsers, string $newEmail, string $newName): array
    {
        $logs = [];

        foreach ($affectedUsers as $user) {
            $oldValues = $this->captureOldValues($user);

            $hasChanges = $this->hasChanges($oldValues, $newEmail, $newName);

            if ($hasChanges) {
                $logs[] = $this->buildActivityLog($user, $oldValues, $newEmail, $newName);
            }
        }

        return $logs;
    }

    /**
     * Check if there are actual changes to log.
     */
    private function hasChanges(array $oldValues, string $newEmail, string $newName): bool
    {
        return AppHelper::compareStrings($oldValues['email'], $newEmail) === false ||
            AppHelper::compareStrings($oldValues['name'], $newName) === false;
    }

    /**
     * Build a single activity log entry with conditional data.
     */
    private function buildActivityLog(User $user, array $oldValues, string $newEmail, string $newName): array
    {
        $data = [];

        // Only include changed fields
        if (AppHelper::compareStrings($oldValues['email'], $newEmail) === false) {
            $data['email'] = [
                'old' => $oldValues['email'],
                'new' => $newEmail,
            ];
        }

        if (AppHelper::compareStrings($oldValues['name'], $newName) === false) {
            $data['name'] = [
                'old' => $oldValues['name'],
                'new' => $newName,
            ];
        }

        return [
            'activityCode' => '00146',
            'userId' => $user->id,
            'accountNo' => $user->account_no ?? null,
            'email' => $oldValues['email'],
            'remarks' => $this->buildRemarks($oldValues['email'], $newEmail, $oldValues['name'], $newName),
            'data' => json_encode($data),
        ];
    }

    /**
     * Execute user account updates.
     */
    private function executeUserUpdates(Collection $affectedUsers, string $newEmail, string $newName): void
    {
        foreach ($affectedUsers as $user) {
            $this->updateUserAccount($user, $newEmail, $newName);
        }
    }

    /**
     * Create activity log records.
     */
    private function createActivityLogs(array $logs): void
    {
        foreach ($logs as $log) {
            ActivityController::log($log);
        }
    }



    /**
     * Build activity log remarks based on what changed.
     */
    private function buildRemarks(string $oldEmail, string $newEmail, string $oldName, string $newName): string
    {
        $changes = [];

        if (AppHelper::compareStrings($oldEmail, $newEmail) === false) {
            $changes[] = "email from {$oldEmail} to {$newEmail}";
        }

        if (AppHelper::compareStrings($oldName, $newName) === false) {
            $changes[] = "name from {$oldName} to {$newName}";
        }

        return 'Updated ' . implode(' and ', $changes) . ' for online account';
    }

    /**
     * Update all proxy records related to an email address.
     */
    private function updateProxyRecords(string $oldEmail, string $newEmail, string $newName): void
    {
        // Update ProxyAmendment records
        ProxyAmendment::where('assignorEmail', $oldEmail)
            ->update(['assignorEmail' => $newEmail, 'assignorName' => $newName]);

        ProxyAmendment::where('assigneeEmail', $oldEmail)
            ->update(['assigneeEmail' => $newEmail, 'assigneeName' => $newName]);

        // Update ProxyAmendmentHistory records
        ProxyAmendmentHistory::where('assignorEmail', $oldEmail)
            ->update(['assignorEmail' => $newEmail, 'assignorName' => $newName]);

        ProxyAmendmentHistory::where('assigneeEmail', $oldEmail)
            ->update(['assigneeEmail' => $newEmail, 'assigneeName' => $newName]);

        // Update ProxyAmendmentCancelled records
        ProxyAmendmentCancelled::where('assignorEmail', $oldEmail)
            ->update(['assignorEmail' => $newEmail, 'assignorName' => $newName]);

        ProxyAmendmentCancelled::where('assigneeEmail', $oldEmail)
            ->update(['assigneeEmail' => $newEmail, 'assigneeName' => $newName]);
    }

    /**
     * Update a user account and its related data.
     */
    private function updateUserAccount(User $user, string $newEmail, string $newName): void
    {
        $user->email = $newEmail;
        $user->save();

        match ($user->role) {
            'stockholder' => $this->updateStockholderData($user, $newName),
            'corp-rep' => $this->updateCorpRepData($user, $newName),
            'non-member' => $this->updateNonMemberData($user, $newName),
        };
    }

    private function updateStockholderData(User $user, string $newName): void
    {
        $stockholder = $user->stockholder;
        if (!$stockholder) {
            throw new Exception("Stockholder not found for user ID: {$user->id}");
        }

        if ($stockholder->accountType === 'indv') {
            $stockholder->stockholder = $newName;
        } else {
            $stockholder->authorizedSignatory = $newName;
        }
        $stockholder->save();
    }

    private function updateCorpRepData(User $user, string $newName): void
    {
        $account = $user->stockholderAccount;
        if (!$account) {
            throw new Exception("Stockholder account not found for user ID: {$user->id}");
        }

        $account->corpRep = $newName;
        $account->save();
    }

    private function updateNonMemberData(User $user, string $newName): void
    {
        $nonMember = $user->nonMemberAccount;
        if (!$nonMember) {
            throw new Exception("Non-member account not found for user ID: {$user->id}");
        }

        $nonMember->fullName = $newName;
        $nonMember->save();
    }

    /**
     * Extract unique emails with their first associated account name.
     */
    public function extractUniqueEmailsWithNames(array $accountsByEmailAndName): array
    {
        $uniqueEmailsAndNames = [];
        foreach ($accountsByEmailAndName as $email => $accounts) {

            $uniqueEmailsAndNames[$email] =   $accounts[array_key_first($accounts)][array_key_first($accounts[array_key_first($accounts)])][0]['accountName'] ?? '';
        }
        return $uniqueEmailsAndNames;
    }



    /**
     * Get the query for online accounts with necessary relationships loaded.
     */
    public function getOnlineAccountsQuery(?string $email = null, bool $canUseVoteOnly = false): Builder
    {
        $query = User::with('stockholder.stockholderAccounts', 'stockholderAccount.stockholder', 'nonMemberAccount')
            ->whereIn('role', ['stockholder', 'corp-rep'])
            ->when($canUseVoteOnly, function ($q) {
                $q->whereNotIn('role', ['non-member']);
            })
            ->whereNotNull('email');


        if ($email) {
            $query->where('email', $email);
        }

        return $query;
    }

    /**
     * Group accounts by email, then by account name, then by account number.
     */
    public function groupAccountsByEmailAndName(Collection $accounts): array
    {
        $groupedAccounts = [];

        foreach ($accounts as $account) {

            $accountData = $this->getAccountData($account);

            $accountNameKey = AppHelper::normalizeString($accountData['accountName'], true);
            $accountKeySha = sha1($accountNameKey);

            $groupedAccounts[$account->email][$accountKeySha][$accountData['accountNo']][] = [
                'userId' => $account->id,
                'accountNo' => $accountData['accountNo'],
                'accountKey' => $accountData['accountKey'],
                'accountName' => $accountData['accountName'],
                'email' => $account->email,
                'emailRole' => $accountData['emailRole'],
                'role' => $account->role,
                'accountType' => $accountData['accountType'],
                'stockholder' => $account?->stockholder?->toArray(), //Stockholder with stockholder_accounts relationship
                'stockholderName' => $account?->stockholder?->stockholder ?? $account?->stockholderAccount?->stockholder->stockholder ?? null,
                'voteInPerson' => $accountData['voteInPerson'],
            ];
        }

        return $groupedAccounts;
    }

    /**
     * Reconcile stockholder and corporate representative accounts.
     * If a stockholder account exists for an email/account, remove corp-rep accounts
     * and add authorized signatory accounts.
     */
    private function reconcileStockholderAndCorpRepAccounts(array $groupedAccounts, bool $canUseVoteOnly): array
    {
        foreach ($groupedAccounts as $email => $accountsByName) {

            if (count($accountsByName) > 1) {
                Log::warning("Multiple account names found for email {$email}. This may indicate data integrity issues. Account names: " . implode(', ', array_keys($accountsByName)));
                throw new Exception("Data integrity error: Multiple names found for email {$email}");
            }

            foreach ($accountsByName as $accountNameSha => $accountsByNumber) {
                foreach ($accountsByNumber as $accountNumber => $details) {
                    if ($this->hasStockholderRole($details, $canUseVoteOnly)) {
                        $groupedAccounts[$email][$accountNameSha][$accountNumber] = $this->processStockholderAccountGroup(
                            $details,
                            $email
                        );
                    } else {
                        $groupedAccounts[$email][$accountNameSha][$accountNumber] = $this->processCorpRepGroup(
                            $details,
                            $email,
                            $canUseVoteOnly
                        );
                    }
                }
            }
        }

        return $groupedAccounts;
    }


    private function hasStockholderRole(array $details, bool $canUseVoteOnly): bool
    {

        $stockholderRole = collect($details)->firstWhere('role', 'stockholder');

        if (!$stockholderRole) {
            return false;
        }

        return $canUseVoteOnly === false
            || $stockholderRole['voteInPerson'] === 'stockholder';
    }

    /**
     * Process a group of accounts where a stockholder role exists.
     * Removes corp-rep accounts and expands stockholder authorized signatories.
     */
    private function processStockholderAccountGroup(array $details, string $email): array
    {
        $detailsToAdd = [];

        foreach ($details as $detail) {
            if ($detail['role'] === 'corp-rep') {
                continue; // Skip corp-rep accounts if a stockholder account exists

            } elseif ($detail['role'] === 'stockholder') {

                if (empty($detail['stockholder']['stockholder_accounts'])) {
                    throw new Exception("Data integrity error: Stockholder account missing for email {$email}");
                }

                // Add authorized signatory accounts
                foreach ($detail['stockholder']['stockholder_accounts'] as $signatoryAccount) {
                    $detailsToAdd[] = [
                        'userId' => $signatoryAccount['userId'],
                        'accountNo' => $detail['stockholder']['accountNo'],
                        'accountKey' => $signatoryAccount['accountKey'],
                        'accountName' => $detail['accountName'],
                        'email' => $email,
                        'emailRole' => $detail['accountType'] === 'corp' ? 'authorized signatory' : 'stockholder',
                        'role' => $detail['role'], // retain the original role for the signatory account
                        'accountType' => $detail['accountType'],
                        'stockholder' => $detail['stockholder'], // retain the original stockholder data to make it available for the view
                        'stockholderName' => $detail['stockholderName'],
                        'voteInPerson' => $detail['voteInPerson']
                    ];
                }
            } else {
                throw new Exception("Unexpected role '{$detail['role']}' encountered for email {$email}. Expected 'stockholder' or 'corp-rep'.");
            }
        }


        return $detailsToAdd;
    }



    private function processCorpRepGroup(array $details, string $email, bool $canUseVoteOnly): array
    {
        $detailsToAdd = [];

        foreach ($details as $detail) {

            if ($canUseVoteOnly === true && $detail['voteInPerson'] === 'stockholder') {

                Log::info("Skipping corporate representative account for email {$email} because voting authorization is assigned to the stockholder account.", [
                    'accountNo' => $detail['accountNo'],
                    'voteInPerson' => $detail['voteInPerson'],
                    'role' => $detail['role'],
                ]);

                continue;
            }

            $detailsToAdd[] = [
                'userId' => $detail['userId'],
                'accountNo' => $detail['accountNo'],
                'accountKey' => $detail['accountKey'],
                'accountName' => $detail['accountName'],
                'email' => $email,
                'emailRole' => $detail['emailRole'],
                'role' => $detail['role'],
                'accountType' => $detail['accountType'],
                'stockholder' => $detail['stockholder'], // retain the original stockholder data to make it available for the view
                'stockholderName' => $detail['stockholderName'],
                'voteInPerson' => $detail['voteInPerson'],
            ];
        }


        return $detailsToAdd;
    }

    /**
     * Get account data for the given user.
     *
     * @param User $account
     * @return array{accountName: string, accountNo: string, accountType: string}
     * @throws \InvalidArgumentException
     */
    private function getAccountData(User $account): array
    {
        return match ($account->role) {
            'stockholder' => $this->getStockholderAccountData($account),
            'corp-rep' => $this->getCorpRepAccountData($account),
            'non-member' => $this->getNonMemberAccountData($account),
            default => throw new \InvalidArgumentException('Invalid account role: ' . $account->role),
        };
    }

    private function getStockholderAccountData(User $account): array
    {
        $stockholder = $account->stockholder;
        $accountName = $this->isCorporateAccount($account)
            ? $stockholder->authorizedSignatory
            : $stockholder->stockholder;

        return [
            'accountNo' => $stockholder->accountNo,
            'accountKey' => $stockholder->accountNo,
            'accountName' => $accountName,
            'emailRole' => $this->isCorporateAccount($account) ? 'authorized signatory' : 'stockholder',
            'accountType' => $stockholder->accountType,
            'voteInPerson' => $stockholder->voteInPerson,
        ];
    }

    private function getCorpRepAccountData(User $account): array
    {
        $stockholder = $account->stockholderAccount->stockholder;
        $accountName = $this->isCorporateAccount($account)
            ? $account->stockholderAccount->corpRep
            : $stockholder->stockholder;

        return [
            'accountName' => $accountName,
            'accountNo' => $account->stockholderAccount->stockholder->accountNo,
            'accountKey' => $account->stockholderAccount->accountKey,
            'emailRole' => 'corporate representative',
            'accountType' => $stockholder->accountType,
            'voteInPerson' => $stockholder->voteInPerson,
        ];
    }

    private function getNonMemberAccountData(User $account): array
    {
        $nonMemberAccountNo = $account->nonMemberAccount->nonmemberAccountNo;
        $accountName = $account->nonMemberAccount->fullName;

        return [
            'accountNo' => $nonMemberAccountNo,
            'accountKey' => $nonMemberAccountNo,
            'accountName' => $accountName,
            'emailRole' => 'non-member',
            'accountType' => 'non-member',
            'voteInPerson' => false,
        ];
    }


    /**
     * Check if the user account is a corporate account.
     * @param User $user
     * @return bool
     */
    private function isCorporateAccount(User $user): bool
    {
        return match ($user->role) {
            'stockholder' => $user->stockholder->accountType === 'corp',
            'corp-rep' => $user->stockholderAccount->stockholder?->accountType === 'corp',
            default => false,
        };
    }

    /**
     * Retrieve and reconcile stocks for a specific email address.
     */
    public function showStocks(ShowOnlineAccountRequest $request, string $email, bool $canUseVoteOnly = false): array
    {

        $onlineAccounts = $this->getOnlineAccountsQuery(email: $email, canUseVoteOnly: $canUseVoteOnly)->get();
        $accountsByEmailAndName = $this->groupAccountsByEmailAndName($onlineAccounts);
        $reconciledAccounts = $this->reconcileStockholderAndCorpRepAccounts($accountsByEmailAndName, $canUseVoteOnly);

        return $reconciledAccounts;
    }


    /**
     * 

     * Stockholder account user IDs associated with the logged-in user's email.
     * 
     * Setting the second parameter to true returns only accounts for which the
     * user is designated as the authorized online voter in the stockholder settings. 
     * 
     * When the second parameter is set to false, all associated accounts are
     * returned, regardless of the user's voting authorization status.
     */
    public function getAccounts(string $email, bool $canUseVoteOnly = true): array
    {

        $onlineAccounts = $this->getOnlineAccountsQuery(email: $email)->get();
        $accountsByEmailAndName = $this->groupAccountsByEmailAndName($onlineAccounts);
        $reconciledAccounts = $this->reconcileStockholderAndCorpRepAccounts($accountsByEmailAndName, $canUseVoteOnly);

        $userIds  = [];

        foreach ($reconciledAccounts as $accountsByEmail) {
            foreach ($accountsByEmail as $accountKey => $accountsByKey) {
                foreach ($accountsByKey as $accountNo => $accounts) {
                    foreach ($accounts as $account) {
                        $userIds[] = $account['userId'];
                    }
                }
            }
        }

        return $userIds;
    }




    /**
     * Retrieve proxies for a specific email address grouped by account and proxy type.
     * Includes both board of director proxies and amendment proxies.
     */
    public function showProxies(string $email): array
    {
        $bodProxies = ProxyBoardOfDirector::with('stockholderAccount.stockholder')
            ->where('assigneeEmail', $email)
            ->get();

        $amendmentProxies = ProxyAmendment::with('stockholderAccount.stockholder')
            ->where('assigneeEmail', $email)
            ->get();

        $proxies = [];

        // Process board of director proxies
        foreach ($bodProxies as $proxy) {
            $accountKey = $proxy->stockholderAccount->accountKey;
            if (!isset($proxies[$accountKey])) {
                $proxies[$accountKey] = [];
            }
            $proxies[$accountKey][] = [
                'accountKey' => $accountKey,
                'accountName' => $proxy->stockholderAccount->stockholder->stockholder,
                'proxyType' => 'bod',
                'proxyRole' => 'Board of Director Proxy',
            ];
        }

        // Process amendment proxies
        foreach ($amendmentProxies as $proxy) {
            $accountKey = $proxy->stockholderAccount->accountKey;
            if (!isset($proxies[$accountKey])) {
                $proxies[$accountKey] = [];
            }
            $proxies[$accountKey][] = [
                'accountKey' => $accountKey,
                'accountName' => $proxy->stockholderAccount->stockholder->stockholder,
                'proxyType' => 'amendment',
                'proxyRole' => 'Amendment Proxy',
            ];
        }

        return $proxies;
    }
}
