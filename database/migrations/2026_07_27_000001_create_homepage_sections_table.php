<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // hero_slider, split_banner, category_strip, product_row, brand_strip, combo_deals, testimonials
            $table->string('title'); // internal admin label
            $table->string('heading')->nullable(); // frontend display heading
            $table->string('subheading')->nullable();
            $table->json('config')->nullable(); // per-type settings: product source, category_id, limit, columns...
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
