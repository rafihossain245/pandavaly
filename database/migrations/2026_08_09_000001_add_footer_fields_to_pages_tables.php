<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the stub pages CMS into the thing that drives the storefront footer.
 *
 * A page category is a footer column; the pages inside it are that column's
 * links, in `position` order. `link_url` lets a link point at an existing route
 * (e.g. Order Tracking) instead of rendering CMS content of its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_categories', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('name');
        });

        Schema::table('pages', function (Blueprint $table) {
            // When set, the footer links here instead of /page/{slug}. Keeps app
            // routes like /track-order inside an otherwise CMS-driven column.
            $table->string('link_url')->nullable()->after('content');
            $table->unsignedInteger('position')->default(0)->after('link_url');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['link_url', 'position']);
        });

        Schema::table('page_categories', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
