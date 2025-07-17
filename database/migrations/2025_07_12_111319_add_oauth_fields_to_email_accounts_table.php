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
        Schema::table('email_accounts', function (Blueprint $table) {
            // Add OAuth fields if they don't exist
            if (!Schema::hasColumn('email_accounts', 'oauth_access_token')) {
                $table->text('oauth_access_token')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('email_accounts', 'oauth_refresh_token')) {
                $table->text('oauth_refresh_token')->nullable()->after('oauth_access_token');
            }
            if (!Schema::hasColumn('email_accounts', 'from_name')) {
                $table->string('from_name')->nullable()->after('email_address');
            }
            if (!Schema::hasColumn('email_accounts', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('workspace_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            // Change encryption columns to string type if needed
            if (!Schema::hasColumn('email_accounts', 'incoming_server_encryption')) {
                $table->string('incoming_server_encryption', 10)->nullable()->after('incoming_server_port');
            }
            if (!Schema::hasColumn('email_accounts', 'outgoing_server_encryption')) {
                $table->string('outgoing_server_encryption', 10)->nullable()->after('outgoing_server_port');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_accounts', function (Blueprint $table) {
            $table->dropColumn(['oauth_access_token', 'oauth_refresh_token', 'from_name']);
            if (Schema::hasColumn('email_accounts', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('email_accounts', 'incoming_server_encryption')) {
                $table->dropColumn('incoming_server_encryption');
            }
            if (Schema::hasColumn('email_accounts', 'outgoing_server_encryption')) {
                $table->dropColumn('outgoing_server_encryption');
            }
        });
    }
};
