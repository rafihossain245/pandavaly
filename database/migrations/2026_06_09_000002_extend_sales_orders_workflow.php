<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change payment_method from ENUM to VARCHAR so new gateways
        // (SSLCommerz, bKash, Nagad, etc.) never require another migration
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'cod'");

        // Extend status enum with new ecommerce workflow stages
        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM(
            'pending','approved','payment_requested','payment_verified',
            'processing','confirmed','packed','shipped',
            'delivered','completed','cancelled'
        ) NOT NULL DEFAULT 'pending'");

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('payment_method');
            $table->string('payment_slip_path')->nullable()->after('payment_status');
            $table->string('payment_transaction_id')->nullable()->after('payment_slip_path');
            $table->string('payment_bank_name')->nullable()->after('payment_transaction_id');
            $table->timestamp('payment_slip_uploaded_at')->nullable()->after('payment_bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status', 'payment_slip_path',
                'payment_transaction_id', 'payment_bank_name',
                'payment_slip_uploaded_at',
            ]);
        });

        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN status ENUM(
            'pending','confirmed','packed','shipped','delivered','completed','cancelled'
        ) NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE sales_orders MODIFY COLUMN payment_method ENUM('cod','bank_transfer') NOT NULL DEFAULT 'cod'");
    }
};
