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
        Schema::create('buyer_credit_amounts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->decimal('credit_limit',14,2)->default(0);
            $t->unsignedInteger('net_days')->default(0);
            $t->enum('state', ['enabled','frozen'])->default('enabled');
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_credit_amounts');
    }
};
