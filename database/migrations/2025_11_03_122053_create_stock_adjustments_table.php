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
        Schema::create('stock_adjustments', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('product_sku_id');
            $t->bigInteger('delta_qty');
            $t->text('note')->nullable();
            $t->unsignedBigInteger('approved_by')->nullable();
            $t->timestamps();
            $t->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $t->foreign('warehouse_id')->references('id')->on('ware_houses')->cascadeOnDelete();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
