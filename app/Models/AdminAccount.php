<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class AdminAccount extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $primaryKey = 'adminId';

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

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    /**
     * Get the admin's full name as an attribute.
     *
     * @return string
     */
    public function getFullnameAttribute()
    {
        $names = array_filter([
            $this->firstName,
            $this->middleName,
            $this->lastName
        ]);
        return implode(' ', $names);
    }
}
