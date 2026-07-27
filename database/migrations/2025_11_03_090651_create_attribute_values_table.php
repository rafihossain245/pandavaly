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
        Schema::create('attribute_values', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('attribute_id');
            $t->string('value');
            $t->unsignedInteger('position')->default(0);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('attribute_id')->references('id')->on('attributes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
