<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('buyer_id');
            $table->string('contact_person')->nullable()->after('company_name');
            $table->string('delivery_contact_name')->nullable()->after('shipping_phone');
            $table->string('delivery_contact_phone')->nullable()->after('delivery_contact_name');
            $table->string('purchase_ref_no')->nullable()->after('note');
            $table->enum('payment_method', ['cod', 'bank_transfer'])->default('cod')->after('purchase_ref_no');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'company_name', 'contact_person',
                'delivery_contact_name', 'delivery_contact_phone',
                'purchase_ref_no', 'payment_method',
            ]);
        });
    }
};
