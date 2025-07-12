# CollaborInbox Email Setup Guide

## Overview
This guide covers setting up real email receiving for CollaborInbox, similar to HubSpot's shared inbox functionality.

## Email Architecture Options

### 1. Local Development with Mailpit (Current Setup)
- **Status**: Already configured
- **Access**: http://localhost:8025
- **Use**: Testing email workflows without real emails

### 2. Webhook-Based Email Receiving (Recommended for Production)

#### Option A: SendGrid Inbound Parse
```env
# Add to .env
SENDGRID_API_KEY=your_sendgrid_api_key
SENDGRID_WEBHOOK_SECRET=your_webhook_secret
INBOUND_EMAIL_DOMAIN=inbox.yourdomain.com
```

**Setup Steps:**
1. Sign up for SendGrid (free tier available)
2. Configure Inbound Parse webhook
3. Point MX records to SendGrid
4. Set webhook URL to: `https://yourdomain.com/api/webhooks/sendgrid/inbound`

#### Option B: Postmark Inbound
```env
# Add to .env
POSTMARK_SERVER_TOKEN=your_server_token
POSTMARK_INBOUND_WEBHOOK_TOKEN=your_webhook_token
POSTMARK_INBOUND_ADDRESS=support@pm.yourdomain.com
```

**Setup Steps:**
1. Create Postmark account
2. Set up server and get tokens
3. Configure inbound email address
4. Set webhook URL

### 3. IMAP/SMTP Integration (Traditional Approach)

```env
# For Gmail/Google Workspace
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls

# IMAP settings for fetching
IMAP_HOST=imap.gmail.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_USERNAME=your-email@gmail.com
IMAP_PASSWORD=your-app-specific-password
```

## Setting Up Real Email Receiving

### Step 1: Choose Your Email Provider

#### For Testing (Free Options):
1. **Mailtrap** - Great for development
2. **MailHog** - Local SMTP testing
3. **Mailpit** - Already configured in your Docker setup

#### For Production (Paid/Freemium):
1. **SendGrid** - 100 emails/day free
2. **Postmark** - 100 emails/month free
3. **Amazon SES** - $0.10 per 1000 emails
4. **Google Workspace** - Full email solution

### Step 2: Domain Configuration

For a professional setup like HubSpot, you need:

1. **Domain**: e.g., `support.yourdomain.com`
2. **DNS Records**:
   ```
   MX Records:
   - Priority 10: mx.sendgrid.net (if using SendGrid)
   - Priority 10: inbound.postmarkapp.com (if using Postmark)
   
   SPF Record:
   v=spf1 include:sendgrid.net ~all
   
   DKIM Records:
   - Will be provided by your email service
   
   DMARC Record:
   v=DMARC1; p=none; rua=mailto:dmarc@yourdomain.com
   ```

### Step 3: Implement Email Processing

Create these files to handle incoming emails:

#### Webhook Controller
`app/Http/Controllers/Webhooks/EmailWebhookController.php`

#### Email Processing Job
`app/Jobs/ProcessIncomingEmail.php`

#### Email Parser Service
`app/Services/EmailParserService.php`

## Local Testing Setup (Immediate Solution)

Since you want to test immediately, let's use ngrok to expose your local environment:

1. **Install ngrok**: https://ngrok.com/download
2. **Run ngrok**: `ngrok http 8000`
3. **Get public URL**: e.g., `https://abc123.ngrok.io`
4. **Configure webhook** in your email service to point to:
   `https://abc123.ngrok.io/api/webhooks/email/inbound`

## Testing Email Flow

### 1. Using Mailpit (Already Available)
- Send test emails to any@email.com
- View at: http://localhost:8025
- Emails are caught locally

### 2. Using Real Email Service
- Configure SendGrid/Postmark
- Send email to: support@yourdomain.com
- Process via webhook
- Store in database
- Display in inbox

## Implementation Checklist

- [ ] Choose email service provider
- [ ] Set up domain and DNS records
- [ ] Configure webhooks
- [ ] Implement email processing
- [ ] Set up email parsing
- [ ] Configure email accounts
- [ ] Test email receiving
- [ ] Implement email sending
- [ ] Set up email templates
- [ ] Configure auto-responses

## Security Considerations

1. **Webhook Verification**: Always verify webhook signatures
2. **Email Validation**: Sanitize and validate all incoming email data
3. **Attachment Handling**: Scan attachments for viruses
4. **Rate Limiting**: Implement rate limits on webhooks
5. **SPF/DKIM/DMARC**: Properly configure email authentication

## Next Steps

1. For immediate testing: Use Mailpit (already configured)
2. For staging: Set up SendGrid with ngrok
3. For production: Configure proper domain with email service
