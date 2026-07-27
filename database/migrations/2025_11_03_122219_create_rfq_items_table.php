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
        Schema::create('rfq_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('rfq_id');
            $t->unsignedBigInteger('product_sku_id');
            $t->unsignedInteger('qty');
            $t->decimal('target_price',14,2)->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
            $t->foreign('product_sku_id')->references('id')->on('product_skus')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfq_items');
    }
};
