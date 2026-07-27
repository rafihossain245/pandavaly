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
        Schema::create('attributes', function (Blueprint $t) {
            $t->id();
            $t->string('code'); // size, color, wattage
            $t->string('name');
            $t->enum('type',['text','number','select'])->default('select');
            $t->timestamps(); $t->softDeletes();
            $t->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
