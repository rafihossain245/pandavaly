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
        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->string('po_no')->unique();
            $t->unsignedBigInteger('supplier_id');
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->enum('status',['draft','approved','partially_received','received','cancelled'])->default('draft');
            $t->date('po_date')->nullable(); $t->date('expected_date')->nullable();
            $t->decimal('subtotal',14,2)->default(0);
            $t->decimal('discount',14,2)->default(0);
            $t->decimal('tax',14,2)->default(0);
            $t->decimal('total',14,2)->default(0);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $t->foreign('warehouse_id')->references('id')->on('ware_houses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
