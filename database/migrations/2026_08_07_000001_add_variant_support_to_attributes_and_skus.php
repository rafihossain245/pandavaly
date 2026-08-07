<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the skeleton attribute/SKU tables into a usable variant system:
 * attributes gain a display style (colour swatch vs pill vs dropdown) so the
 * storefront can render them the way shoppers expect, and every SKU gains its
 * own code, price, stock and image instead of borrowing the parent product's.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $t) {
            // How the storefront renders this attribute's values: pill | swatch | dropdown.
            $t->string('display_type', 20)->default('pill')->after('type');
            $t->unsignedInteger('position')->default(0)->after('display_type');
        });

        Schema::table('attribute_values', function (Blueprint $t) {
            // Hex colour for swatch attributes (e.g. #ff0000). Null for everything else.
            $t->string('color_code', 9)->nullable()->after('value');
        });

        Schema::table('product_skus', function (Blueprint $t) {
            $t->string('sku')->nullable()->after('product_id');
            $t->decimal('price', 14, 2)->nullable()->after('barcode');
            $t->decimal('compare_at_price', 14, 2)->nullable()->after('price');
            $t->integer('stock_qty')->default(0)->after('cost');
            $t->string('image')->nullable()->after('weight');
            $t->unsignedInteger('position')->default(0)->after('image');
        });

        // `mrp` was being read as the selling price everywhere; carry it over to the
        // explicitly-named column before dropping it.
        DB::table('product_skus')->whereNotNull('mrp')->update(['price' => DB::raw('mrp')]);

        Schema::table('product_skus', function (Blueprint $t) {
            $t->dropColumn('mrp');
        });
    }

    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $t) {
            $t->decimal('mrp', 14, 2)->nullable()->after('barcode');
        });

        DB::table('product_skus')->whereNotNull('price')->update(['mrp' => DB::raw('price')]);

        Schema::table('product_skus', function (Blueprint $t) {
            $t->dropColumn(['sku', 'price', 'compare_at_price', 'stock_qty', 'image', 'position']);
        });

        Schema::table('attribute_values', function (Blueprint $t) {
            $t->dropColumn('color_code');
        });

        Schema::table('attributes', function (Blueprint $t) {
            $t->dropColumn(['display_type', 'position']);
        });
    }
};
