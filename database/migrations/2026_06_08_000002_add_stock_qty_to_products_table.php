<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $t) {
            // Denormalized total across all warehouses — updated by stock movements
            $t->unsignedInteger('stock_qty')->default(0)->after('thumbnail');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->dropColumn('stock_qty');
        });
    }
};
