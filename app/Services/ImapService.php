<?php

namespace App\Services;

use App\Models\Tenant\MailboxConfiguration;
use Exception;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Support\MessageCollection;

class ImapService
{
    protected ClientManager $clientManager;

    public function __construct()
    {
        // ClientManager can be instantiated directly or potentially injected via service container if configured
        $this->clientManager = new ClientManager(); 
    }

    /**
     * Get an IMAP client configured for a specific mailbox.
     *
     * @param MailboxConfiguration $config
     * @return Client|null Returns the client on success, null on failure.
     */
    protected function getClient(MailboxConfiguration $config): ?Client
    {
        $decryptedPassword = $config->getDecryptedPassword();
        if ($decryptedPassword === null) {
            Log::error('Cannot create IMAP client: Password decryption failed.', ['mailbox_id' => $config->id]);
            return null;
        }

        $options = [
            'host'          => $config->imap_server,
            'port'          => $config->port,
            'encryption'    => $config->encryption_type === 'none' ? false : $config->encryption_type, // 'false' for no encryption
            'validate_cert' => config('imap.options.validate_cert', true), // Use package default or app config
            'username'      => $config->username,
            'password'      => $decryptedPassword,
            'protocol'      => 'imap',
            // Consider adding timeouts from config if needed
            // 'timeout' => config('imap.options.connect_timeout', 30), 
        ];

        try {
            // Create a client instance using the dynamic options
            $client = $this->clientManager->make($options);
            return $client;
        } catch (Exception $e) {
            Log::error('Failed to create IMAP client instance', [
                'mailbox_id' => $config->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Test the connection for a given mailbox configuration.
     *
     * @param MailboxConfiguration $config
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function testConnection(MailboxConfiguration $config): array
    {
        $client = $this->getClient($config);
        if (!$client) {
            return ['success' => false, 'error' => 'Failed to initialize IMAP client (check logs).'];
        }

        try {
            $client->connect();
            $client->disconnect(); // Disconnect immediately after successful connection test
            return ['success' => true, 'error' => null];
        } catch (AuthFailedException $e) {
            Log::warning('IMAP Auth Failed', ['mailbox_id' => $config->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Authentication failed. Please check username/password.'];
        } catch (ConnectionFailedException $e) {
            Log::warning('IMAP Connection Failed', ['mailbox_id' => $config->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Could not connect to server. Check host/port/encryption settings.'];
        } catch (Exception $e) {
            Log::error('IMAP Generic Connection Error', ['mailbox_id' => $config->id, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
    }

    /**
     * Fetch messages from a specific folder.
     * 
     * @param MailboxConfiguration $config
     * @param string $folderName Default 'INBOX'
     * @param string $criteria Search criteria (e.g., 'ALL', 'UNSEEN') Default 'ALL'
     * @param bool $markAsRead Mark messages as seen after fetching. Default false.
     * @return MessageCollection|null Returns a collection of messages or null on failure.
     */
    public function fetchMessages(MailboxConfiguration $config, string $folderName = 'INBOX', string $criteria = 'ALL', bool $markAsRead = false): ?MessageCollection
    {
        $client = $this->getClient($config);
        if (!$client) {
             return null; // Error logged in getClient
        }

        try {
            $client->connect();
            
            /** @var Folder $folder */
            $folder = $client->getFolder($folderName);
            if (!$folder) {
                Log::error('IMAP folder not found', ['mailbox_id' => $config->id, 'folder' => $folderName]);
                $client->disconnect();
                return null;
            }
            
            // Build query
            $query = $folder->query()->where($criteria); 
            
            if ($markAsRead) {
                $query->markAsRead();
            } else {
                 $query->leaveUnread();
            }
            
            // Fetch messages (consider pagination or limiting for large mailboxes later)
            /** @var MessageCollection $messages */
            $messages = $query->get();

            $client->disconnect(); 
            return $messages;

        } catch (AuthFailedException | ConnectionFailedException $e) {
            Log::error('IMAP connection/auth error during fetch', ['mailbox_id' => $config->id, 'error' => $e->getMessage()]);
            // Attempt to disconnect if connection was partially established
            try { $client->disconnect(); } catch (Exception $ex) {} 
            return null;
         } catch (Exception $e) {
            Log::error('IMAP error fetching messages', ['mailbox_id' => $config->id, 'folder' => $folderName, 'error' => $e->getMessage()]);
             // Attempt to disconnect
            try { $client->disconnect(); } catch (Exception $ex) {} 
            return null;
        }
    }
} 