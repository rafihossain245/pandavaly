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
        Schema::create('product_attributes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_sku_id')->nullable();
            $t->unsignedBigInteger('attribute_id')->nullable();
            $t->unsignedBigInteger('attribute_value_id')->nullable();
            $t->string('value_text')->nullable(); // for text/number attrs
            $t->timestamps();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->cascadeOnDelete();
            $t->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
            $t->foreign('attribute_value_id')->references('id')->on('attribute_values')->nullOnDelete();
            $t->unique(['product_sku_id','attribute_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
