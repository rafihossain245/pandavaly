<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A second logo for dark surfaces. `logo_path` is the primary mark and must
 * read on WHITE (settings preview, invoices, the Open Graph image); this one
 * is its light-on-dark counterpart for the magenta header band and the dark
 * footer. Null simply falls back to the primary logo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('logo_light_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('logo_light_path');
        });
    }
};
