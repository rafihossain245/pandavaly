<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mobile app store links for the footer's "Download App On Mobile" block.
 * Both empty simply means the block is not rendered, so the footer stays
 * correct for a storefront with no published app.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('play_store_url')->nullable()->after('tiktok_url');
            $table->string('app_store_url')->nullable()->after('play_store_url');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['play_store_url', 'app_store_url']);
        });
    }
};
