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
        Schema::create('rfqs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->enum('status',['new','quoted','cancelled'])->default('new');
            $t->text('notes')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
