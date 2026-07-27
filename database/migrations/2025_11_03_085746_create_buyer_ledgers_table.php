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
        Schema::create('buyer_ledgers', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->dateTime('entry_date');
            $t->enum('type', ['invoice','payment','credit_note','debit_note','adjustment']);
            $t->string('ref_no')->nullable();
            $t->decimal('debit',14,2)->default(0);
            $t->decimal('credit',14,2)->default(0);
            $t->decimal('balance',14,2)->default(0);
            $t->json('meta')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->index(['buyer_id','entry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_ledgers');
    }
};
