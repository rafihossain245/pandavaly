<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The original default of 5 was never a deliberate per-product
        // setting (no admin field existed to configure it), so reset
        // existing products to no minimum before admins start using it.
        DB::table('products')->where('moq', 5)->update(['moq' => 1]);

        DB::statement('ALTER TABLE products MODIFY COLUMN moq INT UNSIGNED NOT NULL DEFAULT 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY COLUMN moq INT UNSIGNED NOT NULL DEFAULT 5');
    }
};
