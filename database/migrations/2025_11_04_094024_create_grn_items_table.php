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
        Schema::create('grn_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('grn_id');
            $t->unsignedBigInteger('purchase_order_item_id')->nullable();
            $t->unsignedBigInteger('product_sku_id');
            $t->unsignedInteger('qty_received');
            $t->unsignedInteger('qty_rejected')->default(0);
            $t->text('qc_notes')->nullable();
            $t->timestamps();
            $t->foreign('grn_id')->references('id')->on('grns')->cascadeOnDelete();
            $t->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items')->nullOnDelete();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};
