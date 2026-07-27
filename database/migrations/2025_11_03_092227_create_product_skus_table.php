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
        Schema::create('product_skus', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->string('barcode')->nullable();
            $t->decimal('mrp',14,2)->nullable(); // maximum retail price
            $t->decimal('cost',14,2)->nullable();
            $t->decimal('weight',10,3)->nullable();
            $t->json('options')->nullable(); // {"size":"8x4", "watt":"12W"}
            $t->boolean('is_active')->default(true);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $t->index(['product_id','is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_skus');
    }
};
