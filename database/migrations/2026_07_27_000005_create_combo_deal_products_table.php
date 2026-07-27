<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combo_deal_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();

            $table->unique(['combo_deal_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combo_deal_products');
    }
};
