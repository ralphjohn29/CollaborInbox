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
        'bcc',
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

    /**
     * Generate avatar initials from sender name or email
     */
    public function getAvatarInitials()
    {
        $name = $this->from_name ?: $this->from_email;
        
        // Handle empty name
        if (empty($name)) {
            return '?';
        }
        
        if (str_contains($name, '@')) {
            // If it's an email, use the part before @
            $emailParts = explode('@', $name);
            $name = $emailParts[0] ?? $name;
        }
        
        // Clean up the name - remove special characters but keep spaces
        $name = preg_replace('/[^a-zA-Z\s]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name); // Replace multiple spaces with single space
        $name = trim($name);
        
        // Handle empty name after cleaning
        if (empty($name)) {
            return '?';
        }
        
        // Split by spaces
        $parts = explode(' ', $name);
        $parts = array_filter($parts); // Remove empty parts
        $parts = array_values($parts); // Reindex array
        
        if (count($parts) >= 2) {
            // Take first letter of first and second parts (like Ralph John = RJ)
            $first = isset($parts[0]) && strlen($parts[0]) > 0 ? substr($parts[0], 0, 1) : '';
            $second = isset($parts[1]) && strlen($parts[1]) > 0 ? substr($parts[1], 0, 1) : '';
            
            // If we have both first and second name, use them
            if (!empty($first) && !empty($second)) {
                return strtoupper($first . $second);
            }
            
            // If only first name, try to get first and last part
            $last = isset($parts[count($parts) - 1]) && strlen($parts[count($parts) - 1]) > 0 ? substr($parts[count($parts) - 1], 0, 1) : '';
            if (!empty($first) && !empty($last) && $first !== $last) {
                return strtoupper($first . $last);
            }
        }
        
        // Single name - take first two letters
        if (count($parts) === 1 && isset($parts[0]) && strlen($parts[0]) >= 2) {
            return strtoupper(substr($parts[0], 0, 2));
        }
        
        // Single name - take first letter
        if (count($parts) === 1 && isset($parts[0]) && strlen($parts[0]) >= 1) {
            return strtoupper(substr($parts[0], 0, 1));
        }
        
        // Final fallback
        return strtoupper(substr($name, 0, 1)) ?: '?';
    }

    /**
     * Generate a subtle, professional color for the avatar based on sender
     */
    public function getAvatarColor()
    {
        // More subtle, professional color palette
        $colors = [
            '#6366f1', // Indigo
            '#8b5cf6', // Violet  
            '#06b6d4', // Cyan
            '#10b981', // Emerald
            '#f59e0b', // Amber
            '#ef4444', // Red
            '#ec4899', // Pink
            '#84cc16', // Lime
            '#6b7280', // Gray
            '#14b8a6', // Teal
            '#f97316', // Orange
            '#3b82f6', // Blue
        ];
        
        $email = $this->from_email ?: 'unknown@example.com';
        $hash = crc32($email);
        $index = abs($hash) % count($colors);
        
        return $colors[$index];
    }
}
