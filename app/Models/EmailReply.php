<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailReply extends Model
{
    protected $fillable = [
        'email_id',
        'user_id',
        'to_email',
        'cc',
        'bcc',
        'subject',
        'body_html',
        'body_text',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'sent_at' => 'datetime',
    ];

    protected $dates = [
        'sent_at',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    public function markAsSending()
    {
        $this->update(['status' => 'sending']);
    }
}
