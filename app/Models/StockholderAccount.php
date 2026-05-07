<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Stockholder;

class StockholderAccount extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'accountId';

    protected $guarded = [];

    protected function performDeleteOnModel()
    {
        $this->deletedAt = $this->freshTimestamp(); // Set the deleted_at column
        $this->deletedBy = Auth::user()->id; // Set the value of deletedBy column using the currently authenticated user
        $this->save();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updatedBy');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deletedBy');
    }

    public function restoredBy()
    {
        return $this->belongsTo(User::class, 'restoredBy');
    }


    public function stockholder()
    {
        return $this->belongsTo(Stockholder::class, 'stockholderId', 'stockholderId');
    }

    public function user()
    {

        return $this->belongsTo(User::class, 'userId', 'id');
    }

    public function proxyBoard()
    {

        return $this->hasOne(ProxyBoardOfDirector::class, 'accountId', 'accountId');
    }

    public function proxyAmendment()
    {

        return $this->hasOne(ProxyAmendment::class, 'accountId', 'accountId');
    }

    public function usedBodAccount()
    {
        return $this->hasMany(UsedBoardOfDirectorAccount::class, 'accountId', 'accountId');
    }

    public function usedAmendmentAccount()
    {
        return $this->hasMany(UsedAmendmentAccount::class, 'accountId', 'accountId');
    }



    public function usedBod()
    {
        return $this->hasOne(UsedBoardOfDirectorAccount::class, 'accountId', 'accountId');
    }

    public function usedAmendment()
    {
        return $this->hasOne(UsedAmendmentAccount::class, 'accountId', 'accountId');
    }
}
