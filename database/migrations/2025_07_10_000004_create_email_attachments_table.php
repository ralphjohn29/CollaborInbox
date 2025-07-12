<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_id');
            $table->string('filename');
            $table->string('mime_type');
            $table->bigInteger('size'); // in bytes
            $table->string('content_id')->nullable(); // for inline attachments
            $table->string('storage_path');
            $table->timestamps();
            
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->index('email_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_attachments');
    }
}
