<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('ticket_id');
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();

            // Null when the attachment belongs to the ticket's opening post.
            $table->unsignedInteger('ticket_message_id')->nullable();
            $table->foreign('ticket_message_id')->references('id')->on('ticket_messages')->cascadeOnDelete();

            $table->string('path');
            $table->string('name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
