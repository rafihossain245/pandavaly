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
        Schema::create('buyer_contacts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->string('name');
            $t->string('email')->nullable(); $t->string('phone')->nullable();
            $t->string('designation')->nullable();
            $t->boolean('is_primary')->default(false);
            $t->timestamps(); 
            $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_contacts');
    }
};
