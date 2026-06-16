<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedAccountAmendment extends Model
{
    protected $primaryKey = 'usedAmendmentAccountId';
    protected $table = 'used_amendment_accounts';


    public function stockholderAccount()
    {
        return $this->belongsTo(StockholderAccount::class, 'accountId', 'accountId');
    }
}
