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
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_sku_id');
            $t->unsignedBigInteger('from_warehouse_id')->nullable();
            $t->unsignedBigInteger('to_warehouse_id')->nullable();
            $t->unsignedBigInteger('qty');
            $t->enum('reason',['purchase_receipt','sale_dispatch','transfer','adjustment','return_in','return_out']);
            $t->string('ref_type')->nullable(); // PO, GRN, SO, INV
            $t->string('ref_id')->nullable();
            $t->timestamps();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
