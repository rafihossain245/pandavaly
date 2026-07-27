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
        Schema::create('buyer_branches', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->unsignedBigInteger('buyer_id');
            $t->string('name');
            $t->string('code')->nullable();
            $t->boolean('is_default')->default(false);
            $t->timestamps(); $t->softDeletes();
            $t->foreign('buyer_id')->references('id')->on('buyers')->cascadeOnDelete();
            $t->unique(['buyer_id','code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_branches');
    }
};
