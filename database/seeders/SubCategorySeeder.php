<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubCategorySeeder extends Seeder
{
    /**
     * Sub categories for the storefront category dropdowns, modelled on the
     * ghorerbazar.com menu. Safe to re-run: existing rows are matched by slug
     * and left alone, so nothing an admin edited later gets overwritten.
     *
     * Categories that do not exist are skipped rather than created, so this
     * never invents a top-level menu item by accident.
     */
    public function run(): void
    {
        $tree = [
            'Honey' => [
                'Sundarban Honey',
                'Black Seed Honey',
                'Lychee Flower Honey',
                'Mustard Flower Honey',
                'African Organic Honey',
                'Sidr Honey',
                'Honeycomb',
                'Sachet Box',
            ],
            'Dates' => [
                'Safawi / Kalmi',
                'Medjool',
                'Sukkari',
                'Ajwa',
                'Mabroom',
            ],
            'Beverage' => [
                'Tea',
                'Coffee',
            ],
            'Oil & Ghee' => [
                'Mustard Oil',
                'Olive Oil',
                'Black Seed Oil',
                'Coconut Oil',
                'Ghee',
            ],
            'Spices' => [
                'Turmeric Powder',
                'Chilli Powder',
                'Coriander Powder',
                'Cumin Powder',
                'Garam Masala',
            ],
            'Nuts & Seeds' => [
                'Almond',
                'Cashew Nut',
                'Pistachio',
                'Walnut',
                'Chia Seed',
                'Mixed Nuts',
            ],
            'Rice' => [
                'Chinigura Rice',
                'Kataribhog Rice',
                'Brown Rice',
                'Red Rice',
            ],
            'Pickle' => [
                'Mango Pickle',
                'Olive Pickle',
                'Mixed Pickle',
                'Garlic Pickle',
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($tree as $categoryName => $subCategoryNames) {
            $category = Category::where('name', $categoryName)->first();

            if (!$category) {
                $this->command?->warn("Category \"{$categoryName}\" not found — skipping its sub categories.");
                continue;
            }

            foreach ($subCategoryNames as $name) {
                $slug = Str::slug($name);

                // name and slug are both globally unique on this table, so match on
                // either before inserting.
                $exists = SubCategory::where('slug', $slug)->orWhere('name', $name)->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                SubCategory::create([
                    'name' => $name,
                    'slug' => $slug,
                    'category_id' => $category->id,
                    'is_active' => 1,
                ]);

                $created++;
            }
        }

        $this->command?->info("Sub categories: {$created} created, {$skipped} already present.");
    }
}
