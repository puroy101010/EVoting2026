<?php

namespace App\Services;

use App\Exceptions\ValidationErrorException;
use App\Exceptions\ImportStockholderException;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\EApp;

use App\Models\StockholderAccount;
use App\Models\User;
use App\Models\Stockholder;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;



class StockholderImportService
{


    protected $groupByCombinedEmail = [];
    protected $validationErrors = [];

    public function import()
    {
        try {
            $filePath = storage_path('app/Stockholders 2026.xlsx');
            $timestamp = now();

            // Step 1: Load and validate raw data
            $rawData = $this->loadExcelData($filePath);

            // Step 2: Group and enrich records with consistency validation
            $enrichedRecords = $this->validateAndEnrichRecords($rawData);

            // Step 2a: Validate email uniqueness across all records
            $this->validateEmailConsistency($enrichedRecords);

            if (!empty($this->validationErrors)) {


                echo '<pre>';
                print_r($this->validationErrors);
                echo '</pre>';

                return;;
            }

            // Step 3: Build database insert arrays
            $insertPayload = $this->buildInsertArrays($enrichedRecords, $timestamp);

            // Step 4: Execute database transaction
            $this->executeImport($insertPayload);

            Log::info('Stockholder import completed successfully', ['recordCount' => count($rawData)]);
        } catch (ValidationErrorException $e) {
            Log::warning('Validation error occurred during stockholder import', [
                'error' => $e->getMessage(),
            ]);
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (Exception $e) {
            Log::error('An unexpected error occurred during stockholder import', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function loadExcelData(string $filePath): array
    {
        $excelArray = Excel::toArray([], $filePath);
        $data = array_slice($excelArray[0], 1); // Skip header row

        if (empty($data)) {
            throw new ValidationErrorException('Import file does not contain any data rows.');
        }

        return $data;
    }

    /**
     * Group raw records by account number while validating each record. Each record is enriched with role and combined email information. Validation includes checking for required fields, field formats, and consistency across records with the same account number. Any validation errors are collected and thrown as a single exception at the end of the process.
     * @param array $rawData Raw records from Excel file
     * @return array Grouped and enriched records indexed by account number
     * @throws ValidationErrorException If any validation errors are found during processing, with detailed error messages for each issue
     */
    private function validateAndEnrichRecords(array $rawData): array
    {
        $groupedByAccountNo = $this->groupByAccountNo($rawData);
        $enrichedRecords = [];

        // If grouping resulted in validation errors, return early
        if (!empty($this->validationErrors)) {
            return [];
        }

        foreach ($groupedByAccountNo as $accountNo => $accountRecords) {
            // Validate field consistency across all corp-reps under same account
            $fieldErrors = $this->validateAccountFieldConsistency($accountNo, $accountRecords);
            $this->validationErrors = array_merge($this->validationErrors, $fieldErrors);

            if (!empty($fieldErrors)) {
                continue;
            }

            // Validate suffix uniqueness
            $suffixErrors = $this->validateUniqueSuffixes($accountNo, $accountRecords);
            $this->validationErrors = array_merge($this->validationErrors, $suffixErrors);

            if (!empty($suffixErrors)) {
                continue;
            }

            // Create stockholder record (main account)
            $stockholderRecord = $this->createStockholderRecord($accountRecords);
            $enrichedRecords[$accountNo][] = $stockholderRecord;

            // Create corp-rep records (related accounts)
            foreach ($accountRecords as $corpRepData) {
                $corpRepRecord = $this->createCorpRepRecord($corpRepData);
                $enrichedRecords[$accountNo][] = $corpRepRecord;
            }
        }

        return $enrichedRecords;
    }

    /**
     * Validate that each unique email address is associated with only one authorized signatory across all records.
     * This prevents conflicts where a single email could be linked to multiple different users, which would cause
     * authentication and account management issues.
     * 
     * @param array $enrichedRecords All enriched records grouped by account number
     * @return void Validation errors are stored in $this->validationErrors
     */
    private function validateEmailConsistency(array $enrichedRecords): void
    {
        $emailToSignatoryMap = [];

        foreach ($enrichedRecords as $accountNo => $records) {
            foreach ($records as $record) {
                $email = $record['combinedEmail'];
                $signatory = $record['combinedAuthorizedSignatory'];

                if (!$email) {
                    continue;
                }

                if (isset($emailToSignatoryMap[$email])) {
                    // Email already seen - verify it's associated with the same signatory
                    $existingSignatories = array_column($emailToSignatoryMap[$email], 'signatory');
                    if (!in_array($signatory, $existingSignatories, true)) {

                        // List the account numberr and signatoryies associated with the conflicting email for easier debugging
                        $this->validationErrors[] = "$email is associated with multiple different authorized signatories. "
                            . implode(', ', array_map(function ($entry) {
                                return "[Account No: {$entry['acccountNo']}, Signatory: {$entry['signatory']}]";
                            }, $emailToSignatoryMap[$email])) . ". "
                            . "Current record - Account No: $accountNo, Signatory: $signatory.";
                    }
                } else {
                    // First time seeing this email - record its signatory
                    $emailToSignatoryMap[$email][] = [
                        'signatory' => $signatory,
                        'acccountNo' => $accountNo
                    ];
                }
            }
        }
    }

    /**
     * Validate that all records with the same account number have consistent values for key fields. This ensures data integrity for accounts with multiple stocks
     * 
     * @param string $accountNo Account number being validated
     * @param array $accountRecords All records associated with the account number
     * @return array List of validation error messages, empty if no errors found
     * 
     */
    private function validateAccountFieldConsistency(string $accountNo, array $accountRecords): array
    {
        $errors = [];
        $expectedValues = [
            'stockholder' => null,
            'accountType' => null,
            'voteInPerson' => null,
            'authSignatory' => null,
            'email' => null,
        ];

        foreach ($accountRecords as $record) {
            foreach ($expectedValues as $field => &$expectedValue) {
                $normalizedValue = AppHelper::normalizeString($record[$field]);

                if ($expectedValue === null) {
                    $expectedValue = $normalizedValue;
                } elseif ($normalizedValue !== $expectedValue) {
                    $errors[] = "Each account number must have a unique $field. Account Number: $accountNo. Expected: $expectedValue";
                }
            }
        }

        return $errors;
    }

    private function validateUniqueSuffixes(string $accountNo, array $accountRecords): array
    {
        $errors = [];
        $suffixes = [];

        foreach ($accountRecords as $record) {
            $suffix = (int) $record['suffix'];

            if (in_array($suffix, $suffixes, true)) {
                $errors[] = "The account number $accountNo has a duplicate suffix.";
            }

            $suffixes[] = $suffix;
        }

        return $errors;
    }

    private function createStockholderRecord(array $accountRecords): array
    {
        $firstRecord = $accountRecords[0];

        return array_merge($firstRecord, [
            'role' => 'stockholder',
            'stockholder' => AppHelper::normalizeString($firstRecord['stockholder']),
            'accountType' => $firstRecord['accountType'],
            'voteInPerson' => $firstRecord['voteInPerson'],
            'authSignatory' => $firstRecord['accountType'] === 'corp'
                ? AppHelper::normalizeString($firstRecord['authSignatory'])
                : null,
            'email' => AppHelper::normalizeString($firstRecord['email']),
            'combinedEmail' => AppHelper::normalizeString($firstRecord['email']),
            'combinedAuthorizedSignatory' => $firstRecord['accountType'] === 'corp'
                ? AppHelper::normalizeString($firstRecord['authSignatory'])
                : AppHelper::normalizeString($firstRecord['stockholder']),
        ]);
    }

    /**
     * Create a corp-rep record based on the provided data. This includes normalizing the corp-rep name and email, and setting the role to 'corp-rep'. 
     * The combined email and authorized signatory fields are used for validation purposes but will not be stored in the database for corp-rep records.
     * @param array $record Raw record data for the corp-rep
     * @return array Enriched corp-rep record with normalized fields and role information
     *
     */
    private function createCorpRepRecord(array $record): array
    {
        return array_merge($record, [
            'role' => 'corp-rep',
            'combinedEmail' => $record['accountType'] === 'corp' ? AppHelper::normalizeString($record['corpRepEmail']) : null,
            'combinedAuthorizedSignatory' => $record['accountType'] === 'corp' ? AppHelper::normalizeString($record['corpRep']) : null,
        ]);
    }

    /**
     * Build arrays for bulk insertion into the database. This method takes the enriched records and constructs separate arrays for users, stockholders, and stockholder accounts. It also ensures that user IDs are generated sequentially and that stockholder records are created before their associated corp-rep records to maintain referential integrity.
     * @param array $enrichedRecords All enriched records grouped by account number
     * @param DateTime $timestamp Timestamp to use for createdAt fields
     * @return array Arrays for users, stockholders, and accounts ready for bulk insertion
     * @throws Exception If a corp-rep record is encountered without an associated stockholder record, indicating a logic error in the processing of records 
     */
    private function buildInsertArrays(array $enrichedRecords,  DateTime $timestamp): array
    {

        // Sort records by account number to ensure a consistent and predictable processing order.
        // This is vital for maintaining data integrity and ensuring that the main stockholder 
        // record is created before its related corporate representative accounts.
        ksort($enrichedRecords);
        $userBatches = [];
        $stockholderBatches = [];
        $accountBatches = [];
        $lastUserId = User::max('id') ?? 0;

        $loggedInUserId = Auth::id();

        foreach ($enrichedRecords as $accountNo => $records) {
            $lastStockholderId = null;

            foreach ($records as $record) {
                $lastUserId++;

                // Build user record
                $userBatches[] = [
                    'id' => $lastUserId,
                    'email' => $record['combinedEmail'],
                    'role' => $record['role'],
                    'createdBy' => $loggedInUserId,
                    'createdAt' => $timestamp
                ];

                if ($record['role'] === 'stockholder') {
                    $lastStockholderId = $lastUserId;

                    // Build stockholder record
                    $stockholderBatches[] = [
                        'stockholderId' => $lastStockholderId,
                        'accountNo' => $accountNo,
                        'stockholder' => AppHelper::normalizeString($record['stockholder']),
                        'authorizedSignatory' => AppHelper::normalizeString($record['authSignatory']),
                        'accountType' => $record['accountType'],
                        'voteInPerson' => $record['voteInPerson'],
                        'userId' => $lastUserId,
                        'createdBy' => $loggedInUserId,
                        'createdAt' => $timestamp
                    ];
                } elseif ($record['role'] === 'corp-rep') {

                    if ($lastStockholderId === null) {
                        // This should never happen due to the way records are grouped and processed, but we check just in case
                        // Stockholder record must be created before any corp-rep records for the same account number, so if we encounter a corp-rep without a stockholder, it indicates a logic error in our processing
                        throw new Exception("Data integrity error: Corp-rep record found without an associated stockholder. Account Number: $accountNo");
                    }
                    // Build stockholder account (corp-rep) record
                    $accountBatches[] = [
                        'accountKey' => $accountNo . '-' . $record['suffix'],
                        'corpRep' =>  AppHelper::normalizeString($record['corpRep']),
                        'suffix' => $record['suffix'],
                        'userId' => $lastUserId,
                        'stockholderId' => $lastStockholderId,
                        'createdBy' => $loggedInUserId,
                        'createdAt' => $timestamp
                    ];
                }
            }
        }

        return [
            'users' => $userBatches,
            'stockholders' => $stockholderBatches,
            'accounts' => $accountBatches,
        ];
    }

    private function executeImport(array $insertPayload): void
    {
        DB::beginTransaction();

        try {
            User::insert($insertPayload['users']);

            Stockholder::insert($insertPayload['stockholders']);

            StockholderAccount::insert($insertPayload['accounts']);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Group raw data by account number and validate each record while grouping. Each record is enriched with an account key and role information.
     * 
     * @param array $dataFromExcel Raw data from Excel file
     * @return array Grouped and enriched records indexed by account number
     * @throws ValidationErrorException If any record fails validation during grouping
     * 
     */
    private function groupByAccountNo(array $dataFromExcel): array
    {
        $groupByAccountNo = [];
        $rowIndex = 1;

        foreach ($dataFromExcel as $member) {
            $rowIndex++;
            $validatedRecord = $this->validateImportRecordByRow($member, $rowIndex);

            // Skip records that failed validation (returned empty array)
            if (empty($validatedRecord)) {
                continue;
            }

            $accountNo = $validatedRecord['accountNo'];

            $groupByAccountNo[$accountNo][] = array_merge(
                $validatedRecord,
                [
                    'accountKey' => $accountNo . '-' . (int) $validatedRecord['suffix'],
                    'role' => 'corp-rep'
                ]
            );
        }

        return $groupByAccountNo;
    }



    /**
     * Validate a single record from the Excel file based on its row index. This includes checking required fields, conditional requirements, and field formats. Custom error messages are generated for any validation failures, which include the account number and row index for easier debugging.
     * 
     * @param array $member Raw record data from Excel
     * @param int $rowIndex The index of the row in the Excel file (used for error messages)
     * @return array Validated and normalized record data
     * @throws ValidationErrorException If validation fails for the record, with detailed error messages
     */
    private function validateImportRecordByRow(array $member, int $rowIndex): array
    {
        $formData = array_combine(
            ['stockholder', 'accountNo', 'suffix', 'accountType', 'voteInPerson', 'authSignatory', 'email', 'corpRep', 'corpRepEmail', 'isDelinquent'],
            $member
        );

        $validationRules = [
            'stockholder'   => 'required|string|max:100',
            'accountNo'     => 'required|string|between:4,4',
            'suffix'        => 'required|integer|min:1|max:100',
            'accountType'   => 'required|in:indv,corp',
            'email'         => [
                'nullable',
                'email',
                // Email is required for individual accounts and corporate accounts with authorized signatories, 
                // as these indicate the account will be used for login
                Rule::requiredIf(function () use ($formData) {
                    return AppHelper::compareStrings($formData['accountType'], 'indv')
                        || (AppHelper::compareStrings($formData['accountType'], 'corp') && !empty($formData['authSignatory']));
                })
            ],
            'voteInPerson'  => 'required|in:stockholder,corp-rep',
            'authSignatory' => [
                // Authorized signatory is required only if account type is corporate and email is provided, as this indicates the account will be used for login and needs an associated signatory for authentication purposes
                Rule::requiredIf(function () use ($formData) {
                    return $formData['accountType'] === 'corp' && !empty($formData['email']);
                })
            ],
            'corpRep'       => 'nullable|required_with:corpRepEmail|string|max:100',
            'corpRepEmail'  => 'nullable|required_with:corpRep|email',
            'isDelinquent'  => 'required|in:no,yes',
        ];

        $customMessages = [
            'email.email' => "Invalid email address format. Account Number: {$formData['accountNo']} Row: $rowIndex",
            'email.required_if' => "Email is required for this account type. Account Number: {$formData['accountNo']} Row: $rowIndex",
        ];

        $validator = Validator::make($formData, $validationRules, $customMessages);
        $validatedData = $validator->validated();


        if ($validator->fails()) {
            $this->validationErrors[] = $validator->errors()->first() . " Row: $rowIndex";
            return [];
        }

        // Additional validation for individual accounts
        if ($validatedData['accountType'] === 'indv') {
            $normalizedCorpRep = AppHelper::normalizeString($validatedData['corpRep']);
            $normalizedCorpRepEmail = AppHelper::normalizeEmail($validatedData['corpRepEmail']);

            if ($normalizedCorpRep !== null || $normalizedCorpRepEmail !== null) {
                $this->validationErrors[] = 'Corporate representative details are only for corporate accounts. '
                    . 'Account Number: ' . $validatedData['accountNo'] . ' Row: ' . $rowIndex;
                return [];
            }

            if ($validatedData['voteInPerson'] === 'corp-rep') {
                $this->validationErrors[] = 'Vote in person must be set to "stockholder" for individual accounts. '
                    . 'Account Number: ' . $validatedData['accountNo'] . ' Row: ' . $rowIndex;
                return [];
            }

            if (!empty(AppHelper::normalizeString($validatedData['authSignatory']))) {
                $this->validationErrors[] = 'Authorized signatory should not be provided for individual accounts. '
                    . 'Account Number: ' . $validatedData['accountNo'] . ' Row: ' . $rowIndex;
                return [];
            }
        }

        return $validatedData;
    }
}
