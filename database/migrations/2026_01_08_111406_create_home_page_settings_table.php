<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('home_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('home_banner_one')->nullable();
            $table->string('home_banner_two')->nullable();
            $table->string('featured_title_one')->nullable();
            $table->string('featured_description_one')->nullable();
            $table->string('featured_icon_one')->nullable();
            $table->string('featured_title_two')->nullable();
            $table->string('featured_description_two')->nullable();
            $table->string('featured_icon_two')->nullable();
            $table->string('featured_title_three')->nullable();
            $table->string('featured_description_three')->nullable();
            $table->string('featured_icon_three')->nullable();
            $table->string('featured_title_four')->nullable();
            $table->string('featured_description_four')->nullable();
            $table->string('featured_icon_four')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_page_settings');
    }
};
