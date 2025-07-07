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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('message_id')->index();
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('path');
            $table->string('content_id')->nullable();
            $table->string('content_type');
            $table->unsignedBigInteger('size');
            $table->boolean('is_inline')->default(false);
            $table->boolean('can_preview')->default(false);
            $table->string('preview_path')->nullable();
            $table->timestamps();
            
            // Foreign key constraints - commented out until Message model exists
            // $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
}; 