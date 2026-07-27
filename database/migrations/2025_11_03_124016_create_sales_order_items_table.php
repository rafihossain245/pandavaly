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
        Schema::create('sales_order_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sales_order_id');
            $t->unsignedBigInteger('product_sku_id');
            $t->unsignedInteger('qty');
            $t->decimal('price',14,2);
            $t->decimal('line_total',14,2);
            $t->timestamps();
            $t->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
