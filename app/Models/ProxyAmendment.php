<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ProxyAmendment extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'proxyAmendmentId';
    protected $table = 'proxy_amendments';

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


    public function stockholderAccount()
    {
        return $this->belongsTo(StockholderAccount::class, 'accountId', 'accountId');
    }

    public function assignee()
    {

        return $this->belongsTo(User::class, 'assigneeId', 'id');
    }

    public function assignor()
    {

        return $this->belongsTo(User::class, 'assignorId', 'id');
    }
    public function getProxyAssignorNameAttribute()
    {

        return $this->assignor->role === 'stockholder' ? $this->assignor->stockholder->stockholder : $this->assignor->stockholderAccount->corpRep;
    }

    public function getProxyAssigneeNameAttribute()
    {

        switch ($this->assignee->role) {
            case 'stockholder':
                return $this->assignee->stockholder->stockholder;
            case 'corp-rep':
                return $this->assignee->stockholderAccount->corpRep;
            case 'non-member':
                return $this->assignee->nonMemberAccount->firstName . ' ' . $this->assignee->nonMemberAccount->lastName;
            default:
                throw new \Exception("Unknown role: {$this->assignee->role}");
        }

        return $this->assignee->role === 'stockholder' ? $this->assignee->stockholder->stockholder : $this->assignee->stockholderAccount->corpRep;
    }

    public function auditor()
    {

        return $this->belongsTo(User::class, 'auditedBy', 'id');
    }

    public function usedAccount()
    {

        return $this->hasOne(UsedAmendmentAccount::class, 'accountId', 'accountId');
    }

    public function cancelledProxyAmendment()
    {
        return $this->hasMany(ProxyAmendmentCancelled::class, 'accountId', 'accountId');
    }
}
