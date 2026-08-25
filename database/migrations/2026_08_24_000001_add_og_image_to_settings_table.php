<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A dedicated social share image.
 *
 * The share card previously fell back to the logo, which is the wrong shape
 * (share cards are ~1.91:1, a logo is small and square-ish) and, for a shop
 * whose logo is light artwork made for a coloured header, renders as a nearly
 * blank tile on Facebook and WhatsApp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('og_image_path')->nullable()->after('favicon_path');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('og_image_path');
        });
    }
};
