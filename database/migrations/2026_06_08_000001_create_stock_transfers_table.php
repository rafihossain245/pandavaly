<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $t) {
            $t->id();
            $t->string('transfer_no')->unique();
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('product_sku_id')->nullable();
            $t->unsignedBigInteger('from_warehouse_id');
            $t->unsignedBigInteger('to_warehouse_id');
            $t->decimal('qty', 14, 2);
            $t->text('note')->nullable();
            $t->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();

            $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->nullOnDelete();
            $t->foreign('from_warehouse_id')->references('id')->on('ware_houses')->cascadeOnDelete();
            $t->foreign('to_warehouse_id')->references('id')->on('ware_houses')->cascadeOnDelete();
            $t->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
