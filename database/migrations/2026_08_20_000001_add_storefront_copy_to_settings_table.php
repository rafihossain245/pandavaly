<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Storefront copy that was previously hardcoded in the blade templates: the
 * footer tagline and the top announcement bar. Both are shop-specific wording
 * (the tagline still described a grocery store after the catalogue changed to
 * home textiles), so they belong to the shop owner, not the templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('tagline')->nullable()->after('title');
            $table->string('announcement')->nullable()->after('tagline');
            $table->boolean('announcement_enabled')->default(false)->after('announcement');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'announcement', 'announcement_enabled']);
        });
    }
};
