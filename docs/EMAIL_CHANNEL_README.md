# Email Channel Feature Documentation

## Overview

The Email Channel feature in CollaborInbox allows users to connect their email accounts (Gmail, Outlook, and other providers) to centralize email management within the platform.

## Feature Location

- **Main Entry Point**: Inbox → Connect Channel (in sidebar)
- **Route**: `/inbox/channels/connect`
- **Controller**: `App\Http\Controllers\EmailChannelController`

## Current Implementation Status

### ✅ Completed
- Multi-step connection wizard UI
- Support for "Other mail account" with full IMAP/SMTP configuration
- Email forwarding setup instructions
- Connection testing functionality
- Secure password encryption using Laravel's Crypt facade
- Database schema with OAuth fields for future implementation
- Comprehensive documentation

### 🚧 In Progress
- Gmail OAuth integration (UI shows "Coming Soon")
- Outlook OAuth integration (UI shows "Coming Soon")
- Email channel listing page (`inbox.channels.index`)

### 📋 Future Enhancements
- OAuth2 authentication for Gmail and Outlook
- Real-time email synchronization
- Email templates and signatures
- Advanced automation rules
- Bulk email operations

## Technical Architecture

### Models
- **EmailAccount**: Stores email connection details with encrypted credentials
  - Relationships: User, Tenant, Workspace, Emails
  - Auto-encrypts passwords and OAuth tokens

### Database Fields
```sql
email_accounts
├── user_id (owner of the connection)
├── email_address
├── from_name (display name)
├── provider (gmail/outlook/other)
├── incoming_server_* (IMAP settings)
├── outgoing_server_* (SMTP settings)
├── oauth_access_token (for future OAuth)
├── oauth_refresh_token (for future OAuth)
└── is_active (enable/disable connection)
```

### Routes
```php
// Email Channel Management
Route::prefix('inbox/channels')->name('inbox.channels.')->group(function () {
    Route::get('/', 'index');                    // List channels
    Route::get('/connect', 'create');            // Choose provider
    Route::get('/gmail', 'gmailSetup');          // Gmail setup
    Route::get('/outlook', 'outlookSetup');      // Outlook setup
    Route::get('/other', 'otherSetup');          // Other provider setup
    Route::post('/gmail', 'storeGmail');         // Save Gmail
    Route::post('/outlook', 'storeOutlook');     // Save Outlook
    Route::post('/other', 'storeOther');         // Save Other
    Route::post('/test', 'testConnection');      // Test connection
    Route::delete('/{id}', 'destroy');           // Delete channel
});
```

### Views
```
resources/views/inbox/channels/
├── connect.blade.php    // Provider selection
├── other.blade.php      // 3-step setup wizard
├── gmail.blade.php      // Gmail setup (TODO)
├── outlook.blade.php    // Outlook setup (TODO)
└── index.blade.php      // List channels (TODO)
```

## Security Features

1. **Password Encryption**: All passwords are encrypted using Laravel's Crypt facade
2. **OAuth Token Storage**: Prepared for secure OAuth token storage
3. **User Isolation**: Users can only see/manage their own email connections
4. **Connection Testing**: Verify credentials before saving

## Usage Instructions

### For End Users
1. Navigate to Inbox in the sidebar
2. Click "Connect Channel" under Email Settings
3. Choose email provider (currently all redirect to "Other")
4. Follow the 3-step wizard:
   - Step 1: Enter email details and set up forwarding
   - Step 2: Configure automation (optional)
   - Step 3: Enter server details and test connection

### For Developers

#### Adding a New Email Provider
1. Add provider-specific method in `EmailChannelController`
2. Create view in `resources/views/inbox/channels/`
3. Add route in `web.php`
4. Update provider selection in `connect.blade.php`

#### Testing Email Connection
```php
// The testConnection method supports:
$request->validate([
    'provider' => 'required|in:gmail,outlook,other',
    'email_address' => 'required|email',
    // Additional fields based on provider
]);
```

## API Endpoints

### Test Connection
- **POST** `/inbox/channels/test`
- **Payload**:
  ```json
  {
    "provider": "other",
    "email_address": "user@example.com",
    "incoming_server_host": "imap.example.com",
    "incoming_server_port": 993,
    "incoming_server_encryption": "ssl",
    "incoming_server_username": "user@example.com",
    "incoming_server_password": "password"
  }
  ```
- **Response**:
  ```json
  {
    "success": true,
    "message": "Connection successful!"
  }
  ```

## Troubleshooting

### Common Issues

1. **Connection Failed**
   - Verify server settings match provider requirements
   - Check if using app-specific password (Gmail/Outlook)
   - Ensure IMAP is enabled in email provider
   - Check firewall/port restrictions

2. **Emails Not Syncing**
   - Verify forwarding is set up correctly
   - Check if email account is active
   - Review automation rules

3. **Missing Views Error**
   - Create missing view files as needed
   - Check view paths in controller methods

## Testing Checklist

- [ ] User can navigate to Connect Channel
- [ ] Provider selection page loads
- [ ] "Other mail account" wizard works
- [ ] Step navigation works correctly
- [ ] Form validation works
- [ ] Connection test provides feedback
- [ ] Successful connection saves to database
- [ ] Passwords are encrypted in database
- [ ] User can only see their own connections
- [ ] Error messages are user-friendly

## Related Documentation

- [Email Connection Guide](./EMAIL_CONNECTION_GUIDE.md)
- [Email Settings Quick Reference](./EMAIL_SETTINGS_QUICK_REFERENCE.md)
- [Email Connection Flow](./EMAIL_CONNECTION_FLOW.md)

## Support

For issues or questions:
1. Check the troubleshooting section
2. Review error logs in `storage/logs/`
3. Contact development team

---

*Last updated: July 12, 2025*
