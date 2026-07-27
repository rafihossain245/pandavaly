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
        Schema::create('stocks', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('warehouse_bin_id')->nullable();
            $t->unsignedBigInteger('qty_on_hand')->default(0);
            $t->unsignedBigInteger('qty_reserved')->default(0);
            $t->timestamps();
            $t->foreign('warehouse_id')->references('id')->on('ware_houses')->cascadeOnDelete();
            $t->foreign('warehouse_bin_id')->references('id')->on('ware_house_bins')->nullOnDelete();
            $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $t->unique(['warehouse_id','product_id','warehouse_bin_id'], 'uniq_stock_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
