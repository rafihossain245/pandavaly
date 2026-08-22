<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The wording on the one-page funnel and its receipt, plus the share/search
 * description. All of it was typed into the blade templates, which meant the
 * shop had to ask a developer to change a heading or the cash-on-delivery
 * promise. Every column is nullable: blank falls back to the wording the page
 * shipped with (Setting::LANDING_COPY), so an empty field is never a hole.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('meta_description', 300)->nullable()->after('tagline');
            $table->string('landing_gallery_heading')->nullable()->after('announcement_enabled');
            $table->string('landing_gallery_subheading')->nullable()->after('landing_gallery_heading');
            $table->string('landing_order_heading')->nullable()->after('landing_gallery_subheading');
            $table->string('landing_cod_note')->nullable()->after('landing_order_heading');
            $table->string('landing_thankyou_heading')->nullable()->after('landing_cod_note');
            $table->string('landing_thankyou_note')->nullable()->after('landing_thankyou_heading');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'meta_description',
                'landing_gallery_heading',
                'landing_gallery_subheading',
                'landing_order_heading',
                'landing_cod_note',
                'landing_thankyou_heading',
                'landing_thankyou_note',
            ]);
        });
    }
};
