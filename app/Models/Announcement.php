<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Relationship with user who created the announcement
    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    // Scope for active announcements
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for high priority announcements
    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    // Scope for urgent announcements
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }
}
