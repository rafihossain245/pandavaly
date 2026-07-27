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
        Schema::create('grns', function (Blueprint $t) {
            $t->id();
            $t->string('grn_no')->unique();
            $t->unsignedBigInteger('purchase_order_id')->nullable();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->enum('status',['draft','received','closed'])->default('draft');
            $t->dateTime('received_at')->nullable();
            $t->text('qc_notes')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            $t->foreign('warehouse_id')->references('id')->on('ware_houses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grns');
    }
};
