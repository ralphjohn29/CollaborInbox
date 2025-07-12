<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Email extends Model
{
    protected $fillable = [
        'tenant_id',
        'workspace_id',
        'conversation_id',
        'email_account_id',
        'assigned_to',
        'disposition_id',
        'message_id',
        'from_email',
        'from_name',
        'to_email',
        'subject',
        'body_html',
        'body_text',
        'cc',
        'cc_email',
        'bcc',
        'bcc_email',
        'reply_to',
        'headers',
        'attachments',
        'spam_score',
        'direction',
        'status',
        'sent_by',
        'sent_at',
        'opened_at',
        'open_count',
        'postmark_data',
        'is_starred',
        'is_important',
        'has_attachments',
        'attachment_count',
        'thread_id',
        'in_reply_to',
        'references',
        'received_at',
        'read_at',
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'reply_to' => 'array',
        'headers' => 'array',
        'references' => 'array',
        'is_starred' => 'boolean',
        'is_important' => 'boolean',
        'has_attachments' => 'boolean',
        'received_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected $dates = [
        'received_at',
        'read_at',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function disposition(): BelongsTo
    {
        return $this->belongsTo(Disposition::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(EmailAttachment::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(EmailReply::class);
    }

    // Scopes
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', 'unread');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('status', 'read');
    }

    public function scopeStarred(Builder $query): Builder
    {
        return $query->where('is_starred', true);
    }

    public function scopeImportant(Builder $query): Builder
    {
        return $query->where('is_important', true);
    }

    public function scopeAssignedTo(Builder $query, $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeWithDisposition(Builder $query, $dispositionId): Builder
    {
        return $query->where('disposition_id', $dispositionId);
    }

    // Methods
    public function markAsRead()
    {
        if ($this->status === 'unread') {
            $this->update([
                'status' => 'read',
                'read_at' => now(),
            ]);
        }
    }

    public function markAsUnread()
    {
        $this->update([
            'status' => 'unread',
            'read_at' => null,
        ]);
    }

    public function toggleStar()
    {
        $this->update(['is_starred' => !$this->is_starred]);
    }

    public function assignTo($userId)
    {
        $this->update(['assigned_to' => $userId]);
    }

    public function setDisposition($dispositionId)
    {
        $this->update(['disposition_id' => $dispositionId]);
    }

    public function getPreviewText($length = 100)
    {
        $text = $this->body_text ?: strip_tags($this->body_html);
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
}
