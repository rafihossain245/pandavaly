<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Recreates the footer's link columns as admin-managed content.
 *
 * Idempotent: matches categories by name and pages by slug, so re-running never
 * duplicates and never overwrites content the admin has since written.
 *
 * Deliberately absent (per the storefront's current scope):
 *  - "Careers" — not a real page for this shop
 *  - "Payment" — checkout is cash-on-delivery only, so there is nothing to explain.
 *    Add it back from Content > Pages if online payments are introduced.
 */
class FooterPageSeeder extends Seeder
{
    public function run(): void
    {
        $columns = [
            'Information' => [
                ['title' => 'About us'],
                ['title' => 'Contact us'],
                ['title' => 'Company Information'],
                ['title' => 'Our Stories'],
                ['title' => 'Terms & Conditions'],
                ['title' => 'Privacy Policy'],
            ],
            'Support' => [
                ['title' => 'Support Center'],
                ['title' => 'How to Order'],
                // Real feature, not a content page — link straight at it.
                ['title' => 'Order Tracking', 'link_url' => '/track-order'],
                ['title' => 'Shipping'],
                ['title' => 'FAQ'],
            ],
            'Consumer Policy' => [
                ['title' => 'Happy Return'],
                ['title' => 'Refund Policy'],
                ['title' => 'Exchange'],
                ['title' => 'Cancellation'],
                ['title' => 'Pre-Order'],
                ['title' => 'Extra Discount'],
            ],
        ];

        foreach (array_keys($columns) as $index => $name) {
            $category = PageCategory::firstOrCreate(
                ['name' => $name],
                ['position' => $index + 1, 'is_active' => true]
            );

            foreach ($columns[$name] as $position => $definition) {
                // Match on the plain slug of the title — uniqueSlug() deliberately
                // returns an *unused* slug, which would create a duplicate on every
                // re-run instead of finding the existing row.
                Page::firstOrCreate(
                    ['slug' => Str::slug($definition['title'])],
                    [
                        'category_id' => $category->id,
                        'title' => $definition['title'],
                        'content' => null,
                        'link_url' => $definition['link_url'] ?? null,
                        'position' => $position + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
