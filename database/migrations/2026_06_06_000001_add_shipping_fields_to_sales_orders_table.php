<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('buyer_id');
            $table->string('shipping_email')->nullable()->after('shipping_name');
            $table->string('shipping_phone')->nullable()->after('shipping_email');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_postal_code')->nullable()->after('shipping_state');
            $table->string('shipping_country')->nullable()->after('shipping_postal_code');
            $table->text('note')->nullable()->after('shipping_country');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_email',
                'shipping_phone',
                'shipping_address',
                'shipping_city',
                'shipping_state',
                'shipping_postal_code',
                'shipping_country',
                'note',
            ]);
        });
    }
};
