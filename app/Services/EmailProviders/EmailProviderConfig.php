<?php

namespace App\Services\EmailProviders;

class EmailProviderConfig
{
    /**
     * Get configuration for popular email providers
     */
    public static function getProviderConfig($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        
        $providers = [
            // Gmail / Google Workspace
            'gmail.com' => [
                'name' => 'Gmail',
                'imap' => [
                    'host' => 'imap.gmail.com',
                    'port' => 993,
                    'encryption' => 'ssl',
                ],
                'smtp' => [
                    'host' => 'smtp.gmail.com',
                    'port' => 587,
                    'encryption' => 'tls',
                ],
                'oauth' => true,
                'app_password_required' => true,
                'instructions' => 'Enable 2FA and create an App Password at https://myaccount.google.com/apppasswords',
            ],
            
            // Outlook.com / Hotmail
            'outlook.com' => [
                'name' => 'Outlook',
                'imap' => [
                    'host' => 'outlook.office365.com',
                    'port' => 993,
                    'encryption' => 'ssl',
                ],
                'smtp' => [
                    'host' => 'smtp-mail.outlook.com',
                    'port' => 587,
                    'encryption' => 'tls',
                ],
                'oauth' => true,
                'app_password_required' => true,
                'instructions' => 'Create an App Password at https://account.microsoft.com/security',
            ],
            
            // Office 365
            'office365.com' => [
                'name' => 'Office 365',
                'imap' => [
                    'host' => 'outlook.office365.com',
                    'port' => 993,
                    'encryption' => 'ssl',
                ],
                'smtp' => [
                    'host' => 'smtp.office365.com',
                    'port' => 587,
                    'encryption' => 'tls',
                ],
                'oauth' => true,
                'modern_auth' => true,
            ],
            
            // Yahoo Mail
            'yahoo.com' => [
                'name' => 'Yahoo Mail',
                'imap' => [
                    'host' => 'imap.mail.yahoo.com',
                    'port' => 993,
                    'encryption' => 'ssl',
                ],
                'smtp' => [
                    'host' => 'smtp.mail.yahoo.com',
                    'port' => 587,
                    'encryption' => 'tls',
                ],
                'app_password_required' => true,
                'instructions' => 'Generate App Password at https://login.yahoo.com/account/security',
            ],
            
            // iCloud
            'icloud.com' => [
                'name' => 'iCloud',
                'imap' => [
                    'host' => 'imap.mail.me.com',
                    'port' => 993,
                    'encryption' => 'ssl',
                ],
                'smtp' => [
                    'host' => 'smtp.mail.me.com',
                    'port' => 587,
                    'encryption' => 'tls',
                ],
                'app_password_required' => true,
                'instructions' => 'Create app-specific password at https://appleid.apple.com',
            ],
            
            // ProtonMail (Bridge required)
            'protonmail.com' => [
                'name' => 'ProtonMail',
                'imap' => [
                    'host' => '127.0.0.1',
                    'port' => 1143,
                    'encryption' => 'tls',
                ],
                'smtp' => [
                    'host' => '127.0.0.1',
                    'port' => 1025,
                    'encryption' => 'tls',
                ],
                'bridge_required' => true,
                'instructions' => 'Install ProtonMail Bridge from https://protonmail.com/bridge',
            ],
        ];
        
        // Check for domain variants
        $domainVariants = [
            'hotmail.com' => 'outlook.com',
            'live.com' => 'outlook.com',
            'msn.com' => 'outlook.com',
            'googlemail.com' => 'gmail.com',
            'ymail.com' => 'yahoo.com',
            'rocketmail.com' => 'yahoo.com',
            'me.com' => 'icloud.com',
            'mac.com' => 'icloud.com',
            'pm.me' => 'protonmail.com',
            'proton.me' => 'protonmail.com',
        ];
        
        if (isset($domainVariants[$domain])) {
            $domain = $domainVariants[$domain];
        }
        
        // Check for Google Workspace domains
        if (self::isGoogleWorkspace($domain)) {
            return array_merge($providers['gmail.com'], [
                'name' => 'Google Workspace',
                'is_workspace' => true,
            ]);
        }
        
        return $providers[$domain] ?? self::getGenericConfig();
    }
    
    /**
     * Check if domain uses Google Workspace
     */
    private static function isGoogleWorkspace($domain)
    {
        // You can enhance this with actual MX record checking
        // For now, we'll use a simple check
        $mxRecords = @dns_get_record($domain, DNS_MX);
        if ($mxRecords) {
            foreach ($mxRecords as $mx) {
                if (strpos($mx['target'], 'google.com') !== false || 
                    strpos($mx['target'], 'googlemail.com') !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Get generic IMAP/SMTP configuration
     */
    private static function getGenericConfig()
    {
        return [
            'name' => 'Custom Email Server',
            'imap' => [
                'host' => '',
                'port' => 993,
                'encryption' => 'ssl',
            ],
            'smtp' => [
                'host' => '',
                'port' => 587,
                'encryption' => 'tls',
            ],
            'custom' => true,
            'instructions' => 'Contact your email administrator for server settings',
        ];
    }
    
    /**
     * Validate email credentials
     */
    public static function validateConnection($config, $username, $password)
    {
        try {
            $connectionString = sprintf(
                '{%s:%d/imap/%s}INBOX',
                $config['imap']['host'],
                $config['imap']['port'],
                $config['imap']['encryption']
            );
            
            $imap = @imap_open($connectionString, $username, $password);
            
            if ($imap) {
                imap_close($imap);
                return ['success' => true];
            }
            
            return [
                'success' => false,
                'error' => imap_last_error() ?: 'Connection failed'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
