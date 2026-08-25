<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional sounds: a short welcome cue on arrival and a confirmation cue when
 * an order is placed. Both nullable — silence is the default, and browsers
 * block unsolicited audio anyway, so a shop must opt in deliberately.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('welcome_audio_path')->nullable()->after('og_image_path');
            $table->string('order_audio_path')->nullable()->after('welcome_audio_path');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['welcome_audio_path', 'order_audio_path']);
        });
    }
};
