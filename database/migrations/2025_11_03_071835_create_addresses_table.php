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
        Schema::create('addresses', function (Blueprint $t) {
            $t->id();
            $t->morphs('addressable'); // *_id + *_type
            $t->string('label')->nullable(); // HQ, Billing, Shipping
            $t->string('contact_person')->nullable();
            $t->string('phone')->nullable();
            $t->string('line1'); 
            $t->string('line2')->nullable();
            $t->string('city'); 
            $t->string('state')->nullable();
            $t->string('postal_code')->nullable(); 
            $t->string('country', 2)->default('BD');
            $t->boolean('is_default')->default(false);
            $t->timestamps(); 
            $t->softDeletes();
            $t->index(['addressable_id','addressable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
