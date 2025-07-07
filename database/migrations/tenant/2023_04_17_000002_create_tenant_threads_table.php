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
        $this->createTableWithTenantId('threads', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('status')->default('new'); // new, assigned, closed, etc.
            $table->string('external_id')->nullable(); // For email thread IDs
            $table->foreignId('assigned_to_id')->nullable(); // Will be linked to users table
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes(); // For data retention
            
            // Create a unique constraint on external_id within a tenant
            $table->unique(['external_id', 'tenant_id'], 'threads_tenant_external_id_unique');
            
            // Add a foreign key that respects tenant boundaries
            $this->tenantForeignKey($table, 'assigned_to_id', 'users', 'id', 'set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
}; 