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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
        });
        
        // Update existing tenant records to have a status based on is_active
        if (Schema::hasColumn('tenants', 'is_active') && Schema::hasColumn('tenants', 'status')) {
            $tenants = DB::table('tenants')->get();
            foreach ($tenants as $tenant) {
                $status = $tenant->is_active ? 'active' : 'inactive';
                DB::table('tenants')
                    ->where('id', $tenant->id)
                    ->update(['status' => $status]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}; 