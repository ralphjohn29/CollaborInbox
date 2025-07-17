# Email Connection Flow in CollaborInbox

## Overview

This document describes the flow of connecting email accounts to CollaborInbox.

## Connection Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    User Starts Connection                     │
│                  Navigate to Inbox → Connect Channel          │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
        ┌─────────────────────────────────────────┐
        │         Choose Email Provider           │
        │  ┌─────────┐ ┌─────────┐ ┌───────────┐ │
        │  │  Gmail  │ │ Outlook │ │   Other   │ │
        │  └────┬────┘ └────┬────┘ └─────┬─────┘ │
        └───────┼───────────┼─────────────┼───────┘
                │           │             │
                ▼           ▼             ▼
        ┌───────────────────────────────────────┐
        │    Currently: All redirect to         │
        │    "Other Mail Account" flow          │
        └─────────────────┬─────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────────┐
│                    Step 1: Email Details                      │
├──────────────────────────────────────────────────────────────┤
│  1. Copy forwarding address                                  │
│  2. Set up forwarding in email provider                      │
│  3. Enter email address                                      │
│  4. Enter display name                                       │
└─────────────────────────┬────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────────┐
│                Step 2: Channel Automation                     │
├──────────────────────────────────────────────────────────────┤
│  □ Automatically assign conversations                        │
│     └─ Select team member                                    │
│  □ Create tickets for incoming emails                        │
└─────────────────────────┬────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────────┐
│                    Step 3: Connect                            │
├──────────────────────────────────────────────────────────────┤
│  IMAP Settings:                                              │
│  - Server, Port, Encryption                                  │
│  SMTP Settings:                                              │
│  - Server, Port, Encryption                                  │
│  Credentials:                                                │
│  - Username, Password                                        │
│  [Test Connection]                                           │
│  Team Signature (optional)                                   │
└─────────────────────────┬────────────────────────────────────┘
                          │
                          ▼
                ┌─────────────────────┐
                │  Connection Result  │
                └──────────┬──────────┘
                           │
           ┌───────────────┴───────────────┐
           ▼                               ▼
    ┌──────────────┐               ┌──────────────┐
    │   Success    │               │    Error     │
    │              │               │              │
    │ Email saved  │               │ Show error   │
    │ Redirect to  │               │ Stay on form │
    │ channels list│               │              │
    └──────────────┘               └──────────────┘
```

## Data Flow

### 1. User Input Collection
```
User Input → Form Validation → Controller
    │
    ├─ Email Address
    ├─ Display Name
    ├─ Server Settings (IMAP/SMTP)
    ├─ Credentials
    └─ Optional Settings (Auto-assign, Tickets)
```

### 2. Connection Testing
```
Test Button → AJAX Request → EmailChannelController@testConnection
    │
    ├─ Validate inputs
    ├─ Attempt IMAP connection
    ├─ Return success/failure
    └─ Display result to user
```

### 3. Data Storage
```
Form Submit → EmailChannelController@storeOther
    │
    ├─ Validate all inputs
    ├─ Create EmailAccount model
    ├─ Encrypt passwords (automatic via model)
    ├─ Save to database
    └─ Redirect with success message
```

## Database Schema

```
email_accounts table
├── id
├── tenant_id
├── workspace_id
├── user_id (owner)
├── email_address
├── from_name
├── provider (gmail/outlook/other)
├── incoming_server_host
├── incoming_server_port
├── incoming_server_encryption
├── incoming_server_username
├── incoming_server_password (encrypted)
├── outgoing_server_host
├── outgoing_server_port
├── outgoing_server_encryption
├── outgoing_server_username
├── outgoing_server_password (encrypted)
├── oauth_access_token (encrypted)
├── oauth_refresh_token (encrypted)
├── is_active
└── timestamps
```

## Security Flow

```
Password Input → Model Accessor → Crypt::encryptString() → Database
                                                             │
                                                             ▼
Database → Model Accessor → Crypt::decryptString() → Application Use
```

## Email Processing Flow (After Connection)

```
External Email → Email Provider → Forwarding → CollaborInbox
                                                    │
                                                    ▼
                                            Parse & Store Email
                                                    │
                                                    ▼
                                        Apply Automation Rules
                                                    │
                                    ┌───────────────┴───────────────┐
                                    ▼                               ▼
                            Auto-assign to User            Create Ticket
                                    │                               │
                                    └───────────────┬───────────────┘
                                                    ▼
                                            Display in Inbox
```

## Error Handling

```
Connection Errors
├── Invalid Credentials → "Please check your username and password"
├── Wrong Server → "Unable to connect to server"
├── Port Blocked → "Connection timeout - check firewall"
├── SSL/TLS Error → "Encryption error - verify settings"
└── Generic Error → "Connection failed - check all settings"
```

## Future Enhancements

### OAuth2 Flow (Gmail/Outlook)
```
User Click → OAuth Provider → Authorize → Callback
    │                                        │
    └─ Redirect to provider ─────────────────┘
                                             │
                                             ▼
                                    Store OAuth Tokens
                                             │
                                             ▼
                                    Auto-configure IMAP/SMTP
```

### Real-time Email Sync
```
IMAP IDLE Connection
    │
    ├─ Monitor for new emails
    ├─ Instant notification
    └─ Background sync
```

## Testing Checklist

- [ ] Navigate to Connect Channel page
- [ ] Select "Other mail account"
- [ ] Complete Step 1 (Email Details)
- [ ] Complete Step 2 (Automation - optional)
- [ ] Complete Step 3 (Connection details)
- [ ] Test connection button works
- [ ] Form submission creates email account
- [ ] Encrypted fields are properly stored
- [ ] User can see connected accounts
- [ ] User can delete connected accounts
