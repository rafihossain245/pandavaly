<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marketing pixel IDs. Only the IDs are stored, never a pasted snippet — the
 * storefront builds the official loader itself, so a mistyped ID can never
 * inject arbitrary script into every page.
 *
 * An empty column means that platform ships no JavaScript at all.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('facebook_pixel_id', 32)->nullable()->after('tiktok_url');
            $table->string('ga4_measurement_id', 32)->nullable()->after('facebook_pixel_id');
            $table->string('gtm_container_id', 32)->nullable()->after('ga4_measurement_id');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_pixel_id',
                'ga4_measurement_id',
                'gtm_container_id',
            ]);
        });
    }
};
