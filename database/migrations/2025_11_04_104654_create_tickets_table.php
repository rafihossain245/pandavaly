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
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id')->nullable();
            $t->unsignedBigInteger('invoice_id')->nullable();
            $t->enum('type',['support','warranty','rma'])->default('support');
            $t->enum('status',['open','pending','resolved','closed'])->default('open');
            $t->string('subject'); $t->text('description')->nullable();
            $t->timestamps(); 
            $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->nullOnDelete();
            $t->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
