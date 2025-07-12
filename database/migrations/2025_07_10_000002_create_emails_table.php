<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('email_account_id');
            $table->unsignedBigInteger('assigned_to')->nullable(); // user_id
            $table->unsignedBigInteger('disposition_id')->nullable();
            
            // Email metadata
            $table->string('message_id')->unique();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('subject');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->json('reply_to')->nullable();
            $table->json('headers')->nullable();
            
            // Status and tracking
            $table->enum('status', ['unread', 'read', 'replied', 'forwarded', 'archived', 'spam', 'trash'])->default('unread');
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('has_attachments')->default(false);
            $table->integer('attachment_count')->default(0);
            
            // Thread management
            $table->string('thread_id')->nullable();
            $table->string('in_reply_to')->nullable();
            $table->json('references')->nullable();
            
            // Timestamps
            $table->timestamp('received_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('email_account_id')->references('id')->on('email_accounts')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'received_at']);
            $table->index(['thread_id']);
            $table->index(['from_email']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
}
