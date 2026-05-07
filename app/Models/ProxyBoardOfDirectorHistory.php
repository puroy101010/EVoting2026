<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ProxyBoardOfDirectorHistory extends Model
{

    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'proxyBodHistoryId';
    protected $table = 'proxy_board_of_director_histories';

    protected $casts = [];

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

    public function auditor()
    {

        return $this->belongsTo(AdminAccount::class, 'auditedBy', 'adminId');
    }

    public function cancellationBy()
    {
        return $this->belongsTo(User::class, 'cancelledBy', 'id');
    }
}
