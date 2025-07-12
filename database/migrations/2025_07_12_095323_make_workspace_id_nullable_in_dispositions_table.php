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
        Schema::table('dispositions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['workspace_id']);
            
            // Make workspace_id nullable
            $table->unsignedBigInteger('workspace_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispositions', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['workspace_id']);
            
            // Make workspace_id non-nullable again
            $table->unsignedBigInteger('workspace_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
        });
    }
};
