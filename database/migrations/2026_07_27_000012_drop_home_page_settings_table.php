<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Superseded by homepage_sections/banners — the old fixed-field feature
     * (single banner-one/two + 4 hardcoded featured icons, insert-only bug) is removed.
     */
    public function up(): void
    {
        Schema::dropIfExists('home_page_settings');
    }

    public function down(): void
    {
        Schema::create('home_page_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
