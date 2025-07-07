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
        $this->createTableWithTenantId('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, number, json, etc.
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // Whether this is a system setting
            $table->timestamps();
            
            // Create a unique constraint on key within a tenant
            $table->unique(['key', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
}; 