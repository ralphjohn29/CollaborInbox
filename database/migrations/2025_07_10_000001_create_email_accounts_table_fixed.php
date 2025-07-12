<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailAccountsTableFixed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('email_accounts')) {
            Schema::create('email_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('tenant_id')->nullable(); // Keep for backwards compatibility
                $table->string('email_prefix')->nullable(); // e.g., 'sales', 'support', 'info'
                $table->string('email_address')->unique(); // full email address
                $table->string('display_name')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('provider')->nullable(); // gmail, outlook, etc.
                
                // Email server settings
                $table->string('incoming_server_type')->default('imap'); // imap, pop3
                $table->string('incoming_server_host')->nullable();
                $table->integer('incoming_server_port')->nullable();
                $table->string('incoming_server_username')->nullable();
                $table->text('incoming_server_password')->nullable(); // encrypted
                $table->boolean('incoming_server_ssl')->default(true);
                
                $table->string('outgoing_server_type')->default('smtp');
                $table->string('outgoing_server_host')->nullable();
                $table->integer('outgoing_server_port')->nullable();
                $table->string('outgoing_server_username')->nullable();
                $table->text('outgoing_server_password')->nullable(); // encrypted
                $table->boolean('outgoing_server_ssl')->default(true);
                
                $table->timestamps();
                
                $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
                $table->index(['workspace_id', 'email_address']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_accounts');
    }
}
