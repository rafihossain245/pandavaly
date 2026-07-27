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
        Schema::create('product_media', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->unsignedBigInteger('media_id');
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
            $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $t->foreign('media_id')->references('id')->on('media_files')->cascadeOnDelete();
            $t->unique(['product_id','media_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};
