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
        Schema::create('payment_allocations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('payment_id');
            $t->unsignedBigInteger('invoice_id');
            $t->decimal('allocated_amount',14,2);
            $t->timestamps();
            $t->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
            $t->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $t->unique(['payment_id','invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
