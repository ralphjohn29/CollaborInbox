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
        Schema::create('mailbox_configurations', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('tenant_id')->constrained()->cascadeOnDelete(); // Tenant ID is implicit in tenant DB
            $table->string('email_address')->unique(); // Unique within the tenant
            $table->string('imap_server');
            $table->integer('port')->default(993);
            $table->string('encryption_type')->default('ssl'); // e.g., 'ssl', 'tls', 'none'
            $table->string('username');
            $table->text('encrypted_password'); // Store encrypted password
            $table->string('folder_to_monitor')->default('INBOX');
            $table->timestamp('last_sync_timestamp')->nullable();
            $table->string('status')->default('active'); // e.g., 'active', 'inactive', 'error'
            $table->text('last_error')->nullable(); // Store last connection/sync error message
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mailbox_configurations');
    }
}; 