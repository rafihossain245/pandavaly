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
        Schema::create('quotations', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->unsignedBigInteger('rfq_id')->nullable();
            $t->enum('status',['draft','issued','expired','won','lost'])->default('draft');
            $t->date('valid_until')->nullable();
            $t->decimal('subtotal',14,2)->default(0);
            $t->decimal('discount',14,2)->default(0);
            $t->decimal('tax',14,2)->default(0);
            $t->decimal('total',14,2)->default(0);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->foreign('rfq_id')->references('id')->on('rfqs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
