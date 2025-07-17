# Email Connection Guide for CollaborInbox

This guide will help you connect your email accounts to CollaborInbox. We support Gmail, Microsoft Outlook, and other email providers through IMAP/SMTP configuration.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Gmail Setup](#gmail-setup)
- [Microsoft Outlook Setup](#microsoft-outlook-setup)
- [Other Email Providers](#other-email-providers)
- [Troubleshooting](#troubleshooting)
- [Security Considerations](#security-considerations)

## Prerequisites

Before connecting your email account, ensure you have:
- Admin access to your email account
- Two-factor authentication enabled (recommended)
- App-specific passwords (for Gmail and Outlook)

## Gmail Setup

### Step 1: Enable 2-Step Verification
1. Go to your [Google Account settings](https://myaccount.google.com/)
2. Navigate to **Security**
3. Under "Signing in to Google," select **2-Step Verification**
4. Follow the prompts to enable it

### Step 2: Generate App Password
1. Go to [Google Account Security](https://myaccount.google.com/security)
2. Under "Signing in to Google," select **App passwords**
3. Select app: **Mail**
4. Select device: **Other (Custom name)**
5. Enter: **CollaborInbox**
6. Click **Generate**
7. Copy the 16-character password (you'll need this later)

### Step 3: Enable IMAP in Gmail
1. Open Gmail
2. Click the gear icon → **See all settings**
3. Click the **Forwarding and POP/IMAP** tab
4. In the "IMAP Access" section, select **Enable IMAP**
5. Click **Save Changes**

### Step 4: Connect to CollaborInbox
1. In CollaborInbox, navigate to **Inbox** → **Connect Channel**
2. Select **Gmail** (Note: Currently shows "Coming Soon" - use "Other mail account" instead)
3. Enter the following details:
   - **Email Address**: your-email@gmail.com
   - **Display Name**: Your Name or Team Name
   - **IMAP Server**: imap.gmail.com
   - **IMAP Port**: 993
   - **IMAP Encryption**: SSL
   - **SMTP Server**: smtp.gmail.com
   - **SMTP Port**: 587
   - **SMTP Encryption**: TLS
   - **Username**: your-email@gmail.com
   - **Password**: [Your 16-character app password from Step 2]

## Microsoft Outlook Setup

### Step 1: Enable Two-Factor Authentication
1. Go to [Microsoft Account Security](https://account.microsoft.com/security)
2. Click on **Advanced security options**
3. Under "Two-step verification," click **Turn on**
4. Follow the setup wizard

### Step 2: Create App Password
1. Go to [Microsoft Account Security](https://account.microsoft.com/security)
2. Click on **Advanced security options**
3. Under "App passwords," click **Create a new app password**
4. Copy the generated password

### Step 3: Connect to CollaborInbox
1. In CollaborInbox, navigate to **Inbox** → **Connect Channel**
2. Select **Microsoft Outlook** (Note: Currently shows "Coming Soon" - use "Other mail account" instead)
3. Enter the following details:
   - **Email Address**: your-email@outlook.com
   - **Display Name**: Your Name or Team Name
   - **IMAP Server**: outlook.office365.com
   - **IMAP Port**: 993
   - **IMAP Encryption**: SSL
   - **SMTP Server**: smtp-mail.outlook.com
   - **SMTP Port**: 587
   - **SMTP Encryption**: TLS
   - **Username**: your-email@outlook.com
   - **Password**: [Your app password from Step 2]

## Other Email Providers

For other email providers, you'll need to obtain the IMAP and SMTP settings from your email provider.

### Common Email Provider Settings

#### Yahoo Mail
- **IMAP Server**: imap.mail.yahoo.com
- **IMAP Port**: 993
- **IMAP Encryption**: SSL
- **SMTP Server**: smtp.mail.yahoo.com
- **SMTP Port**: 587 or 465
- **SMTP Encryption**: TLS (port 587) or SSL (port 465)

#### iCloud Mail
- **IMAP Server**: imap.mail.me.com
- **IMAP Port**: 993
- **IMAP Encryption**: SSL
- **SMTP Server**: smtp.mail.me.com
- **SMTP Port**: 587
- **SMTP Encryption**: TLS

#### AOL Mail
- **IMAP Server**: imap.aol.com
- **IMAP Port**: 993
- **IMAP Encryption**: SSL
- **SMTP Server**: smtp.aol.com
- **SMTP Port**: 587
- **SMTP Encryption**: TLS

#### Custom/Business Email
Contact your email administrator or hosting provider for:
- IMAP server address
- IMAP port (usually 993 for SSL or 143 for non-SSL)
- SMTP server address
- SMTP port (usually 587 for TLS or 465 for SSL)
- Encryption methods supported

### Step-by-Step Setup for Other Providers

1. **Navigate to Connect Channel**
   - Go to CollaborInbox
   - Click on **Inbox** in the sidebar
   - Click on **Connect Channel**

2. **Select Other Mail Account**
   - Click on the **Other mail account** option

3. **Step 1: Email Details**
   - Copy the forwarding address provided (e.g., hello-447@243282747.nd2.hubspot-inbox.com)
   - Set up email forwarding in your email provider (see provider-specific instructions below)
   - Enter your email address
   - Enter a display name

4. **Step 2: Channel Automation** (Optional)
   - Enable automatic assignment to team members
   - Enable automatic ticket creation

5. **Step 3: Connect**
   - Enter your IMAP server details
   - Enter your SMTP server details
   - Enter your username and password
   - Click **Test Connection** to verify settings
   - Add optional team signature
   - Click **Connect & finish**

## Setting Up Email Forwarding

### Gmail Forwarding
1. Open Gmail settings
2. Go to **Forwarding and POP/IMAP** tab
3. Click **Add a forwarding address**
4. Enter the CollaborInbox forwarding address
5. Click **Next** → **Proceed** → **OK**
6. Confirm the forwarding request (check your CollaborInbox)
7. Select **Forward a copy of incoming mail to** and choose the address
8. Choose what to do with Gmail's copy (keep, archive, or delete)
9. Save changes

### Outlook Forwarding
1. Sign in to Outlook.com
2. Go to Settings → View all Outlook settings
3. Select **Mail** → **Forwarding**
4. Check **Enable forwarding**
5. Enter the CollaborInbox forwarding address
6. Choose to keep a copy of forwarded messages (optional)
7. Click **Save**

### Yahoo Mail Forwarding
1. Click the Settings icon → **More Settings**
2. Click **Mailboxes**
3. Under your email address, click **Add**
4. Enter the CollaborInbox forwarding address
5. Click **Verify**
6. Confirm the forwarding address
7. Click **Save**

## Troubleshooting

### Common Issues and Solutions

#### "Connection Failed" Error
- **Check credentials**: Ensure username and password are correct
- **App passwords**: Use app-specific passwords for Gmail and Outlook
- **2FA**: Ensure two-factor authentication is properly configured
- **Less secure apps**: For some providers, you may need to enable "less secure app access"

#### Emails Not Appearing
- **Check forwarding**: Ensure email forwarding is properly set up
- **Spam filters**: Check if emails are being filtered
- **IMAP folders**: Ensure you're checking the correct folders

#### Cannot Send Emails
- **SMTP settings**: Verify SMTP server, port, and encryption
- **Authentication**: Ensure SMTP authentication is enabled
- **Sending limits**: Check if you've hit provider sending limits

### Testing Your Connection

1. Use the **Test Connection** button in Step 3
2. Send a test email to your connected account
3. Check if it appears in CollaborInbox within a few minutes
4. Try replying to test outgoing mail functionality

## Security Considerations

### Best Practices
1. **Use App Passwords**: Never use your main account password
2. **Enable 2FA**: Always use two-factor authentication
3. **Regular Reviews**: Periodically review connected accounts
4. **Revoke Access**: Remove unused connections promptly
5. **Secure Storage**: CollaborInbox encrypts all stored credentials

### Data Privacy
- All email credentials are encrypted using industry-standard encryption
- OAuth tokens are stored securely and refreshed automatically
- No email content is stored permanently without your consent
- You can disconnect accounts at any time

### Compliance
- GDPR compliant data handling
- SOC 2 Type II certified infrastructure
- Regular security audits and penetration testing

## Additional Resources

- [Gmail IMAP Settings](https://support.google.com/mail/answer/7126229)
- [Outlook IMAP Settings](https://support.microsoft.com/en-us/office/pop-imap-and-smtp-settings-8361e398-8af4-4e97-b147-6c6c4ac95353)
- [Yahoo Mail Settings](https://help.yahoo.com/kb/SLN4075.html)
- [iCloud Mail Settings](https://support.apple.com/en-us/HT202304)

## Need Help?

If you encounter any issues:
1. Check this documentation first
2. Review the troubleshooting section
3. Contact support at support@collaborinbox.com
4. Include your email provider and any error messages

---

*Last updated: July 12, 2025*
