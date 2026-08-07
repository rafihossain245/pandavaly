<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded column-by-column: these were also applied by hand on some
        // installs, and an unguarded add aborts the whole migration queue.
        Schema::table('buyers', function (Blueprint $table) {
            if (! Schema::hasColumn('buyers', 'google_id')) {
                // Google's subject id. Unique so two accounts can never claim the
                // same Google identity, nullable so password-only accounts are fine.
                $table->string('google_id')->nullable()->unique()->after('password');
            }

            if (! Schema::hasColumn('buyers', 'avatar')) {
                $table->string('avatar')->nullable()->after('google_id');
            }

            if (! Schema::hasColumn('buyers', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone');
            }

            // OTP sign-in looks a buyer up by phone on every attempt.
            if (! $this->hasIndex('buyers', 'buyers_phone_index')) {
                $table->index('phone');
            }
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn ($existing) => ($existing['name'] ?? null) === $index);
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
