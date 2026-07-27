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
        Schema::create('ware_house_bins', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id');
            $t->string('code'); // A-01-01
            $t->timestamps(); $t->softDeletes();
            $t->foreign('warehouse_id')->references('id')->on('ware_houses')->cascadeOnDelete();
            $t->unique(['warehouse_id','code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ware_house_bins');
    }
};
