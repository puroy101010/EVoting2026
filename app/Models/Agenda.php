<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Agenda extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'agendaId';

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

    public function ballotAgendaDetails()
    {
        return $this->hasMany(BallotAgenda::class, 'agendaId', 'agendaId');
    }
}
