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
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->string('payment_no')->unique();
            $t->unsignedBigInteger('buyer_id');
            $t->enum('method',['cash','bank','gateway'])->default('cash');
            $t->string('reference')->nullable(); // bank ref / trx id
            $t->date('payment_date')->nullable();
            $t->decimal('amount',14,2);
            $t->json('meta')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->index(['buyer_id','payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
