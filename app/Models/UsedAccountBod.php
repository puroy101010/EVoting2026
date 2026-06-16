<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedAccountBod extends Model
{
    protected $primaryKey = 'usedBodAccountId';
    protected $table = 'used_bod_accounts';

    public function stockholderAccount()
    {
        return $this->belongsTo(StockholderAccount::class, 'accountId', 'accountId');
    }
}
