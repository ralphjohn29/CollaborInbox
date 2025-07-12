<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('emails', 'is_starred')) {
                $table->boolean('is_starred')->default(false)->after('status');
            }
            
            if (!Schema::hasColumn('emails', 'is_important')) {
                $table->boolean('is_important')->default(false)->after('is_starred');
            }
            
            if (!Schema::hasColumn('emails', 'has_attachments')) {
                $table->boolean('has_attachments')->default(false)->after('is_important');
            }
            
            if (!Schema::hasColumn('emails', 'attachment_count')) {
                $table->integer('attachment_count')->default(0)->after('has_attachments');
            }
            
            if (!Schema::hasColumn('emails', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('conversation_id');
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('emails', 'disposition_id')) {
                $table->unsignedBigInteger('disposition_id')->nullable()->after('assigned_to');
                $table->foreign('disposition_id')->references('id')->on('dispositions')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('emails', 'thread_id')) {
                $table->string('thread_id')->nullable()->after('message_id');
                $table->index('thread_id');
            }
            
            if (!Schema::hasColumn('emails', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('opened_at');
            }
            
            if (!Schema::hasColumn('emails', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('sent_at');
            }
            
            // Add workspace_id column if it doesn't exist
            if (!Schema::hasColumn('emails', 'workspace_id')) {
                $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
                $table->index('workspace_id');
            }
            
            // Add indexes for performance (check if they exist first)
            if (!$this->indexExists('emails', 'emails_is_starred_index')) {
                $table->index('is_starred');
            }
            if (!$this->indexExists('emails', 'emails_assigned_to_index')) {
                $table->index('assigned_to');
            }
            if (!$this->indexExists('emails', 'emails_workspace_id_status_index')) {
                $table->index(['workspace_id', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['is_starred']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['workspace_id', 'status']);
            
            // Drop foreign keys
            if (Schema::hasColumn('emails', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
            }
            if (Schema::hasColumn('emails', 'disposition_id')) {
                $table->dropForeign(['disposition_id']);
            }
            
            // Drop columns
            $table->dropColumn([
                'is_starred',
                'is_important',
                'has_attachments',
                'attachment_count',
                'assigned_to',
                'disposition_id',
                'thread_id',
                'read_at',
                'received_at',
                'workspace_id'
            ]);
        });
    }
    
    /**
     * Check if an index exists on a table
     */
    private function indexExists($table, $indexName)
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }
};
