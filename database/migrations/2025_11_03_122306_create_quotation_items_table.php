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
        Schema::create('quotation_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('quotation_id');
            $t->unsignedBigInteger('product_sku_id');
            $t->unsignedInteger('qty');
            $t->decimal('price',14,2);
            $t->decimal('line_total',14,2);
            $t->timestamps();
            $t->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
