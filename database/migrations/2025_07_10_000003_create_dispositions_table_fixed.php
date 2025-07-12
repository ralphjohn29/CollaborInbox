<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDispositionsTableFixed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('dispositions')) {
            Schema::create('dispositions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('tenant_id')->nullable(); // Keep for backwards compatibility
                $table->string('name');
                $table->string('slug');
                $table->string('color')->default('#6B7280'); // hex color
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
                $table->unique(['workspace_id', 'slug']);
                $table->index(['workspace_id', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dispositions');
    }
}
