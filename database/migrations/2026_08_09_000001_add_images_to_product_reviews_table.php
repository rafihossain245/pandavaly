<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The product page review form lets a shopper attach photos of what they
     * received, so the paths need somewhere to live. JSON rather than a side
     * table: they are only ever read back as a whole list for one review.
     */
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->json('images')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
