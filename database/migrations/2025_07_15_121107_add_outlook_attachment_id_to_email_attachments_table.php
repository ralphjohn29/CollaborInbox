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
        Schema::table('email_attachments', function (Blueprint $table) {
            $table->string('outlook_attachment_id')->nullable()->after('storage_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_attachments', function (Blueprint $table) {
            $table->dropColumn('outlook_attachment_id');
        });
    }
};
