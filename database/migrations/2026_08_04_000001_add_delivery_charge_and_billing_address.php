<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Delivery charge lives on the district so it stays data-driven:
        // Dhaka Tk 70, every other district Tk 130 (editable per district later).
        Schema::table('districts', function (Blueprint $table) {
            $table->decimal('delivery_charge', 10, 2)->default(130)->after('name_bn');
        });

        DB::table('districts')->where('name', 'Dhaka')->update(['delivery_charge' => 70]);

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('shipping_charge', 14, 2)->default(0)->after('tax');

            $table->boolean('billing_same_as_shipping')->default(true)->after('thana_id');
            $table->string('billing_name')->nullable()->after('billing_same_as_shipping');
            $table->string('billing_phone')->nullable()->after('billing_name');
            $table->string('billing_email')->nullable()->after('billing_phone');
            $table->text('billing_address')->nullable()->after('billing_email');
            $table->string('billing_country')->nullable()->after('billing_address');
            $table->foreignId('billing_district_id')->nullable()->after('billing_country')
                ->constrained('districts')->nullOnDelete();
            $table->foreignId('billing_thana_id')->nullable()->after('billing_district_id')
                ->constrained('thanas')->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('shipping_charge', 14, 2)->default(0)->after('tax');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('shipping_charge');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_thana_id');
            $table->dropConstrainedForeignId('billing_district_id');
            $table->dropColumn([
                'shipping_charge',
                'billing_same_as_shipping',
                'billing_name',
                'billing_phone',
                'billing_email',
                'billing_address',
                'billing_country',
            ]);
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn('delivery_charge');
        });
    }
};
