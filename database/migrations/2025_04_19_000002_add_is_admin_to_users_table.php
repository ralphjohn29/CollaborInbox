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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false)->after('is_active');
            }
        });
        
        // Set users with 'admin' role to have is_admin=true
        if (Schema::hasColumn('users', 'is_admin') && Schema::hasTable('roles')) {
            $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
            
            if ($adminRoleId) {
                DB::table('users')
                    ->where('role_id', $adminRoleId)
                    ->update(['is_admin' => true]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_admin')) {
                $table->dropColumn('is_admin');
            }
        });
    }
}; 