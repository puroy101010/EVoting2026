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
use Illuminate\Support\Facades\Session;

class UserService
{


    /**
     * Find user by email with role-based priority ordering.
     *
     * When multiple users exist with the same email (edge case), prioritizes by role:
     * 1. Stockholder (primary account owner)
     * 2. Corporate Representative (delegated authority)
     * 3. Non-member (limited permissions)
     *
     * @param string $email The email address to search for
     * @return User|null The user if found, null otherwise
     */
    public static function findUserByEmail(string $email): ?User
    {
        if (!$email) {
            return null;
        }
        return User::where('email', $email)
            ->with('stockholder', 'stockholderAccount.stockholder', 'nonMemberAccount')
            ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
            ->orderByRaw("FIELD(role, 'stockholder', 'corp-rep', 'non-member')") // Prioritize by role
            ->first();
    }



    /**
     * Validate that new email doesn't conflict with existing user accounts.
     * Throws exception if email belongs to another account with different signatory.
     * 
     * @param int $userIds ID of the user being updated
     * @param string|null $newEmail Email to validate
     * @param string|null $newSignatory Signatory name for the new email
     * @throws ValidationErrorException If email conflict found
     */
    public static function validateEmailConflicts(array|int $userIds, ?string $newEmail, ?string $newSignatory): void
    {
        if ($newEmail === null) {
            return;
        }


        $emailConflictUsers = User::with('stockholder', 'stockholderAccount')
            ->where('email', $newEmail)
            ->when(is_array($userIds), function ($query) use ($userIds) {
                $query->whereNotIn('id', $userIds);
            })
            ->when(is_int($userIds), function ($query) use ($userIds) {
                $query->where('id', '!=', $userIds);
            })
            ->get();


        if ($emailConflictUsers->count() === 0) {
            return;
        }


        foreach ($emailConflictUsers as $conflictingUser) {
            // $conflictingUserSignatory = UserService::getAuthorizedSignatory($conflictingUser);
            $conflictingUserSignatory = $conflictingUser->authorized_signatory;

            if (!self::compareSignatories($conflictingUserSignatory, $newSignatory)) {

                throw new ValidationErrorException(
                    "This email address is already in use by $conflictingUserSignatory. "
                );
            }
        }
    }


    /**
     * Get the authorized signatory name for a user based on their role.
     * 
     * @param User $user User to get signatory for
     * @return string|null Signatory name or null if not applicable
     * @throws ValidationErrorException If user role is unsupported
     */
    public static function getAuthorizedSignatory(User $user): ?string
    {
        //migrated to User model as accessor: $user->authorized_signatory
        // return match ($user->role) {
        //     'stockholder' => $user->stockholder->accountType === 'corp'
        //         ? $user->stockholder->authorizedSignatory
        //         : $user->stockholder->stockholder,
        //     'corp-rep' => $user->stockholderAccount->stockholder->accountType === 'corp'
        //         ? $user->stockholderAccount->corpRep
        //         : $user->stockholderAccount->stockholder->stockholder,
        //     'non-member' => $user->nonMemberAccount->fullName,
        //     default => throw new ValidationErrorException("Authorized signatory information is required to validate email changes for this account.")
        // };
    }



    /**
     * Compare two signatories for equality, ignoring case.
     * 
     * @param string|null $signatory1 First signatory
     * @param string|null $signatory2 Second signatory
     * @return bool True if signatories are equal, false otherwise
     */
    private static function compareSignatories(?string $signatory1, ?string $signatory2): bool
    {
        if ($signatory1 === null || $signatory2 === null) {
            return false;
        }


        return AppHelper::compareStrings($signatory1, $signatory2);
    }

    public static function logoutUpdatedUser(string $id): void
    {

        // Invalidate the session for the user
        Session::getHandler()->destroy($id);

        User::where('id', $id)->update([
            'password' => null,
            'otp' => null,
            'otpValid' => false
        ]);
    }
}
