<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create workspaces table
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 8)->unique();
            $table->string('name');
            $table->string('email_alias')->unique()->nullable();
            $table->string('postmark_server_token')->nullable();
            $table->string('postmark_inbound_webhook_token')->nullable();
            $table->json('settings')->nullable();
            $table->json('email_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('subscription_status')->default('trial');
            $table->timestamps();
            
            $table->index('uid');
            $table->index('email_alias');
        });

        // Add workspace_id to users table
        if (!Schema::hasColumn('users', 'workspace_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
                $table->string('provider', 50)->default('local')->after('email');
                $table->string('provider_id')->nullable()->after('provider');
                $table->boolean('is_workspace_creator')->default(false);
                $table->timestamp('last_active_at')->nullable();
                
                $table->index('workspace_id');
                $table->index(['email', 'workspace_id']);
            });
        }

        // Create conversations table
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('uid', 12)->unique();
            $table->string('subject')->nullable();
            $table->string('status')->default('new'); // new, in_progress, waiting, resolved, spam
            $table->string('disposition')->default('new'); // customizable per workspace
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('customer_email');
            $table->string('customer_name')->nullable();
            $table->json('tags')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('response_count')->default(0);
            $table->integer('email_count')->default(0);
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            
            $table->index('workspace_id');
            $table->index('status');
            $table->index('assigned_to');
            $table->index('customer_email');
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'created_at']);
        });

        // Create emails table
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('conversation_id');
            $table->string('message_id')->unique();
            $table->string('in_reply_to')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->json('to_email');
            $table->json('cc_email')->nullable();
            $table->json('bcc_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->json('headers')->nullable();
            $table->json('attachments')->nullable();
            $table->decimal('spam_score', 3, 1)->nullable();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced'])->default('pending');
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->json('postmark_data')->nullable();
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index('workspace_id');
            $table->index('conversation_id');
            $table->index('message_id');
            $table->index('from_email');
            $table->index(['workspace_id', 'direction']);
            $table->index(['workspace_id', 'created_at']);
        });

        // Create audit_logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['workspace_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('action');
        });

        // Create workspace_invitations table
        Schema::create('workspace_invitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('email');
            $table->string('role')->default('agent');
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('invited_by');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('workspace_id');
            $table->index('email');
            $table->index('token');
        });

        // Create email_templates table
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('name');
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->string('category')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index('workspace_id');
            $table->index(['workspace_id', 'category']);
        });

        // Create workspace_stats table for daily aggregation
        Schema::create('workspace_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->date('date');
            $table->integer('conversations_created')->default(0);
            $table->integer('conversations_resolved')->default(0);
            $table->integer('emails_received')->default(0);
            $table->integer('emails_sent')->default(0);
            $table->integer('spam_emails')->default(0);
            $table->integer('avg_response_time')->default(0); // in minutes
            $table->integer('avg_resolution_time')->default(0); // in minutes
            $table->json('agent_stats')->nullable(); // per-agent breakdown
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            
            $table->unique(['workspace_id', 'date']);
            $table->index('date');
        });

        // Create contacts table
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->integer('conversation_count')->default(0);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            
            $table->unique(['workspace_id', 'email']);
            $table->index(['workspace_id', 'name']);
        });

        // Create automation_rules table
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions'); // e.g., subject contains, from email, etc.
            $table->json('actions'); // e.g., assign to, add tag, send template
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('times_triggered')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['workspace_id', 'is_active']);
        });

        // Update existing tables to add workspace_id if they exist
        $tablesToUpdate = [
            'mailbox_configurations',
            'mailbox_threads',
            'mailbox_messages',
            'agent_mailboxes',
            'snippets'
        ];

        foreach ($tablesToUpdate as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'workspace_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
                    $table->index('workspace_id');
                });
            }
        }

        // First remove foreign key constraints before dropping tables
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Check if foreign key exists before trying to drop it
                $dbName = DB::connection()->getDatabaseName();
                $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'tenant_id' AND REFERENCED_TABLE_NAME IS NOT NULL", [$dbName]);
                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign($foreignKey->CONSTRAINT_NAME);
                }
            });
        }

        // Drop tenant-related tables if they exist
        $tenantTables = [
            'domains',
            'tenant_users',
            'tenants' // Drop tenants table last
        ];

        foreach ($tenantTables as $table) {
            Schema::dropIfExists($table);
        }

        // Remove tenant_id from tables if it exists
        $tablesWithTenantId = [
            'users',
            'mailbox_configurations',
            'mailbox_threads',
            'mailbox_messages',
            'agent_mailboxes',
            'snippets'
        ];

        foreach ($tablesWithTenantId as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('workspace_stats');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('workspace_invitations');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('emails');
        Schema::dropIfExists('conversations');
        
        // Remove workspace_id from users
        if (Schema::hasColumn('users', 'workspace_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'workspace_id',
                    'provider',
                    'provider_id',
                    'is_workspace_creator',
                    'last_active_at'
                ]);
            });
        }
        
        // Drop workspaces table
        Schema::dropIfExists('workspaces');
    }
};
