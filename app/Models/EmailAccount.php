<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class EmailAccount extends Model
{
    protected $fillable = [
        'tenant_id',
        'workspace_id',
        'user_id',
        'email_prefix',
        'email_address',
        'from_name',
        'display_name',
        'description',
        'is_active',
        'incoming_server_type',
        'incoming_server_host',
        'incoming_server_port',
        'incoming_server_encryption',
        'incoming_server_username',
        'incoming_server_password',
        'incoming_server_ssl',
        'outgoing_server_host',
        'outgoing_server_port',
        'outgoing_server_encryption',
        'outgoing_server_username',
        'outgoing_server_password',
        'outgoing_server_ssl',
        'provider',
        'outgoing_server_type',
        'oauth_access_token',
        'oauth_refresh_token',
        'oauth_expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'incoming_server_ssl' => 'boolean',
        'outgoing_server_ssl' => 'boolean',
        'oauth_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'incoming_server_password',
        'outgoing_server_password',
        'oauth_access_token',
        'oauth_refresh_token',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    // Encrypt password before saving
    public function setIncomingServerPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['incoming_server_password'] = Crypt::encryptString($value);
        }
    }

    // Decrypt password when retrieving
    public function getIncomingServerPasswordAttribute($value)
    {
        if ($value) {
            return Crypt::decryptString($value);
        }
        return null;
    }

    // Encrypt password before saving
    public function setOutgoingServerPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['outgoing_server_password'] = Crypt::encryptString($value);
        }
    }

    // Decrypt password when retrieving
    public function getOutgoingServerPasswordAttribute($value)
    {
        if ($value) {
            return Crypt::decryptString($value);
        }
        return null;
    }

    // Encrypt OAuth access token before saving
    public function setOauthAccessTokenAttribute($value)
    {
        if ($value) {
            $this->attributes['oauth_access_token'] = Crypt::encryptString($value);
        }
    }

    // Decrypt OAuth access token when retrieving
    public function getOauthAccessTokenAttribute($value)
    {
        if ($value) {
            return Crypt::decryptString($value);
        }
        return null;
    }

    // Encrypt OAuth refresh token before saving
    public function setOauthRefreshTokenAttribute($value)
    {
        if ($value) {
            $this->attributes['oauth_refresh_token'] = Crypt::encryptString($value);
        }
    }

    // Decrypt OAuth refresh token when retrieving
    public function getOauthRefreshTokenAttribute($value)
    {
        if ($value) {
            return Crypt::decryptString($value);
        }
        return null;
    }

    public function getUnreadCount()
    {
        return $this->emails()->where('status', 'unread')->count();
    }
}
