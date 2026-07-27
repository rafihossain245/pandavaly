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
        Schema::create('supplier_invoices', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_no');
            $t->unsignedBigInteger('supplier_id');
            $t->unsignedBigInteger('purchase_order_id')->nullable();
            $t->enum('status',['unpaid','partial','paid','disputed'])->default('unpaid');
            $t->date('invoice_date')->nullable();
            $t->decimal('subtotal',14,2)->default(0);
            $t->decimal('discount',14,2)->default(0);
            $t->decimal('tax',14,2)->default(0);
            $t->decimal('total',14,2)->default(0);
            $t->decimal('balance',14,2)->default(0);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $t->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            $t->unique(['supplier_id','invoice_no']); // avoid duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
    }
};
