<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject',
        'status',
        'external_id',
        'assigned_to_id',
        'last_activity_at',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the user this thread is assigned to.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Get all messages in this thread.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all notes in this thread.
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Scope a query to only include threads with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include unassigned threads.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to_id');
    }

    /**
     * Update the last activity timestamp.
     */
    public function updateLastActivity()
    {
        $this->update(['last_activity_at' => now()]);
    }
} 