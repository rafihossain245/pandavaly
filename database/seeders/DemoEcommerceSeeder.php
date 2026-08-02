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
     * Demo content so the rebuilt storefront has something real to render:
     * categories, brands, a broad product catalog, sliders, homepage
     * sections and a combo deal. Safe to re-run (firstOrCreate/updateOrCreate
     * throughout). Thumbnails are honest SVG placeholders (colored tile +
     * emoji) under public/images/demo/ — not real product photography.
     */
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Honey', 'sort_order' => 1, 'image' => 'images/demo/honey.svg'],
            ['name' => 'Beverage', 'sort_order' => 2, 'image' => 'images/demo/beverage.svg'],
            ['name' => 'Spices', 'sort_order' => 3, 'image' => 'images/demo/spices.svg'],
            ['name' => 'Dates', 'sort_order' => 4, 'image' => 'images/demo/dates.svg'],
            ['name' => 'Oil & Ghee', 'sort_order' => 5, 'image' => 'images/demo/oil-ghee.svg'],
            ['name' => 'Rice', 'sort_order' => 6, 'image' => 'images/demo/rice.svg'],
            ['name' => 'Nuts & Seeds', 'sort_order' => 7, 'image' => 'images/demo/nuts.svg'],
            ['name' => 'Pickle', 'sort_order' => 8, 'image' => 'images/demo/pickle.svg'],
        ])->map(function ($cat) {
            return Category::updateOrCreate(
                ['name' => $cat['name']],
                [
                    'slug' => Str::slug($cat['name']),
                    'is_active' => true,
                    'sort_order' => $cat['sort_order'],
                    'image_path' => $cat['image'],
                ]
            );
        });

        $brandNames = ['Glarvest', 'Khejuri', 'Shohi', 'Honeya', 'MacCoffee', 'Sreemangal'];
        $brands = collect($brandNames)->map(function ($name) {
            $slug = Str::slug($name);
            $path = 'images/demo/brands/' . $slug . '.svg';
            $this->ensureBrandLogo($name, $path);

            return Brand::updateOrCreate(
                ['name' => $name],
                ['slug' => $slug, 'is_active' => true, 'image' => $path]
            );
        });

        $productDefs = [
            // Honey
            ['name' => 'Sundarban Honey 1kg', 'category' => 'Honey', 'image' => 'honey', 'price' => 2500, 'prev' => 2800, 'flags' => ['is_trending']],
            ['name' => 'Black Seed Honey 1kg', 'category' => 'Honey', 'image' => 'honey', 'price' => 1500, 'prev' => null, 'flags' => ['is_trending', 'is_popular']],
            ['name' => 'Litchi Flower Honey 500gm', 'category' => 'Honey', 'image' => 'honey', 'price' => 900, 'prev' => 1000, 'flags' => ['is_popular']],
            ['name' => 'Mustard Flower Honey 1kg', 'category' => 'Honey', 'image' => 'honey', 'price' => 1300, 'prev' => null, 'flags' => ['is_recommended']],

            // Beverage
            ['name' => 'MacCoffee Original 95gm', 'category' => 'Beverage', 'image' => 'beverage', 'price' => 465, 'prev' => null, 'flags' => ['is_popular']],
            ['name' => 'MacCoffee Gold 100gm', 'category' => 'Beverage', 'image' => 'beverage', 'price' => 895, 'prev' => null, 'flags' => ['is_trending']],
            ['name' => 'Organic Green Tea 100gm', 'category' => 'Beverage', 'image' => 'beverage', 'price' => 1400, 'prev' => null, 'flags' => ['is_recommended']],
            ['name' => "Sreemangal's Tea Gold 500g", 'category' => 'Beverage', 'image' => 'beverage', 'price' => 300, 'prev' => 350, 'flags' => ['is_popular', 'is_recommended']],
            ['name' => 'Organic Matcha Green Tea 100gm', 'category' => 'Beverage', 'image' => 'beverage', 'price' => 1500, 'prev' => null, 'flags' => []],

            // Spices
            ['name' => 'Turmeric Powder 200gm', 'category' => 'Spices', 'image' => 'spices', 'price' => 120, 'prev' => null, 'flags' => ['is_recommended']],
            ['name' => 'Red Chili Powder 200gm', 'category' => 'Spices', 'image' => 'spices', 'price' => 140, 'prev' => 160, 'flags' => ['is_popular']],
            ['name' => 'Cumin Powder 100gm', 'category' => 'Spices', 'image' => 'spices', 'price' => 110, 'prev' => null, 'flags' => ['is_trending']],
            ['name' => 'Coriander Powder 100gm', 'category' => 'Spices', 'image' => 'spices', 'price' => 90, 'prev' => null, 'flags' => []],

            // Dates
            ['name' => 'Ajwa Dates 500gm', 'category' => 'Dates', 'image' => 'dates', 'price' => 950, 'prev' => 1100, 'flags' => ['is_popular', 'is_recommended']],
            ['name' => 'Mariam Dates 1kg', 'category' => 'Dates', 'image' => 'dates', 'price' => 1800, 'prev' => null, 'flags' => ['is_trending']],
            ['name' => 'Segai Dates 500gm', 'category' => 'Dates', 'image' => 'dates', 'price' => 1250, 'prev' => 1400, 'flags' => ['is_recommended']],
            ['name' => 'Kalmi Dates 1kg', 'category' => 'Dates', 'image' => 'dates', 'price' => 1600, 'prev' => null, 'flags' => []],

            // Oil & Ghee
            ['name' => 'Deshi Mustard Oil 5L', 'category' => 'Oil & Ghee', 'image' => 'oil-ghee', 'price' => 1700, 'prev' => 1900, 'flags' => ['is_trending']],
            ['name' => 'Pure Cow Ghee 1kg', 'category' => 'Oil & Ghee', 'image' => 'oil-ghee', 'price' => 1650, 'prev' => null, 'flags' => ['is_popular']],
            ['name' => 'Soybean Oil 5L', 'category' => 'Oil & Ghee', 'image' => 'oil-ghee', 'price' => 950, 'prev' => null, 'flags' => ['is_recommended']],
            ['name' => 'Olive Oil 500ml', 'category' => 'Oil & Ghee', 'image' => 'oil-ghee', 'price' => 1100, 'prev' => 1250, 'flags' => []],

            // Rice
            ['name' => 'Basmati Rice 5kg', 'category' => 'Rice', 'image' => 'rice', 'price' => 850, 'prev' => null, 'flags' => ['is_trending', 'is_recommended']],
            ['name' => 'Chinigura Rice 5kg', 'category' => 'Rice', 'image' => 'rice', 'price' => 780, 'prev' => null, 'flags' => ['is_popular']],
            ['name' => 'Kalijira Rice 2kg', 'category' => 'Rice', 'image' => 'rice', 'price' => 420, 'prev' => 460, 'flags' => []],

            // Nuts & Seeds
            ['name' => 'Cashew Nuts 500gm', 'category' => 'Nuts & Seeds', 'image' => 'nuts', 'price' => 1100, 'prev' => null, 'flags' => ['is_trending']],
            ['name' => 'Almonds 500gm', 'category' => 'Nuts & Seeds', 'image' => 'nuts', 'price' => 950, 'prev' => 1050, 'flags' => ['is_popular']],
            ['name' => 'Pumpkin Seeds 200gm', 'category' => 'Nuts & Seeds', 'image' => 'nuts', 'price' => 280, 'prev' => null, 'flags' => ['is_recommended']],
            ['name' => 'Walnuts 500gm', 'category' => 'Nuts & Seeds', 'image' => 'nuts', 'price' => 1200, 'prev' => null, 'flags' => []],

            // Pickle
            ['name' => 'Mango Pickle 400gm', 'category' => 'Pickle', 'image' => 'pickle', 'price' => 220, 'prev' => null, 'flags' => ['is_popular']],
            ['name' => 'Mixed Vegetable Pickle 400gm', 'category' => 'Pickle', 'image' => 'pickle', 'price' => 200, 'prev' => 230, 'flags' => ['is_recommended']],
            ['name' => 'Olive Pickle 350gm', 'category' => 'Pickle', 'image' => 'pickle', 'price' => 260, 'prev' => null, 'flags' => []],
        ];

        foreach ($productDefs as $def) {
            $category = $categories->firstWhere('name', $def['category']);

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($def['name'])],
                [
                    'sku' => 'DEMO-' . Str::upper(Str::slug($def['name'], '')),
                    'name' => $def['name'],
                    'category_id' => $category?->id,
                    'brand_id' => $brands->random()->id,
                    'thumbnail' => 'images/demo/' . $def['image'] . '.svg',
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

        // Side banner(s) shown next to the hero slider (reference layout:
        // large rotating slider on the left + a promo banner on the right).
        Banner::firstOrCreate(
            ['homepage_section_id' => $heroSection->id, 'title' => 'Organic Wild Honey'],
            [
                'image_path' => 'frontEnd/assets/image/big-banner.png',
                'subtitle' => 'Pure & naturally harvested',
                'link' => '/shop?category=honey',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        HomepageSection::firstOrCreate(
            ['title' => 'Featured Categories'],
            ['type' => 'category_strip', 'heading' => 'Featured Categories', 'sort_order' => 2, 'is_active' => true]
        );

        HomepageSection::firstOrCreate(
            ['title' => 'Top Selling Products'],
            [
                'type' => 'product_row',
                'heading' => 'Top Selling Products',
                'config' => ['source' => 'trending', 'limit' => 8],
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        HomepageSection::firstOrCreate(
            ['title' => 'Our Brands'],
            ['type' => 'brand_strip', 'heading' => 'Our Brands', 'sort_order' => 4, 'is_active' => true]
        );

        HomepageSection::firstOrCreate(
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

        HomepageSection::firstOrCreate(
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
                    'image' => 'images/demo/honey.svg',
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

    /**
     * Generate a simple colored-badge SVG logo for a demo brand if one
     * doesn't already exist on disk.
     */
    protected function ensureBrandLogo(string $name, string $relativePath): void
    {
        $fullPath = public_path($relativePath);

        if (file_exists($fullPath)) {
            return;
        }

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $colors = ['#f4801f', '#0b3d2e', '#c2410c', '#166534', '#9a3412', '#065f46'];
        $color = $colors[crc32($name) % count($colors)];
        $fontSize = strlen($name) > 9 ? 18 : 24;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="80">'
            . '<rect width="200" height="80" rx="8" fill="' . $color . '"/>'
            . '<text x="50%" y="52%" font-size="' . $fontSize . '" font-family="Arial, sans-serif" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="middle">'
            . htmlspecialchars(strtoupper($name))
            . '</text></svg>';

        file_put_contents($fullPath, $svg);
    }
}
