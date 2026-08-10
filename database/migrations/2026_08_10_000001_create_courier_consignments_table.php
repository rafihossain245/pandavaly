<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per order handed to a courier.
 *
 * Deliberately separate from the ERP template's `deliveries` table: that one is
 * unused (0 rows) and its status is an enum of pending/shipped/delivered, which
 * cannot hold Steadfast's eleven delivery states. Keeping this apart also leaves
 * room for a second courier later without reshaping either table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_consignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->string('courier', 40)->default('steadfast');

            // What we sent as the courier's unique invoice reference. Kept even
            // when the push fails, so a retry reuses it instead of minting a
            // second reference for the same parcel.
            $table->string('invoice');

            $table->string('consignment_id')->nullable();
            $table->string('tracking_code')->nullable();

            // Courier-side delivery state, e.g. in_review / delivered / cancelled.
            $table->string('delivery_status', 60)->nullable();

            $table->string('recipient_phone', 20);
            $table->decimal('cod_amount', 14, 2)->default(0);
            $table->unsignedTinyInteger('delivery_type')->default(0);

            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_body')->nullable();

            $table->timestamp('pushed_at')->nullable();
            $table->timestamp('status_synced_at')->nullable();
            $table->timestamps();

            // The idempotency guard: one consignment per order per courier, so a
            // duplicate job, a cron retry and a manual click cannot ship twice.
            $table->unique(['sales_order_id', 'courier']);
            $table->index('delivery_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_consignments');
    }
};
