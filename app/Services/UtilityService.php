<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Http\Controllers\ActivityController;
use App\Models\NonMemberAccount;
use App\Models\Stockholder;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UtilityService
{
    /**
     * Return the first validation error message and field from a validator instance.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return array [message, field, errors]
     */
    public static function firstValidationError($validator, $json = true)
    {
        $errors = $validator->errors()->toArray();
        $firstField = array_key_first($errors);
        $firstMessage = $errors[$firstField][0] ?? 'The given data was invalid.';


        Log::error('Validation failed', [
            'message' => $firstMessage,
            'field' => $firstField,
            'errors' => $errors
        ]);
        if ($json) {
            return response()->json([
                'message' => $firstMessage,
                'field' => $firstField,
                'errors' => $errors,
            ], 422);
        }

        return [
            'message' => $firstMessage,
            'field' => $firstField,
            'errors' => $errors,
        ];
    }

    /**
     * Generate the next ID for a table/field, with optional padding and custom start value.
     *
     * @param string $table The table name
     * @param string $field The field/column name
     * @param int $length The length to pad the ID to (default: 0, no padding)
     * @param string $pad The padding character (default: '0')
     * @param int $start The starting value if no record exists (default: 1)
     * @return string The next ID
     * @throws Exception
     */
    public static function generateId($table = "", $field = "", $length = 0, $pad = '0', $start = 1)
    {

        try {


            $id = DB::table($table)->selectRaw('MAX(CAST(trim(LEADING "0" FROM ' . $field . ') AS UNSIGNED)) AS lastId')->first();


            $id = $id->lastId === null ? 1 : (int)$id->lastId + 1;


            $id = str_pad($id, $length, $pad, STR_PAD_LEFT);

            return $id;
        } catch (Exception $e) {

            throw new Exception('Error generating ID');
        }
    }

    public static function isAccountNumberTaken($accountNumber)
    {


        $existsInStockholder = Stockholder::withTrashed()
            ->where('accountNo', $accountNumber)
            ->exists();

        $existsInNonMember = NonMemberAccount::withTrashed()
            ->where('nonmemberAccountNo', $accountNumber)
            ->exists();

        return $existsInStockholder || $existsInNonMember;
    }

    /**
     * Handles status changes for a soft-deletable model (activate/deactivate).
     * Returns an array describing the change, or an empty array if no change.
     *
     * @param Model $model
     * @param int $newStatus
     * @param mixed $id
     * @return array
     */
    public static function handleStatusChange(Model $model, int $newStatus, $id): array
    {
        $newStatus = (int)$newStatus;
        $currentStatus = $model->isActive ? 1 : 0;
        $statusChange = [];

        if ($currentStatus === $newStatus) {
            Log::info('No status change needed.', [
                'model' => get_class($model),
                'id' => $id,
                'status' => $currentStatus ? 'active' : 'inactive'
            ]);
            return [];
        }

        $statusChange['status'] = [
            'old' => $currentStatus === 1 ? 'active' : 'inactive',
            'new' => $newStatus === 1 ? 'active' : 'inactive'
        ];

        $model->isActive = $newStatus;




        return $statusChange;
    }

    public static function logServerError($request, $e, $message = 'Server error occurred', $errorArray = [])
    {

        DB::rollBack();
        Log::error(
            $message,
            array_merge([
                "error" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "exception" => $e instanceof ValidationErrorException
                    ? $e->getMessage() : $e,
            ], $errorArray)
        );

        Log::error("exception_class: " . get_class($e));
    }

    public static function validateProxyType(string $proxyType)
    {
        if (!in_array($proxyType, ['bod', 'amendment'])) {
            Log::error("Invalid proxy type provided to getAccountIds method.", ['proxyType' => $proxyType]);
            throw new Exception("Invalid proxy type provided: {$proxyType}");
        }

        if (strtolower($proxyType) === 'bod') {
            return 'usedBodAccount';
        }
        return 'usedAmendmentAccount';
    }

    public static function validateBallotType(string $ballotType)
    {
        if (!in_array($ballotType, ['person', 'proxy'])) {
            Log::error("Invalid ballot type provided.", ['ballotType' => $ballotType]);
            throw new Exception("Invalid ballot type provided: {$ballotType}");
        }

        return $ballotType;
    }

    public static function validateVotingType(string $votingType): string
    {
        if (!in_array($votingType, ['Stockholder Online Voting', 'Proxy Voting'])) {
            Log::error("Invalid voting type provided.", ['proxyType' => $votingType]);
            throw new Exception("Invalid voting type provided: {$votingType}");
        }

        if (strtolower($votingType) === 'stockholder online voting') {
            return 'Stockholder Online Voting';
        }
        return 'Proxy Voting';
    }

    public static function getDisplayUserRole(User $user): string
    {
        switch ($user->role) {

            case 'stockholder':
                $accountRole = 'Stockholder';

                break;

            case 'corp-rep':
                $accountRole = 'Corporate Representative';

                break;

            case 'non-member':
                $accountRole = 'Non Member';

                break;

            default:
                $accountRole = 'Default';

                break;
        }

        return $accountRole;
    }

    public static function getVotingType($ballotType)
    {
        if (!in_array($ballotType, ['person', 'proxy'])) {
            Log::error("Invalid ballot type provided to getVotingType method.", ['ballotType' => $ballotType]);
            throw new Exception("Invalid ballot type provided: {$ballotType}");
        }

        return $ballotType === 'person' ? 'Stockholder Online Voting' : 'Proxy Voting';
    }
}
