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
        Schema::create('media_files', function (Blueprint $t) {
            $t->id();
            $t->morphs('mediable'); // product, doc, etc.
            $t->string('disk')->default('public');
            $t->string('path'); // e.g., uploads/…
            $t->string('mime')->nullable();
            $t->string('title')->nullable();
            $t->unsignedBigInteger('uploaded_by')->nullable()->index();
            $t->timestamps(); 
            $t->softDeletes();
            $t->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
