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
        // Update the enum to include 'sending' status
        DB::statement("ALTER TABLE email_replies MODIFY COLUMN status ENUM('draft', 'sending', 'sent', 'failed') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum without 'sending'
        DB::statement("ALTER TABLE email_replies MODIFY COLUMN status ENUM('draft', 'sent', 'failed') NOT NULL DEFAULT 'draft'");
    }
};
