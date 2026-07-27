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
        Schema::create('supplier_bank_accounts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('supplier_id');
            $t->string('bank_name')->nullable(); 
            $t->string('branch')->nullable();
            $t->string('account_name')->nullable(); 
            $t->string('account_no')->nullable();
            $t->string('swift')->nullable();
            $t->timestamps(); 
            $t->softDeletes();
            $t->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $t->unique(['supplier_id','account_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_bank_accounts');
    }
};
