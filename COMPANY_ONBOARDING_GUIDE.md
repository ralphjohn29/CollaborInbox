# CollaborInbox Company Onboarding Guide

## Welcome to CollaborInbox! 🎉

This guide will walk you through setting up your company's shared email CRM system. The entire process takes about 15-20 minutes.

## Pre-Onboarding Requirements

Before starting, please have ready:
- Company name
- Primary admin email address
- Company logo (optional, can be added later)
- Team member email addresses (optional, can be added later)

## Step 1: Account Creation (2 minutes)

### Option A: Email/Password Signup
1. Visit `https://collaborinbox.com/signup`
2. Enter your details:
   - Full Name
   - Work Email
   - Password (min 8 characters)
   - Company Name
3. Click "Create Workspace"
4. Check your email for verification link
5. Click the verification link to activate your account

### Option B: Google/Microsoft OAuth
1. Visit `https://collaborinbox.com/signup`
2. Click "Sign up with Google" or "Sign up with Microsoft"
3. Authorize CollaborInbox to access your account
4. Enter your Company Name
5. Click "Create Workspace"

## Step 2: Initial Workspace Setup (3 minutes)

After account creation, you'll be guided through the setup wizard:

### 2.1 Company Profile
- **Company Name**: Already filled from signup
- **Industry**: Select from dropdown (helps us customize features)
- **Team Size**: Approximate number of agents
- **Time Zone**: For scheduling and notifications
- **Company Logo**: Upload (PNG/JPG, max 2MB)

### 2.2 Email Configuration
Your workspace automatically receives a unique email address:
```
sales+[your-workspace-id]@collaborinbox.com
```

Example: `sales+abc123xy@collaborinbox.com`

This email will:
- Automatically forward to your shared inbox
- Be DKIM verified for deliverability
- Support custom routing rules

### 2.3 Notification Preferences
Choose how you want to be notified:
- [ ] Email notifications for new conversations
- [ ] Email digest (daily/weekly)
- [ ] Desktop notifications (requires permission)
- [ ] Mobile push notifications (if using mobile app)

## Step 3: Team Setup (5 minutes)

### 3.1 Invite Team Members
1. Click "Invite Team" in the dashboard
2. Enter team member emails (comma separated)
3. Select their role:
   - **Admin**: Full access, can manage workspace settings
   - **Agent**: Can manage conversations and contacts
   - **Viewer**: Read-only access to conversations
4. Customize invitation message (optional)
5. Click "Send Invitations"

### 3.2 Team Member Onboarding
When team members accept invitations, they will:
1. Click the invitation link in their email
2. Set their password (or use OAuth)
3. Complete their profile (name, avatar, timezone)
4. Get a quick tour of the interface

## Step 4: Customize Your Workflow (5 minutes)

### 4.1 Disposition Board Setup
The default board includes these columns:
- **New**: Incoming conversations
- **In Progress**: Active conversations
- **Waiting**: Pending customer response
- **Resolved**: Completed conversations

To customize:
1. Go to Settings → Workflow
2. Click "Add Column" to create custom statuses
3. Drag to reorder columns
4. Set colors for visual organization
5. Define automation rules (optional)

### 4.2 Email Templates
Create common response templates:
1. Go to Settings → Templates
2. Click "New Template"
3. Enter:
   - Template Name
   - Subject Line
   - Email Body (supports variables)
4. Save and organize by category

Example variables:
- `{{customer_name}}` - Customer's name
- `{{agent_name}}` - Your name
- `{{ticket_number}}` - Conversation ID
- `{{company_name}}` - Your company name

### 4.3 Auto-Responses
Set up automatic responses:
1. Go to Settings → Automation
2. Create rules like:
   - Auto-acknowledge new emails
   - Out-of-office messages
   - Routing based on keywords
   - Spam filtering (score > 5 auto-archived)

## Step 5: Integration Setup (5 minutes)

### 5.1 Email Forwarding
To connect your existing email:
1. Go to Settings → Email Integration
2. Choose your email provider
3. Follow provider-specific instructions:

**Gmail:**
1. Create a filter for emails to forward
2. Forward to your workspace email
3. Verify forwarding address

**Outlook:**
1. Create an inbox rule
2. Forward to workspace email
3. Confirm forwarding

**Other Providers:**
- We support IMAP/SMTP integration
- Contact support for assistance

### 5.2 Calendar Integration (Optional)
Connect your calendar for scheduling:
1. Go to Settings → Integrations
2. Click "Connect Calendar"
3. Choose Google Calendar or Outlook
4. Authorize access
5. Select which calendars to sync

### 5.3 CRM Integration (Optional)
Import existing contacts:
1. Export contacts from your current system (CSV)
2. Go to Contacts → Import
3. Upload CSV file
4. Map fields to CollaborInbox fields
5. Review and import

## Step 6: Training & Best Practices (Ongoing)

### 6.1 Quick Start Tutorial
Complete the interactive tutorial:
1. Click the "?" icon in the top menu
2. Select "Interactive Tutorial"
3. Follow the 5-minute walkthrough

### 6.2 Keyboard Shortcuts
Learn productivity shortcuts:
- `C` - Compose new email
- `R` - Reply to conversation
- `A` - Assign conversation
- `E` - Archive conversation
- `/` - Quick search
- `?` - Show all shortcuts

### 6.3 Best Practices
1. **Response Time**: Aim for < 2 hour first response
2. **Tagging**: Use consistent tags for categorization
3. **Internal Notes**: Add context for team members
4. **Templates**: Use for common responses
5. **Assignments**: Distribute workload evenly

## Step 7: Go Live Checklist ✓

Before fully launching with your team:

- [ ] All team members invited and onboarded
- [ ] Email forwarding tested and working
- [ ] Disposition board customized
- [ ] Key templates created
- [ ] Auto-responses configured
- [ ] Team trained on basics
- [ ] Test conversation created and resolved
- [ ] Notification settings confirmed
- [ ] Mobile app downloaded (if needed)

## Support Resources

### Help Center
Visit `help.collaborinbox.com` for:
- Video tutorials
- Feature guides
- Troubleshooting
- API documentation

### Live Support
- **Chat**: Available 9 AM - 6 PM EST
- **Email**: support@collaborinbox.com
- **Priority Support**: For Pro/Enterprise plans

### Community
- Join our Slack community
- Monthly webinars
- Best practices blog

## Advanced Features (After Initial Setup)

Once comfortable with basics, explore:

1. **Analytics Dashboard**
   - Response time metrics
   - Agent performance
   - Customer satisfaction
   - Volume trends

2. **Automation Rules**
   - Smart routing
   - SLA management
   - Escalation policies
   - Custom workflows

3. **API Integration**
   - Webhook notifications
   - Custom integrations
   - Bulk operations
   - Data export

4. **White Label Options**
   - Custom domain
   - Branded emails
   - Custom colors/logo
   - Remove CollaborInbox branding

## Security & Compliance

Your data is secure with:
- 256-bit SSL encryption
- Daily automated backups
- GDPR compliance tools
- Audit logs for all actions
- Two-factor authentication (recommended)
- IP allowlisting (optional)

## Billing Information

### Free Trial
- 14-day free trial
- No credit card required
- All features included
- Up to 5 team members

### Pricing Plans
After trial, choose from:
- **Starter**: $29/month (up to 3 agents)
- **Growth**: $79/month (up to 10 agents)
- **Pro**: $199/month (unlimited agents)
- **Enterprise**: Custom pricing

### Payment Methods
- Credit/Debit cards
- ACH bank transfer (US only)
- Wire transfer (Enterprise only)
- Annual billing (20% discount)

## Success Metrics

Track your success with CollaborInbox:

### Week 1 Goals
- [ ] 90% of emails routed correctly
- [ ] Average response time < 4 hours
- [ ] All agents actively using system

### Month 1 Goals
- [ ] Response time < 2 hours
- [ ] 95% customer satisfaction
- [ ] 50% reduction in email overlap
- [ ] Established workflow patterns

### Ongoing Optimization
- Regular team reviews
- Feature adoption tracking
- Customer feedback integration
- Process refinement

## Frequently Asked Questions

**Q: Can we use our own domain for emails?**
A: Yes, Enterprise plans support custom domains with full DKIM/SPF setup.

**Q: How many emails can we process?**
A: No hard limits. System scales automatically with your needs.

**Q: Can we migrate from another helpdesk?**
A: Yes, we provide migration assistance for common platforms.

**Q: Is there a mobile app?**
A: Yes, iOS and Android apps are available for all plans.

**Q: Can we export our data?**
A: Yes, full data export is available anytime in multiple formats.

## Welcome Aboard! 🚀

You're now ready to transform your email communication with CollaborInbox. Remember:
- Start simple, add complexity gradually
- Use the help resources available
- Gather team feedback regularly
- Optimize based on metrics

For any questions during onboarding, don't hesitate to reach out to our support team.

Happy collaborating!

---

*Last updated: December 2024*
*Version: 1.0*
