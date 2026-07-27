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
        Schema::create('credit_levels', function (Blueprint $t) {
            $t->id();
            $t->string('code'); // L1/L2/L3
            $t->decimal('credit_limit', 14,2)->default(0);
            $t->unsignedInteger('net_days')->default(0); // 0 = cash
            $t->timestamps(); 
            $t->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_levels');
    }
};
