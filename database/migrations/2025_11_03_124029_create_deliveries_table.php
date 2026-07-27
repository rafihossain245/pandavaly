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
        Schema::create('deliveries', function (Blueprint $t) {
            $t->id();
            $t->string('delivery_no')->unique();
            $t->unsignedBigInteger('sales_order_id');
            $t->enum('status',['pending','shipped','delivered'])->default('pending');
            $t->string('courier')->nullable();
            $t->string('tracking_no')->nullable();
            $t->unsignedBigInteger('pod_media_id')->nullable(); // proof of delivery
            $t->dateTime('delivered_at')->nullable();
            $t->timestamps(); $t->softDeletes();
            $t->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();
            $t->foreign('pod_media_id')->references('id')->on('media_files')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
