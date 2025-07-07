<?php

use App\Database\Migrations\TenantMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends TenantMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create Messages Table
        $this->createTableWithTenantId('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id'); // Will be linked to threads table
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->text('to')->nullable(); // Serialized array of recipients
            $table->text('cc')->nullable(); // Serialized array of CC recipients
            $table->text('bcc')->nullable(); // Serialized array of BCC recipients
            $table->text('subject');
            $table->text('body_html')->nullable();
            $table->text('body_text')->nullable();
            $table->string('external_id')->nullable(); // For email message IDs
            $table->boolean('is_outbound')->default(false); // Indicates if the message was sent by an agent
            $table->foreignId('author_id')->nullable(); // Will be linked to users table for outbound messages
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // For data retention
            
            // Create a unique constraint on external_id within a tenant
            $table->unique(['external_id', 'tenant_id'], 'messages_tenant_external_id_unique');
            
            // Add foreign keys that respect tenant boundaries
            $this->tenantForeignKey($table, 'thread_id', 'threads', 'id', 'cascade');
            $this->tenantForeignKey($table, 'author_id', 'users', 'id', 'set null');
        });

        // Create Attachments Table
        $this->createTableWithTenantId('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id'); // Will be linked to messages table
            $table->string('filename');
            $table->string('mime_type');
            $table->integer('size'); // In bytes
            $table->string('path');
            $table->timestamps();
            
            // Add foreign key that respects tenant boundaries
            $this->tenantForeignKey($table, 'message_id', 'messages', 'id', 'cascade');
        });

        // Create Notes Table
        $this->createTableWithTenantId('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id'); // Will be linked to threads table
            $table->foreignId('user_id'); // Will be linked to users table
            $table->text('content');
            $table->boolean('is_private')->default(false);
            $table->timestamps();
            $table->softDeletes(); // For data retention
            
            // Add foreign keys that respect tenant boundaries
            $this->tenantForeignKey($table, 'thread_id', 'threads', 'id', 'cascade');
            $this->tenantForeignKey($table, 'user_id', 'users', 'id', 'cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('messages');
    }
}; 