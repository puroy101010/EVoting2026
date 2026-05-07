<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Ballot extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'ballotId';

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


    public function bodDetails()
    {
        return $this->hasMany(BallotDetail::class, 'ballotId', 'ballotId');
    }

    public function amendmentDetails()
    {
        return $this->hasMany(BallotAmendment::class, 'ballotId', 'ballotId');
    }

    public function agendaDetails()
    {
        return $this->hasMany(BallotAgenda::class, 'ballotId', 'ballotId');
    }

    public function usedAccountBod()
    {

        return $this->hasMany(UsedAccountBod::class, 'ballotId', 'ballotId');
    }

    public function usedAccountAmendment()
    {

        return $this->hasMany(UsedAccountAmendment::class, 'ballotId', 'ballotId');
    }

    public function ballotConfirmation()
    {
        return $this->hasOne(BallotConfirmation::class, 'confirmationId', 'confirmationId');
    }
}
