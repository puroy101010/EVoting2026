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





    public static function getHistory(Request $request, int $accountId, string $proxyType)
    {

        switch ($proxyType) {
            case 'BOD':
                return ProxyBoardOfDirectorHistory::with(['stockholderAccount.user', 'stockholderAccount.stockholder', 'cancellationBy'])->where('accountId', $accountId)->get();
            case 'Amendment':
                return ProxyAmendmentHistory::with(['stockholderAccount.user', 'stockholderAccount.stockholder', 'cancellationBy'])->where('accountId', $accountId)->get();
            default:
                return collect();
        }
    }



    public static function processHistory(Request $request, int $accountId)
    {

        $proxyType = $request->input('proxyType');

        if (!in_array($proxyType, ['BOD', 'Amendment'])) {
            throw new Exception("Invalid proxy type: $proxyType");
        }


        $histories = self::getHistory($request, $accountId, $proxyType);


        $historyArr = [];

        foreach ($histories as $history) {
            $historyArr[] = [
                'id' => $proxyType === 'BOD' ? $history->proxyBodId : $history->proxyAmendmentId,
                'formNo' => $proxyType === 'BOD' ? $history->proxyBodFormNo : $history->proxyAmendmentFormNo,
                'assignorName' => $history->assignorName,
                'assignorEmail' => $history->assignorEmail,
                'assignorType' => null,
                'assigneeName' => $history->assigneeName,
                'assigneeEmail' => $history->assigneeEmail,
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

    /**
     * Retrieve the assignor user for a given assignor ID and stockholder account.
     *
     * For individual accounts, returns the stockholder's direct user.
     * For corporate accounts, returns the assignor user with proper account validation.
     *
     * @param int $assignorUserId The user ID attempting to assign the proxy
     * @param StockholderAccount $stockholderAccount The target stockholder account for the proxy assignment
     * @return User The validated assignor user
     * @throws ValidationErrorException If account/stockholder not found or assignor invalid
     */
    public function resolveAssignorForAccount(int $assignorUserId, StockholderAccount $stockholderAccount): User
    {
        $stockholder = $stockholderAccount->stockholder;

        // For individual accounts, return the stockholder's direct user
        if ($stockholder->accountType === 'indv') {
            return $stockholder->user;
        }

        // For corporate accounts, validate assignor has rights to this account
        return $this->findValidatedAssignor($assignorUserId, $stockholder->accountNo);
    }

    /**
     * Load and validate stockholder account with its stockholder relationship.
     *
     * @param int $accountId The stockholder account ID
     * @return StockholderAccount The loaded account with stockholder
     * @throws ValidationErrorException If account or stockholder not found
     */
    private function loadStockholderAccount(int $accountId): StockholderAccount
    {
        $account = StockholderAccount::with('stockholder.user')->find($accountId);

        if (!$account || !$account->stockholder) {
            throw new ValidationErrorException(
                "The stock to assign was not found."
            );
        }

        return $account;
    }

    /**
     * Find and validate assignor user by account number.
     * Validates that the assignor has rights to assign from the specified account and has necessary credentials.
     * For corporate accounts, ensures the assignor is either the stockholder or a corporate representative with matching email.
     * @param int $assignorUserId The user ID of the assignor
     * @param string $accountNo The account number to validate against
     * @return User The validated assignor user
     * @throws ValidationErrorException If assignor is not valid for the account
     */
    private function findValidatedAssignor(int $assignorUserId, string $accountNo): User
    {
        $assignorUser = $this->findAssignorByAccountNumber($assignorUserId, $accountNo);

        if (!$assignorUser) {
            throw new ValidationErrorException(
                "The assignor does not have rights to assign shares from this account."
            );
        }

        $this->validateAssignorCredentials($assignorUser);

        return $assignorUser;
    }

    /**
     * Validate assignor has required credentials based on their role.
     *
     * @param User $assignorUser The assignor user to validate
     * @throws ValidationErrorException If email validation fails
     */
    private function validateAssignorCredentials(User $assignorUser): void
    {
        // Early return if email is present
        if ($assignorUser->email !== null) {
            return;
        }

        // Missing email for corporate stockholder
        if ($assignorUser->role === 'stockholder' && $assignorUser->stockholder->accountType === 'corp') {
            throw new ValidationErrorException(
                "You cannot assign a proxy to this account. The account does not have a registered authorized signatory."
            );
        }

        // Missing email for corporate representative
        if ($assignorUser->role === 'corp-rep') {
            throw new ValidationErrorException(
                "Proxy assignment is not allowed. This account does not have a registered corporate representative."
            );
        }
    }

    /**
     * Find assignor user by account number across stockholder and corporate representative roles.
     * @param int $assignorUserId The user ID to find
     * @param string $accountNo The account number to match
     * @return User|null The found assignor user or null if not found
     * @throws ValidationErrorException If assignor not found for the account
     */
    private function findAssignorByAccountNumber(int $assignorUserId, string $accountNo): ?User
    {
        return User::where('id', $assignorUserId)
            ->where(function ($query) use ($accountNo) {
                // Stockholder role: Direct account owner
                $query->where(function ($q) use ($accountNo) {
                    $q->where('role', 'stockholder')
                        ->whereHas(
                            'stockholder',
                            fn($sq) => $sq->where('accountNo', $accountNo)
                        );
                })
                    // Corporate representative role: Account manager
                    ->orWhere(function ($q) use ($accountNo) {
                        $q->where('role', 'corp-rep')
                            ->whereHas(
                                'stockholderAccount.stockholder',
                                fn($sq) => $sq->where('accountNo', $accountNo)
                            );
                    });
            })
            //->orderBy('role', 'desc') // [stockholder, corp-rep] - prioritize stockholder if user has multiple roles.
            ->orderByRaw("FIELD(role, 'stockholder', 'corp-rep')") //
            ->first();
    }

    /**
     * Validate assignee credentials and eligibility for proxy receipt.
     *
     * Performs comprehensive validation of proxy assignees including:
     * - User exists and has appropriate role
     * - All assignees have registered email addresses
     * - Non-members are active and authorized (role and GM status based on form type)
     *
     * @param array $request Request data containing 'assignee' key
     * @param string $formType The proxy form type ('amendment' or 'bod')
     * @return User The validated assignee user
     * @throws ValidationErrorException If any validation fails
     */
    public function validateAssignee(array $request, string $formType): User
    {
        $assigneeEmail = $request['assignee'];
        $assigneeUser = UserService::findUserByEmail($assigneeEmail);

        if (!$assigneeUser) {
            throw new ValidationErrorException(
                "The specified assignee does not exist, is ineligible to receive proxy assignments, or has no registered email address."
            );
        }



        // Non-members have additional role-specific constraints
        if ($assigneeUser->role === 'non-member') {
            $this->validateNonMemberAssignee($assigneeUser, $formType);
        }

        return $assigneeUser;
    }

    /**
     * Validate non-member assignee specific requirements.
     *
     * Non-members have multiple constraints before they can receive proxy assignments:
     * 1. Non-member account must exist in the system
     * 2. Account must be active (not soft-deleted/deactivated)
     * 3. For amendment proxies: Must have General Manager authorization if configured
     *
     * @param User $assignee The non-member user to validate
     * @param string $formType The proxy form type ('amendment' or 'bod')
     * @throws ValidationErrorException If any validation fails
     */
    private function validateNonMemberAssignee(User $assignee, string $formType): void
    {
        // Validate form type is supported
        $this->validateFormType($formType);

        // Load non-member account (including soft-deleted)
        $nonmemberAccount = $assignee->nonMemberAccount()->withTrashed()->first();

        if (!$nonmemberAccount) {
            throw new ValidationErrorException(
                "Non-member account not found. The assignee may have been deactivated."
            );
        }

        if ($nonmemberAccount->trashed()) {
            throw new ValidationErrorException(
                "The non-member account has been deactivated. " .
                    "Please reactivate the non-member account before assigning proxies to them."
            );
        }

        // Apply form-type specific validations
        $this->validateNonMemberForFormType($nonmemberAccount, $formType);
    }

    /**
     * Validate the form type is supported.
     *
     * @param string $formType The proxy form type to validate
     * @throws ValidationErrorException If form type is invalid
     */
    private function validateFormType(string $formType): void
    {
        if (!in_array($formType, ['amendment', 'bod'])) {
            throw new ValidationErrorException(
                "Invalid proxy form type '{$formType}'. Must be 'amendment' or 'bod'."
            );
        }
    }

    /**
     * Apply form-type specific validations for non-member assignee.
     *
     * Amendment proxies have stricter requirements (GM authorization) than BOD proxies.
     *
     * @param NonMemberAccount $nonmemberAccount The non-member account to validate
     * @param string $formType The proxy form type ('amendment' or 'bod')
     * @throws ValidationErrorException If validation fails
     */
    private function validateNonMemberForFormType(NonMemberAccount $nonmemberAccount, string $formType): void
    {
        // Amendment proxies require GM authorization if configured
        if ($formType === 'amendment' && $this->settings['amendment_restricted_to_gm'] === 1 && $nonmemberAccount->isGM !== 1) {
            throw new ValidationErrorException(
                "Amendment proxy assignment is restricted to the General Manager only. "
            );
        }
    }

    /**
     * Validate non-member has General Manager authorization.
     *
     * Some systems restrict amendment proxy assignment to General Managers only.
     * This enforces that restriction if configured.
     *
     * @param NonMemberAccount $nonmemberAccount The non-member account to validate
     * @throws ValidationErrorException If user is not a General Manager
     */
    private function validateNonMemberIsGM(NonMemberAccount $nonmemberAccount): void
    {
        if ($nonmemberAccount->isGM !== 1) {
            throw new ValidationErrorException(
                "Amendment proxy assignment is restricted to General Managers only. " .
                    "The specified non-member does not have General Manager authorization."
            );
        }
    }


    public function validateAssignor(array $request): User
    {
        // Validate and extract request data
        $assignorUserId = $request['assignor'] ?? null;
        $accountId = $request['accountToAssign'] ?? null;


        $stockholderAccount = $this->loadStockholderAccount($accountId);
        $assignorUser = $this->resolveAssignorForAccount($assignorUserId, $stockholderAccount);

        // Apply role-specific validations
        $this->applyRoleValidations($assignorUser, $stockholderAccount);

        return $assignorUser;
    }



    /**
     * Apply role-specific validation rules to assignor.
     *
     * @param User $assignorUser The assignor user to validate
     * @param StockholderAccount $stockholderAccount The target stockholder account
     * @throws ValidationErrorException If role-specific validation fails
     */
    private function applyRoleValidations(User $assignorUser, StockholderAccount $stockholderAccount): void
    {
        switch ($assignorUser->role) {
            case 'corp-rep':
                $this->validateEmailMatches($assignorUser, $stockholderAccount);
                break;
            case 'stockholder':
                // Stockholders can assign from their own account - no additional validation needed
                break;
            default:
                throw new ValidationErrorException(
                    "Invalid assignor role: {$assignorUser->role}. Only stockholders and corporate representatives can assign proxies."
                );
        }
    }




    /**
     * Validate corporate representative email matches account owner's email.
     *
     * This prevents corporate representatives from assigning shares from accounts
     * they don't own, adding an important security layer.
     *
     * @param User $corpRepUser The corporate representative user
     * @param StockholderAccount $stockholderAccount The target stockholder account
     * @throws ValidationErrorException If emails don't match
     */
    private function validateEmailMatches(User $corpRepUser, StockholderAccount $stockholderAccount): void
    {
        $accountOwnerEmail = $stockholderAccount->user->email;

        if (!$accountOwnerEmail || !AppHelper::compareStrings($corpRepUser->email, $accountOwnerEmail)) {
            throw new ValidationErrorException(
                "Stock assignment is not allowed. Corporate representatives can only assign stocks registered under their own name."
            );
        }
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

    /**
     * Retrieves all proxies (BOD and Amendment) where the email is used as assignee or assignor.
     */
    private function getProxiesUsingEmail(string $email): array
    {
        $emailUsage = [];

        $proxies = ProxyBoardOfDirector::where('assigneeEmail', $email)
            ->orWhere('assignorEmail', $email)
            ->select('assigneeEmail', 'assignorEmail')
            ->get()
            ->concat(
                ProxyAmendment::where('assigneeEmail', $email)
                    ->orWhere('assignorEmail', $email)
                    ->select('assigneeEmail', 'assignorEmail')
                    ->get()
            );

        foreach ($proxies as $proxy) {
            if ($proxy->assigneeEmail) {
                $emailUsage[$proxy->assigneeEmail] ??= $proxy;
                $emailUsage[$proxy->assignorEmail] ??= $proxy;
            }
        }

        return $emailUsage;
    }


    /**
     * Validate that proxy email usage won't conflict with changes.
     * 
     * @param string|null $oldEmail Email to check for proxy usage
     * @throws ValidationErrorException If email is in use by proxies
     */
    public static function validateProxyEmailUsage(?string $oldEmail): void
    {
        if (empty($oldEmail)) {
            return; // No email means no proxy usage, so we can skip validation
        }
        $proxyService = new ProxyService();
        $proxiesUsingEmail = $proxyService->getProxiesUsingEmail($oldEmail);

        if (!empty($proxiesUsingEmail)) {
            throw new ValidationErrorException(
                "The authorized signatory's current email address is associated with one or more active proxy assignments. Please cancel those assignments before updating the authorized signatory."
            );
        }
    }
}
