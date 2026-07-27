<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('password')->nullable()->after('email');
            $table->rememberToken();
            $table->text('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code', 50)->nullable()->after('state');
            $table->string('country')->nullable()->after('postal_code');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn([
                'password',
                'remember_token',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
            ]);
        });
    }
};
