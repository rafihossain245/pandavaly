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
        Schema::create('product_prices', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('pricing_tier_id')->nullable(); // null = default wholesale
            $t->decimal('purchase_price',14,2)->default(0);
            $t->decimal('previous_price',14,2)->default(0);
            $t->decimal('selling_price',14,2)->default(0);
            $t->date('valid_from')->nullable();
            $t->date('valid_to')->nullable();
            $t->timestamps();
            $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $t->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->nullOnDelete();
            $t->unique(['product_id','pricing_tier_id','valid_from']);
            $t->index(['valid_from']);
            $t->index(['valid_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
