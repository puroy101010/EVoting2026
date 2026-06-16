<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class ActivityLog extends Model
{

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'logId';



    protected $casts = [];


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



    public function candidate()
    {

        return $this->belongsTo(Candidate::class);
    }


    public function amendment()
    {

        return $this->belongsTo(amendment::class);
    }

    public function agenda()
    {

        return $this->belongsTo(Agenda::class);
    }

    public function ballot()
    {

        return $this->belongsTo(Ballot::class);
    }


    public function user()
    {

        return $this->belongsTo(User::class, 'userId', 'id');
    }
}
