<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'thread_id',
        'user_id',
        'is_inbound',
        'external_id',
        'metadata',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_inbound' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the thread that the message belongs to.
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Get the user that created the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include inbound messages.
     */
    public function scopeInbound($query)
    {
        return $query->where('is_inbound', true);
    }

    /**
     * Scope a query to only include outbound messages.
     */
    public function scopeOutbound($query)
    {
        return $query->where('is_inbound', false);
    }
} 