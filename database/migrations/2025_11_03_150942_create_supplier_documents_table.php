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
        Schema::create('supplier_documents', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('supplier_id');
            $t->string('type'); 
            $t->string('number')->nullable();
            $t->date('expiry_date')->nullable();
            $t->unsignedBigInteger('media_id')->nullable();
            $t->timestamps(); 
            $t->softDeletes();
            $t->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $t->foreign('media_id')->references('id')->on('media_files')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_documents');
    }
};
