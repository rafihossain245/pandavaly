<?php

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $category = PageCategory::where('name', 'Customer Information')->first();

        if (!$category) {
            return;
        }

        Page::whereIn('slug', [
            'delivery-return-policy',
            'privacy-policy',
            'terms-and-conditions',
        ])->update(['category_id' => $category->id]);
    }

    public function down(): void
    {
        $category = PageCategory::where('name', 'Customer Information')->first();

        if ($category) {
            Page::where('category_id', $category->id)
                ->whereIn('slug', [
                    'delivery-return-policy',
                    'privacy-policy',
                    'terms-and-conditions',
                ])
                ->update(['category_id' => null]);
        }
    }
};
