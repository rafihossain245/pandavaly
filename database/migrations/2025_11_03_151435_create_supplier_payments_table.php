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
        Schema::create('supplier_payments', function (Blueprint $t) {
            $t->id();
            $t->string('payment_no')->unique();
            $t->unsignedBigInteger('supplier_id');
            $t->enum('method',['bank','lc','tt','cash'])->default('bank');
            $t->string('reference')->nullable();
            $t->date('payment_date')->nullable();
            $t->decimal('amount',14,2);
            $t->json('meta')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
