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
        Schema::create('buyer_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('buyer_id');
            $t->string('type'); // NID, TradeLicense
            $t->string('number')->nullable();
            $t->date('expiry_date')->nullable();
            $t->unsignedBigInteger('media_id')->nullable(); // file linkage
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->foreign('media_id')->references('id')->on('media_files')->nullOnDelete();
            $t->index(['type','expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_documents');
    }
};
