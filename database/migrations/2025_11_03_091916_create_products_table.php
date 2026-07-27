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
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('sku')->unique(); // master SKU
            $t->string('name');
            $t->string('slug')->unique();
            $t->unsignedBigInteger('category_id')->nullable();
            $t->unsignedBigInteger('sub_category_id')->nullable();
            $t->unsignedBigInteger('brand_id')->nullable();
            $t->unsignedBigInteger('unit_id')->nullable();
            $t->unsignedBigInteger('supplier_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('description')->nullable();
            $t->string('thumbnail')->nullable();
            $t->unsignedInteger('moq')->default(5); // minimum order quantity
            $t->boolean('price_public')->default(false); // show without login
            $t->boolean('is_active')->default(true);
            $t->boolean('is_trending')->default(false);
            $t->boolean('is_popular')->default(false);
            $t->boolean('is_recommended')->default(false);
            $t->timestamps(); 
            $t->softDeletes();
            $t->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $t->foreign('sub_category_id')->references('id')->on('sub_categories')->nullOnDelete();
            $t->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $t->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $t->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $t->index(['name','category_id','sub_category_id']);

            // $table->unsignedBigInteger('user_id')->nullable()->comment('Reference to the user who created this product');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->string('name');
            // $table->string('sku')->unique();
            // $table->unsignedBigInteger('category_id')->nullable();
            // $table->unsignedBigInteger('sub_category_id')->nullable();
            // $table->unsignedBigInteger('brand_id')->nullable();
            // $table->unsignedBigInteger('unit_id')->nullable();
            // $table->unsignedBigInteger('supplier_id')->nullable();
            // $table->decimal('purchase_price', 15, 2)->default(0);
            // $table->decimal('selling_price', 15, 2)->default(0);
            // $table->integer('stock_qty')->default(0); // total available across branches
            // $table->boolean('is_active')->default(true);

            // $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            // $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onDelete('set null');
            // $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            // $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            // $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null')->comment('Reference to the supplier of this product');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
