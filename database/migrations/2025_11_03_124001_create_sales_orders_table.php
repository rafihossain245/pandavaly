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
        Schema::create('sales_orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_no')->unique();
            $t->unsignedBigInteger('buyer_id');
            $t->unsignedBigInteger('quotation_id')->nullable();
            $t->enum('status',['pending','confirmed','packed','shipped','delivered','completed','cancelled'])->default('pending');
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->enum('payment_term',['cash','partial','credit'])->default('cash');
            $t->decimal('advance_paid',14,2)->default(0);
            $t->decimal('subtotal',14,2)->default(0);
            $t->decimal('discount',14,2)->default(0);
            $t->decimal('tax',14,2)->default(0);
            $t->decimal('total',14,2)->default(0);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->foreign('quotation_id')->references('id')->on('quotations')->nullOnDelete();
            $t->foreign('warehouse_id')->references('id')->on('ware_houses')->nullOnDelete();
            $t->index(['buyer_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
