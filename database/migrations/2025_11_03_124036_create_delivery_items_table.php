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
        Schema::create('delivery_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('delivery_id');
            $t->unsignedBigInteger('sales_order_item_id');
            $t->unsignedInteger('qty');
            $t->timestamps();
            $t->foreign('delivery_id')->references('id')->on('deliveries')->cascadeOnDelete();
            $t->foreign('sales_order_item_id')->references('id')->on('sales_order_items')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_items');
    }
};
