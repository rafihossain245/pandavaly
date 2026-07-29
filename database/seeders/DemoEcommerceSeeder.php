<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ComboDeal;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Slider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoEcommerceSeeder extends Seeder
{
    /**
     * Minimal demo content so the rebuilt homepage has something to render.
     * Safe to re-run (uses firstOrCreate/updateOrCreate throughout).
     */
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Honey', 'sort_order' => 1],
            ['name' => 'Beverage', 'sort_order' => 2],
            ['name' => 'Spices', 'sort_order' => 3],
            ['name' => 'Dates', 'sort_order' => 4],
            ['name' => 'Oil & Ghee', 'sort_order' => 5],
            ['name' => 'Rice', 'sort_order' => 6],
        ])->map(function ($cat) {
            return Category::firstOrCreate(
                ['name' => $cat['name']],
                ['slug' => Str::slug($cat['name']), 'is_active' => true, 'sort_order' => $cat['sort_order']]
            );
        });

        $brands = collect(['Glarvest', 'Khejuri', 'Shohi', 'Honeya'])->map(function ($name) {
            return Brand::firstOrCreate(['name' => $name], ['slug' => Str::slug($name), 'is_active' => true]);
        });

        $productDefs = [
            ['name' => 'Sundarban Honey 1kg', 'category' => 'Honey', 'price' => 2500, 'prev' => 2800, 'flags' => ['is_trending']],
            ['name' => 'Black Seed Honey 1kg', 'category' => 'Honey', 'price' => 1500, 'prev' => null, 'flags' => ['is_trending', 'is_popular']],
            ['name' => 'MacCoffee Original 95gm', 'category' => 'Beverage', 'price' => 465, 'prev' => null, 'flags' => ['is_popular']],
            ['name' => 'Deshi Mustard Oil 5L', 'category' => 'Oil & Ghee', 'price' => 1700, 'prev' => 1900, 'flags' => ['is_trending']],
            ['name' => 'Organic Green Tea 100gm', 'category' => 'Beverage', 'price' => 1400, 'prev' => null, 'flags' => ['is_recommended']],
            ['name' => 'Ajwa Dates 500gm', 'category' => 'Dates', 'price' => 950, 'prev' => 1100, 'flags' => ['is_popular', 'is_recommended']],
            ['name' => 'Turmeric Powder 200gm', 'category' => 'Spices', 'price' => 120, 'prev' => null, 'flags' => ['is_recommended']],
            ['name' => 'Basmati Rice 5kg', 'category' => 'Rice', 'price' => 850, 'prev' => null, 'flags' => ['is_trending', 'is_recommended']],
        ];

        foreach ($productDefs as $def) {
            $category = $categories->firstWhere('name', $def['category']);

            $product = Product::firstOrCreate(
                ['slug' => Str::slug($def['name'])],
                [
                    'sku' => 'DEMO-' . Str::upper(Str::random(8)),
                    'name' => $def['name'],
                    'category_id' => $category?->id,
                    'brand_id' => $brands->random()->id,
                    'moq' => 1,
                    'is_active' => true,
                    'is_trending' => in_array('is_trending', $def['flags']),
                    'is_popular' => in_array('is_popular', $def['flags']),
                    'is_recommended' => in_array('is_recommended', $def['flags']),
                    'stock_qty' => 50,
                ]
            );

            ProductPrice::updateOrCreate(
                ['product_id' => $product->id, 'pricing_tier_id' => null, 'valid_from' => null],
                [
                    'selling_price' => $def['price'],
                    'previous_price' => $def['prev'] ?? 0,
                ]
            );
        }

        $slider = Slider::firstOrCreate(
            ['title' => 'Natural Food for Every Home'],
            ['image_path' => 'frontEnd/assets/image/banner.png', 'link' => '/shop', 'is_active' => true]
        );

        $heroSection = HomepageSection::firstOrCreate(
            ['title' => 'Hero Banner'],
            ['type' => 'hero_slider', 'heading' => null, 'sort_order' => 1, 'is_active' => true]
        );

        $categorySection = HomepageSection::firstOrCreate(
            ['title' => 'Featured Categories'],
            ['type' => 'category_strip', 'heading' => 'Featured Categories', 'sort_order' => 2, 'is_active' => true]
        );

        $trendingSection = HomepageSection::firstOrCreate(
            ['title' => 'Top Selling Products'],
            [
                'type' => 'product_row',
                'heading' => 'Top Selling Products',
                'config' => ['source' => 'trending', 'limit' => 8],
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        $brandSection = HomepageSection::firstOrCreate(
            ['title' => 'Our Brands'],
            ['type' => 'brand_strip', 'heading' => 'Our Brands', 'sort_order' => 4, 'is_active' => true]
        );

        $popularSection = HomepageSection::firstOrCreate(
            ['title' => 'Popular Products'],
            [
                'type' => 'product_row',
                'heading' => 'Popular Products',
                'config' => ['source' => 'popular', 'limit' => 8],
                'sort_order' => 5,
                'is_active' => true,
            ]
        );

        $bannerSection = HomepageSection::firstOrCreate(
            ['title' => 'Promotional Banner'],
            ['type' => 'split_banner', 'sort_order' => 6, 'is_active' => true]
        );

        Banner::firstOrCreate(
            ['homepage_section_id' => $bannerSection->id, 'title' => 'Cooking Essentials'],
            ['image_path' => 'frontEnd/assets/image/big-banner.png', 'subtitle' => 'Everything for your kitchen', 'link' => '/shop', 'is_active' => true, 'sort_order' => 1]
        );

        $recommendedSection = HomepageSection::firstOrCreate(
            ['title' => 'Just For You'],
            [
                'type' => 'product_row',
                'heading' => 'Just For You',
                'config' => ['source' => 'recommended', 'limit' => 8],
                'sort_order' => 7,
                'is_active' => true,
            ]
        );

        $comboProducts = Product::whereIn('name', ['Sundarban Honey 1kg', 'Black Seed Honey 1kg'])->get();
        if ($comboProducts->count() >= 2) {
            $combo = ComboDeal::firstOrCreate(
                ['name' => 'Honey Combo'],
                [
                    'slug' => Str::slug('Honey Combo') . '-' . Str::random(5),
                    'description' => 'Sundarban Honey + Black Seed Honey bundled together.',
                    'price' => 3500,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
            $combo->products()->sync($comboProducts->pluck('id')->mapWithKeys(fn ($id) => [$id => ['qty' => 1]]));

            HomepageSection::firstOrCreate(
                ['title' => 'Exclusive Combo Deals'],
                ['type' => 'combo_deals', 'heading' => 'Exclusive Combo Deals', 'sort_order' => 8, 'is_active' => true]
            );
        }
    }
}
