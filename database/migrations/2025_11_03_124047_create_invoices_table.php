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
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_no')->unique();
            $t->unsignedBigInteger('buyer_id');
            $t->unsignedBigInteger('sales_order_id')->nullable();
            $t->enum('status',['unpaid','partial','paid','void'])->default('unpaid');
            $t->date('invoice_date')->nullable();
            $t->date('due_date')->nullable();
            $t->decimal('subtotal',14,2)->default(0);
            $t->decimal('discount',14,2)->default(0);
            $t->decimal('tax',14,2)->default(0);
            $t->decimal('total',14,2)->default(0);
            $t->decimal('balance',14,2)->default(0);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->foreign('sales_order_id')->references('id')->on('sales_orders')->nullOnDelete();
            $t->index(['buyer_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
