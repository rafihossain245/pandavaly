<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

/**
 * Starter attribute library. Idempotent — matches on the attribute code and
 * the value text, so re-running never duplicates and never overwrites colours
 * or names the admin has since changed.
 */
class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            [
                'code' => 'size',
                'name' => 'Size',
                'display_type' => Attribute::DISPLAY_PILL,
                'values' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            ],
            [
                'code' => 'color',
                'name' => 'Colour',
                'display_type' => Attribute::DISPLAY_SWATCH,
                'values' => [
                    'Black' => '#111827',
                    'White' => '#ffffff',
                    'Red' => '#dc2626',
                    'Blue' => '#2563eb',
                    'Green' => '#16a34a',
                    'Yellow' => '#facc15',
                    'Maroon' => '#7f1d1d',
                    'Navy' => '#1e3a8a',
                ],
            ],
            [
                'code' => 'weight',
                'name' => 'Weight',
                'display_type' => Attribute::DISPLAY_PILL,
                'values' => ['250 g', '500 g', '1 kg', '2 kg', '5 kg'],
            ],
        ];

        foreach ($attributes as $position => $definition) {
            $attribute = Attribute::firstOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'type' => 'select',
                    'display_type' => $definition['display_type'],
                    'position' => $position + 1,
                ]
            );

            foreach (array_values($definition['values']) as $index => $value) {
                $label = is_int(array_keys($definition['values'])[$index])
                    ? $value
                    : array_keys($definition['values'])[$index];

                $color = is_int(array_keys($definition['values'])[$index]) ? null : $value;

                AttributeValue::firstOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $label],
                    ['color_code' => $color, 'position' => $index + 1]
                );
            }
        }
    }
}
