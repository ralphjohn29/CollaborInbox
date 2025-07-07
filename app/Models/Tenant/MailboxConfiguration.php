<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;

class MailboxConfiguration extends Model
{
    use HasFactory; // Assuming you might want factories later

    // Specify the connection if your tenant connection name is not the default 'tenant'
    // protected $connection = 'tenant'; 

    protected $table = 'mailbox_configurations';

    protected $fillable = [
        'email_address',
        'imap_server',
        'port',
        'encryption_type',
        'username',
        'encrypted_password', // We'll handle encryption via mutator
        'folder_to_monitor',
        'last_sync_timestamp',
        'status',
        'last_error',
    ];

    protected $casts = [
        'last_sync_timestamp' => 'datetime',
        // Do NOT cast encrypted_password here, handle via mutator/accessor
    ];

    /**
     * Encrypt the password before saving.
     *
     * @param  string  $value
     * @return void
     */
    public function setEncryptedPasswordAttribute(string $value): void
    {
        $this->attributes['encrypted_password'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt the password when accessing.
     * Returns null if decryption fails.
     * 
     * Use a dedicated method like getDecryptedPassword() for clarity 
     * instead of an accessor to avoid accidental decryption/logging.
     *
     * @return string|null
     */
    public function getDecryptedPassword(): ?string
    {
        try {
            return Crypt::decryptString($this->attributes['encrypted_password']);
        } catch (DecryptException $e) {
            Log::error('Failed to decrypt password for mailbox configuration', [
                'mailbox_id' => $this->id,
                'email' => $this->email_address,
                'error' => $e->getMessage()
            ]);
            return null; 
        }
    }

    // Relationships (if needed later)
    // public function tenant()
    // {
    //     // If you needed a reference back to the central tenant model (uncommon from tenant DB)
    //     // This would require more complex setup
    // }
} 