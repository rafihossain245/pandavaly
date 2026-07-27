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
        Schema::create('buyers', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('company_id'); // EPAL as owner (multi-tenant optional)
            $t->string('business_name');
            $t->string('category'); // Interior, IT Shop, Contractor
            $t->string('email')->nullable(); $t->string('phone')->nullable();
            $t->string('tin')->nullable();
            $t->string('trade_license_no')->nullable();
            $t->date('trade_license_expiry')->nullable();
            $t->enum('status', ['pending','active','suspended','blacklisted'])->default('pending');
            $t->unsignedBigInteger('pricing_tier_id')->nullable();
            $t->unsignedBigInteger('credit_level_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable()->index(); // owner user
            $t->json('verification_log')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $t->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->nullOnDelete();
            $t->foreign('credit_level_id')->references('id')->on('credit_levels')->nullOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $t->index(['status','category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};
