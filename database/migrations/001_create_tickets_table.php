<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title');
            $table->string('category');
            $table->string('priority');
            $table->string('status');
            $table->text('description')->nullable();

            // Nullable so tickets can be server-independent.
            $table->unsignedInteger('server_id')->nullable();
            $table->foreign('server_id')->references('id')->on('servers')->nullOnDelete();

            $table->unsignedInteger('author_id')->nullable();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();

            $table->unsignedInteger('assigned_user_id')->nullable();
            $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();

            $table->timestamp('last_reply_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
