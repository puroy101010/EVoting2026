<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class OnlineAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $onlineAccounts = User::with('stockholder', 'stockholderAccount.stockholder')
            ->whereIn('role', ['stockholder', 'corp-rep', 'non-member'])
            ->whereNotNull('email')
            ->get();

        $accountByEmail = [];

        foreach ($onlineAccounts as $account) {
            $accountData = $this->getAccountData($account);
            $accountName = $accountData['accountName'];
            $accountNo = $accountData['accountNo'];

            $accountByEmail[$account->email][$accountName][] = array(
                'userId' => $account->id,
                'accountNo' => $accountNo,
                'accountType' => match ($account->role) {
                    'stockholder' => $account->stockholder->accountType,
                    'corp-rep' => $account->stockholderAccount->stockholder->accountType,
                    'non-member' => 'non-member',
                    default => null,
                },
            );
        }

        echo '<pre>';
        print_r($accountByEmail);
        echo '</pre>';
        return;
        return view('admin.online-accounts', compact('onlineAccounts', 'accountByEmail'));
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
            'accountName' => $accountName,
            'accountNo' => $stockholder->accountNo,
            'accountType' => $stockholder->accountType,
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
            'accountNo' => $account->stockholderAccount?->accountKey,
            'accountType' => $stockholder?->accountType,
        ];
    }

    private function getNonMemberAccountData(User $account): array
    {
        return [
            'accountName' => trim(($account->lastName ?? '') . ' ' . ($account->firstName ?? '')),
            'accountNo' => $account->nonMemberAccount?->nonmemberAccountNo,
            'accountType' => 'non-member',
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
