<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            // Google's subject id. Unique so two accounts can never claim the
            // same Google identity, nullable so password-only accounts are fine.
            $table->string('google_id')->nullable()->unique()->after('password');
            $table->string('avatar')->nullable()->after('google_id');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');

            // OTP sign-in looks a buyer up by phone on every attempt.
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'avatar', 'phone_verified_at']);
        });
    }
};
