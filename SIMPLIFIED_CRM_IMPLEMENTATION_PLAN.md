# Simplified CollaborInbox CRM Implementation Plan

## Overview
Transform CollaborInbox into a streamlined HubSpot-style shared email CRM with single-database workspace architecture, removing multi-tenancy complexity while adding powerful collaborative features.

## Core Architecture Changes

### 1. Remove Multi-Tenancy
- Remove `stancl/tenancy` package
- Convert to single database with `workspace_id` scoping
- Simplify routing structure
- Remove subdomain-based isolation

### 2. Workspace-Based Architecture
```
Users → belong to → Workspaces → have → Emails/Conversations
                              → have → Agents/Team Members
                              → have → Settings/Configurations
```

## Implementation Phases

### Phase 1: Infrastructure Refactoring (Days 1-3)

#### 1.1 Remove Tenancy Package
```bash
composer remove stancl/tenancy
```

#### 1.2 Database Migration
- Add `workspace_id` to all relevant tables
- Create workspaces table
- Update foreign key relationships

#### 1.3 Authentication System
- Implement public signup (email/password)
- Add Google OAuth integration
- Add Microsoft OAuth integration
- Auto-create workspace on first user registration

### Phase 2: Email Infrastructure (Days 4-6)

#### 2.1 Postmark Integration
- Setup Postmark API integration
- Implement inbound email handling via webhooks
- Configure DKIM for each workspace
- Auto-provision alias: `sales+{workspace_uid}@collaborinbox.com`

#### 2.2 Laravel Mailbox Setup
```bash
composer require beyondcode/laravel-mailbox
```
- Configure inbound email processing
- Parse and store emails in database
- Handle attachments and inline images

#### 2.3 Outbound Email Queue
- Setup Laravel Horizon for queue management
- Implement per-workspace SMTP settings
- Add email scheduling and retry logic

### Phase 3: Core Features (Days 7-10)

#### 3.1 Vue 3 Disposition Board
- Drag-and-drop interface (using Vue Draggable)
- Customizable columns (New, In Progress, Resolved, etc.)
- Real-time updates via WebSockets
- Quick actions and bulk operations

#### 3.2 Agent Management
- Spatie Laravel Permission integration
- Roles: Admin, Agent, Viewer
- Invitation system with email verification
- Activity tracking and presence indicators

#### 3.3 Search Integration
- Meilisearch full-text search setup
- Index emails, contacts, and conversations
- Advanced filters and saved searches
- Quick search with keyboard shortcuts

### Phase 4: Automation & Compliance (Days 11-12)

#### 4.1 Scheduled Jobs
- Daily stats aggregation (Laravel Scheduler)
- Spam auto-purge (score > 5)
- Email retention policies
- Performance metrics calculation

#### 4.2 Audit & Compliance
- Immutable audit logs for GDPR
- Data export functionality
- Right to erasure implementation
- Activity timeline for each conversation

## Technical Stack

### Backend
- Laravel 10.x (latest)
- MySQL 8.0
- Redis for caching/queues
- Horizon for queue management

### Frontend
- Vue 3 with Composition API
- Tailwind CSS for styling
- Inertia.js for SPA experience
- WebSockets for real-time updates

### Services
- Postmark for email infrastructure
- Meilisearch for search
- AWS S3 for file storage (optional)

## Database Schema

### Core Tables

```sql
-- Workspaces
CREATE TABLE workspaces (
    id BIGINT UNSIGNED PRIMARY KEY,
    uid VARCHAR(255) UNIQUE, -- for email alias
    name VARCHAR(255),
    email_alias VARCHAR(255), -- sales+uid@collaborinbox.com
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Users
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY,
    workspace_id BIGINT UNSIGNED,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    provider VARCHAR(50), -- local, google, microsoft
    provider_id VARCHAR(255),
    email_verified_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id)
);

-- Conversations
CREATE TABLE conversations (
    id BIGINT UNSIGNED PRIMARY KEY,
    workspace_id BIGINT UNSIGNED,
    subject VARCHAR(255),
    status VARCHAR(50), -- new, in_progress, resolved, spam
    disposition VARCHAR(50), -- customizable per workspace
    assigned_to BIGINT UNSIGNED,
    priority ENUM('low', 'normal', 'high', 'urgent'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Emails
CREATE TABLE emails (
    id BIGINT UNSIGNED PRIMARY KEY,
    workspace_id BIGINT UNSIGNED,
    conversation_id BIGINT UNSIGNED,
    message_id VARCHAR(255),
    from_email VARCHAR(255),
    from_name VARCHAR(255),
    to_email TEXT,
    cc_email TEXT,
    subject VARCHAR(255),
    body_html LONGTEXT,
    body_text LONGTEXT,
    spam_score DECIMAL(3,1),
    direction ENUM('inbound', 'outbound'),
    sent_at TIMESTAMP,
    created_at TIMESTAMP,
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id)
);

-- Audit Logs
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY,
    workspace_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
    action VARCHAR(255),
    auditable_type VARCHAR(255),
    auditable_id BIGINT UNSIGNED,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP,
    INDEX idx_workspace_created (workspace_id, created_at),
    FOREIGN KEY (workspace_id) REFERENCES workspaces(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Implementation Details

### 1. Public Signup Flow

```php
// routes/web.php
Route::get('/signup', [SignupController::class, 'show'])->name('signup');
Route::post('/signup', [SignupController::class, 'store']);

// SignupController
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'company_name' => 'required|string|max:255',
    ]);

    DB::transaction(function () use ($validated) {
        // Create workspace
        $workspace = Workspace::create([
            'uid' => Str::random(8),
            'name' => $validated['company_name'],
            'email_alias' => 'sales+' . Str::random(8) . '@collaborinbox.com',
        ]);

        // Create admin user
        $user = User::create([
            'workspace_id' => $workspace->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign admin role
        $user->assignRole('admin');

        // Setup Postmark inbound
        SetupPostmarkInbound::dispatch($workspace);

        // Send welcome email
        $user->notify(new WelcomeNotification($workspace));

        // Auto-login
        Auth::login($user);
    });

    return redirect()->route('dashboard');
}
```

### 2. Postmark Webhook Handler

```php
// Using Laravel Mailbox
Mailbox::from('{token}@collaborinbox.com', function (InboundEmail $email, $token) {
    $workspace = Workspace::where('uid', $token)->firstOrFail();
    
    ProcessInboundEmail::dispatch($email, $workspace);
});
```

### 3. Vue 3 Disposition Board Component

```vue
<template>
  <div class="disposition-board">
    <div class="columns-container">
      <div 
        v-for="column in columns" 
        :key="column.id"
        class="column"
      >
        <h3>{{ column.name }} ({{ column.items.length }})</h3>
        <draggable 
          v-model="column.items"
          group="conversations"
          @change="handleMove"
          item-key="id"
        >
          <template #item="{element}">
            <ConversationCard 
              :conversation="element"
              @click="openConversation(element)"
            />
          </template>
        </draggable>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import draggable from 'vuedraggable'
import { useConversations } from '@/composables/useConversations'

const columns = ref([
  { id: 'new', name: 'New', items: [] },
  { id: 'in_progress', name: 'In Progress', items: [] },
  { id: 'waiting', name: 'Waiting', items: [] },
  { id: 'resolved', name: 'Resolved', items: [] }
])

const { fetchConversations, updateDisposition } = useConversations()

onMounted(async () => {
  const conversations = await fetchConversations()
  // Organize conversations into columns
  conversations.forEach(conv => {
    const column = columns.value.find(c => c.id === conv.disposition)
    if (column) column.items.push(conv)
  })
})

const handleMove = async (evt) => {
  if (evt.added) {
    const conversation = evt.added.element
    const newDisposition = columns.value.find(c => 
      c.items.includes(conversation)
    ).id
    
    await updateDisposition(conversation.id, newDisposition)
  }
}
</script>
```

### 4. Meilisearch Integration

```php
// config/scout.php
'meilisearch' => [
    'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
    'key' => env('MEILISEARCH_KEY', null),
],

// Models/Email.php
use Laravel\Scout\Searchable;

class Email extends Model
{
    use Searchable;

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'from_email' => $this->from_email,
            'from_name' => $this->from_name,
            'body_text' => $this->body_text,
            'workspace_id' => $this->workspace_id,
            'created_at' => $this->created_at->timestamp,
        ];
    }
}
```

### 5. Daily Jobs

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new AggregateWorkspaceStats)->daily();
    $schedule->job(new PurgeSpamEmails)->daily();
    $schedule->job(new CleanupOldAuditLogs)->weekly();
}

// Jobs/PurgeSpamEmails.php
public function handle()
{
    Email::where('spam_score', '>', 5)
        ->where('created_at', '<', now()->subDays(7))
        ->chunkById(100, function ($emails) {
            foreach ($emails as $email) {
                // Log deletion for audit
                AuditLog::create([
                    'workspace_id' => $email->workspace_id,
                    'action' => 'auto_delete_spam',
                    'auditable_type' => Email::class,
                    'auditable_id' => $email->id,
                    'old_values' => $email->toArray(),
                ]);
                
                $email->delete();
            }
        });
}
```

## Security Considerations

1. **API Rate Limiting**
   - Implement rate limiting per workspace
   - Throttle authentication attempts

2. **Data Isolation**
   - Global scope for workspace_id on all models
   - Middleware to ensure workspace context

3. **GDPR Compliance**
   - Data export API
   - Right to erasure
   - Consent management
   - Audit trail preservation

## Performance Optimizations

1. **Database Indexes**
   - workspace_id on all tables
   - email addresses for quick lookups
   - created_at for time-based queries

2. **Caching Strategy**
   - Redis for session management
   - Cache workspace settings
   - Cache user permissions

3. **Queue Management**
   - Separate queues for email processing
   - Priority queues for user actions
   - Failed job handling

## Monitoring & Analytics

1. **Key Metrics**
   - Emails processed per minute
   - Average response time
   - User engagement rates
   - Storage usage per workspace

2. **Alerts**
   - High spam rates
   - Queue backlogs
   - Failed email deliveries
   - Unusual activity patterns

## Cost Estimation

### Monthly Costs (per 1000 users)
- **Postmark**: $100 (100k emails)
- **Meilisearch Cloud**: $50 (starter)
- **Redis Cloud**: $30
- **Hosting (DigitalOcean)**: $100
- **Total**: ~$280/month

## Timeline

- **Week 1**: Infrastructure refactoring and auth system
- **Week 2**: Email infrastructure and processing
- **Week 3**: Vue 3 UI and disposition board
- **Week 4**: Search, automation, and compliance
- **Week 5**: Testing, optimization, and deployment

## Next Steps

1. Review and approve implementation plan
2. Set up development environment
3. Begin Phase 1 implementation
4. Create detailed API documentation
5. Plan beta testing with select users
