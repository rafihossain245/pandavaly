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
        Schema::create('ticket_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('ticket_id');
            $t->unsignedBigInteger('user_id')->nullable(); // null = buyer
            $t->text('message');
            $t->unsignedBigInteger('media_id')->nullable();
            $t->timestamps();
            $t->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $t->foreign('media_id')->references('id')->on('media_files')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
