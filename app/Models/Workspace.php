<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'name',
        'email_alias',
        'postmark_server_token',
        'postmark_inbound_webhook_token',
        'settings',
        'email_settings',
        'is_active',
        'trial_ends_at',
        'subscription_status',
    ];

    protected $casts = [
        'settings' => 'array',
        'email_settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    protected $attributes = [
        'settings' => '{}',
        'email_settings' => '{}',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {
            if (empty($workspace->uid)) {
                $workspace->uid = static::generateUniqueUid();
            }
            if (empty($workspace->email_alias)) {
                $workspace->email_alias = 'sales+' . $workspace->uid . '@collaborinbox.com';
            }
            if (empty($workspace->trial_ends_at)) {
                $workspace->trial_ends_at = now()->addDays(14);
            }
        });
    }

    /**
     * Generate a unique workspace UID
     */
    public static function generateUniqueUid(): string
    {
        do {
            $uid = Str::lower(Str::random(8));
        } while (static::where('uid', $uid)->exists());

        return $uid;
    }

    /**
     * Get the workspace's users
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the workspace's conversations
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get the workspace's emails
     */
    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Get the workspace's contacts
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get the workspace's email templates
     */
    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    /**
     * Get the workspace's automation rules
     */
    public function automationRules(): HasMany
    {
        return $this->hasMany(AutomationRule::class);
    }

    /**
     * Get the workspace's invitations
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }

    /**
     * Get the workspace's audit logs
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the workspace's statistics
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(WorkspaceStat::class);
    }

    /**
     * Check if workspace is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired
     */
    public function isTrialExpired(): bool
    {
        return $this->subscription_status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isPast();
    }

    /**
     * Get setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set setting value
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get email setting value
     */
    public function getEmailSetting(string $key, $default = null)
    {
        return data_get($this->email_settings, $key, $default);
    }

    /**
     * Set email setting value
     */
    public function setEmailSetting(string $key, $value): void
    {
        $emailSettings = $this->email_settings ?? [];
        data_set($emailSettings, $key, $value);
        $this->email_settings = $emailSettings;
        $this->save();
    }

    /**
     * Get workspace statistics for a date range
     */
    public function getStatistics($startDate = null, $endDate = null)
    {
        $query = $this->statistics();

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->get();
    }

    /**
     * Get today's statistics
     */
    public function getTodayStatistics()
    {
        return $this->statistics()
            ->whereDate('date', today())
            ->first();
    }

    /**
     * Create admin user for workspace
     */
    public function createAdminUser(array $userData): User
    {
        $user = $this->users()->create(array_merge($userData, [
            'is_workspace_creator' => true,
        ]));

        $user->assignRole('admin');

        return $user;
    }
}
