<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->change();
            $table->foreignId('district_id')->nullable()->after('state')->constrained()->nullOnDelete();
            $table->foreignId('thana_id')->nullable()->after('district_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('thana_id');
            $table->dropConstrainedForeignId('district_id');
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });
    }
};
