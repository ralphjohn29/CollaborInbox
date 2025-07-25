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
        if (!Schema::hasTable('domains')) {
            Schema::create('domains', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->unique();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
}; 