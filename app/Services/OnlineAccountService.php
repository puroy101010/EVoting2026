<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EApp;
use App\Models\NonMemberAccount;
use App\Models\ProxyAmendment;
use App\Models\ProxyAmendmentCancelled;
use App\Models\ProxyAmendmentHistory;
use App\Models\ProxyBoardOfDirector;
use App\Models\ProxyBoardOfDirectorHistory;
use App\Models\StockholderAccount;
use App\Models\User;
use App\Stockholder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use  App\Http\Requests\EditOnlineAccountRequest;

class OnlineAccountService
{

    /**
     * Display a listing of all online accounts grouped and reconciled by email.
     */
    public function index(Request $request): \Illuminate\View\View
    {

        if (!Auth::user()->can('view online accounts') && !Auth::user()->hasRole('superadmin')) {
            return view('errors.unauthorized', ['message' => 'You do not have permission to view online accounts.']);
        }

        $onlineAccounts = $this->getOnlineAccountsQuery()->get();
        $accountsByEmailAndName = $this->groupAccountsByEmailAndName($onlineAccounts);

        // Extract unique email and its first account name
        $uniqueEmailsAndNames = $this->extractUniqueEmailsWithNames($accountsByEmailAndName);

        return view('admin.online-accounts', [
            'onlineAccounts' => $uniqueEmailsAndNames
        ]);
    }

    public function update(EditOnlineAccountRequest $request, string $email)
    {
        try {
            $validatedData = $request->validated();
            $newName = $validatedData['name'];
            $newEmail = $validatedData['email'];

            $affectedUsers = User::where('email', $email)
                ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
                ->get();

            if ($affectedUsers->isEmpty()) {
                return response()->json(['message' => 'No accounts found for this email'], 404);
            }

            UserService::validateEmailConflicts($affectedUsers->pluck('id')->toArray(), $newEmail, $newName);

            DB::beginTransaction();

            try {
                $this->updateProxyRecords($email, $newEmail, $newName);
                $logs = $this->prepareActivityLogs($affectedUsers, $email, $newEmail, $newName);
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
            AppHelper::logServerError("An error occurred while updating online account for email: {$email}. Error: " . $e->getMessage(), $e);
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
            'name' => UserService::getAuthorizedSignatory($user),
        ];
    }

    /**
     * Prepare activity logs for all affected users.
     */
    private function prepareActivityLogs(Collection $affectedUsers, string $oldEmail, string $newEmail, string $newName): array
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
     * Get the account name for a user before update.
     */
    private function getAccountName(User $user): string
    {
        return match ($user->role) {
            'stockholder' => $user->stockholder?->stockholder ?? $user->stockholder?->authorizedSignatory ?? '',
            'corp-rep' => $user->stockholderAccount?->corpRep ?? '',
            'non-member' => $user->nonMemberAccount?->fullName ?? '',
            default => '',
        };
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
    private function updateProxyRecords(string $email, string $newEmail, string $newName): void
    {
        // Update ProxyAmendment records
        ProxyAmendment::where('assignorEmail', $email)
            ->update(['assignorEmail' => $newEmail, 'assignorName' => $newName]);

        ProxyAmendment::where('assigneeEmail', $email)
            ->update(['assigneeEmail' => $newEmail, 'assigneeName' => $newName]);

        // Update ProxyAmendmentHistory records
        ProxyAmendmentHistory::where('assignorEmail', $email)
            ->update(['assignorEmail' => $newEmail, 'assignorName' => $newName]);

        ProxyAmendmentHistory::where('assigneeEmail', $email)
            ->update(['assigneeEmail' => $newEmail, 'assigneeName' => $newName]);

        // Update ProxyAmendmentCancelled records
        ProxyAmendmentCancelled::where('assignorEmail', $email)
            ->update(['assignorEmail' => $newEmail, 'assignorName' => $newName]);

        ProxyAmendmentCancelled::where('assigneeEmail', $email)
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
            $uniqueEmailsAndNames[$email] = key($accounts);
        }
        return $uniqueEmailsAndNames;
    }



    /**
     * Get the query for online accounts with necessary relationships loaded.
     */
    public function getOnlineAccountsQuery(?int $userId = null, ?string $email = null): Builder
    {
        $query = User::with('stockholder.stockholderAccounts', 'stockholderAccount.stockholder', 'nonMemberAccount')
            ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
            ->whereNotNull('email');

        if ($userId) {
            $query->where('id', $userId);
        }

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

            $groupedAccounts[$account->email][$accountData['accountName']][$accountData['accountNo']][] = [
                'userId' => $account->id,
                'accountNo' => $accountData['accountNo'],
                'accountKey' => $accountData['accountKey'],
                'accountName' => $accountData['accountName'],
                'email' => $account->email,
                'emailRole' => $accountData['emailRole'],
                'role' => $account->role,
                'stockholder' => $account?->stockholder?->toArray(),
            ];
        }

        return $groupedAccounts;
    }

    /**
     * Reconcile stockholder and corporate representative accounts.
     * If a stockholder account exists for an email/account, remove corp-rep accounts
     * and add authorized signatory accounts.
     */
    private function reconcileStockholderAndCorpRepAccounts(array $groupedAccounts): array
    {
        foreach ($groupedAccounts as $email => $accountsByName) {
            foreach ($accountsByName as $accountName => $accountsByNumber) {
                foreach ($accountsByNumber as $accountNumber => $details) {
                    if ($this->hasStockholderRole($details)) {
                        $groupedAccounts[$email][$accountName][$accountNumber] = $this->processStockholderAccountGroup(
                            $details,
                            $email,
                            $accountName
                        );
                    }
                }
            }
        }

        return $groupedAccounts;
    }

    /**
     * Check if any detail in the group has a stockholder role.
     */
    private function hasStockholderRole(array $details): bool
    {
        return collect($details)->contains('role', 'stockholder');
    }

    /**
     * Process a group of accounts where a stockholder role exists.
     * Removes corp-rep accounts and expands stockholder authorized signatories.
     */
    private function processStockholderAccountGroup(array $details, string $email, string $accountName): array
    {
        $detailsToAdd = [];
        $filteredDetails = [];

        foreach ($details as $detail) {
            if ($detail['role'] === 'corp-rep') {
                // Skip corp-rep accounts
                continue;
            } elseif ($detail['role'] === 'stockholder' && !empty($detail['stockholder']['stockholder_accounts'])) {
                // Add authorized signatory accounts
                foreach ($detail['stockholder']['stockholder_accounts'] as $signatoryAccount) {
                    $detailsToAdd[] = [
                        'userId' => $signatoryAccount['userId'],
                        'accountNo' => $detail['stockholder']['accountNo'],
                        'accountKey' => $signatoryAccount['accountKey'],
                        'accountName' => $accountName,
                        'email' => $email,
                        'emailRole' => 'authorized signatory',
                        'role' => 'stockholder',
                    ];
                }
            } else {
                $filteredDetails[] = $detail;
            }
        }

        // Return merged filtered and new signatory accounts
        return array_merge($filteredDetails, $detailsToAdd);
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
            'emailRole' => 'authorized signatory',
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
            'stockholder' => $user->stockholder?->accountType === 'corp',
            'corp-rep' => $user->stockholderAccount?->stockholder?->accountType === 'corp',
            default => false,
        };
    }

    /**
     * Retrieve and reconcile stocks for a specific email address.
     */
    public function showStocks(string $email): array
    {
        if (!Auth::user()->cannot('view stocks for online account') && !Auth::user()->hasRole('superadmin')) {
            throw new Exception("Unauthorized to view stocks for online account with email: {$email}");
        }

        $onlineAccounts = $this->getOnlineAccountsQuery(email: $email)->get();
        $accountsByEmailAndName = $this->groupAccountsByEmailAndName($onlineAccounts);
        $reconciledAccounts = $this->reconcileStockholderAndCorpRepAccounts($accountsByEmailAndName);

        return $reconciledAccounts;
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
