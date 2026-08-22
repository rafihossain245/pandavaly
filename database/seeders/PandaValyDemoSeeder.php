<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ComboDeal;
use App\Models\HomepageSection;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Panda Valy storefront demo content: a home-textile catalogue (bedsheets,
 * comforters, bed covers, pillow covers, blankets) and the homepage laid out
 * as the reference design — one full-width promo banner above a single
 * "প্রোডাক্ট গ্যালারি" product grid.
 *
 * Safe to re-run: every write is an updateOrCreate/firstOrCreate keyed on a
 * natural key, so a second run updates in place rather than duplicating.
 *
 * Nothing is deleted. Superseded demo rows and the homepage sections this
 * layout does not use are switched off (is_active = 0) so they can be brought
 * back from the admin. Product images are honest SVG placeholders under
 * public/images/demo/, not real photography.
 */
class PandaValyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();
        $brand = $this->seedBrand();
        $products = $this->seedProducts($categories, $brand);
        $this->seedHomepage($categories);
        $this->retireSupersededDemoData($categories, $brand, $products);

        $this->command?->info('Panda Valy home-textile demo content seeded.');
    }

    /** @return \Illuminate\Support\Collection<string, Category> */
    protected function seedCategories()
    {
        $defs = [
            ['name' => 'Bedsheets',     'emoji' => '🛏️', 'bg' => '#fce7f3'],
            ['name' => 'Comforters',    'emoji' => '🧶', 'bg' => '#f3e8ff'],
            ['name' => 'Bed Covers',    'emoji' => '🛋️', 'bg' => '#e0f2fe'],
            ['name' => 'Pillow Covers', 'emoji' => '🪶', 'bg' => '#ecfdf5'],
            ['name' => 'Blankets',      'emoji' => '🧣', 'bg' => '#fef3c7'],
            ['name' => 'Curtains',      'emoji' => '🪟', 'bg' => '#f1f5f9'],
        ];

        $out = collect();

        foreach ($defs as $i => $def) {
            $slug = Str::slug($def['name']);
            $path = 'images/demo/' . $slug . '.svg';
            $this->ensureTile($def['emoji'], $def['bg'], $path);

            $out[$def['name']] = Category::updateOrCreate(
                ['name' => $def['name']],
                [
                    'slug' => $slug,
                    'is_active' => true,
                    'sort_order' => $i + 1,
                    'image_path' => $path,
                ]
            );
        }

        return $out;
    }

    protected function seedBrand(): Brand
    {
        $path = 'images/demo/brands/panda-valy.svg';
        $this->ensureBrandLogo('Panda Valy', '#e6007e', $path);

        return Brand::updateOrCreate(
            ['name' => 'Panda Valy'],
            ['slug' => 'panda-valy', 'is_active' => true, 'image' => $path]
        );
    }

    /**
     * Slugs are written explicitly rather than derived from the Bengali names:
     * transliteration output is not guaranteed stable across framework
     * versions, and these end up in product URLs.
     *
     * @return \Illuminate\Support\Collection<string, Product>
     */
    protected function seedProducts($categories, Brand $brand)
    {
        $defs = [
            // ---- Bedsheets (the reference gallery) ----
            ['name' => 'রোজ ফ্লোরাল বেডশিট — ৭/৮ ফিট (৫/৬ হাত প্রায়)',   'slug' => 'rose-floral-bedsheet-7-8-feet',    'cat' => 'Bedsheets', 'price' => 1250, 'prev' => 1600, 'tile' => ['🌹', '#fce7f3'], 'flags' => ['is_trending']],
            ['name' => 'ট্রপিক্যাল ব্লুম বেডশিট — ৭/৮ ফিট (৫/৬ হাত প্রায়)', 'slug' => 'tropical-bloom-bedsheet-7-8-feet', 'cat' => 'Bedsheets', 'price' => 1250, 'prev' => 1600, 'tile' => ['🌺', '#fde7f0'], 'flags' => ['is_trending']],
            ['name' => 'প্যাস্টেল গার্ডেন বেডশিট — ৭/৮ ফিট (৫/৬ হাত প্রায়)','slug' => 'pastel-garden-bedsheet-7-8-feet',  'cat' => 'Bedsheets', 'price' => 1250, 'prev' => 1600, 'tile' => ['🌷', '#f5e9fb'], 'flags' => ['is_trending']],
            ['name' => 'নাইট ব্লসম বেডশিট — ৭/৮ ফিট (৫/৬ হাত প্রায়)',     'slug' => 'night-blossom-bedsheet-7-8-feet',  'cat' => 'Bedsheets', 'price' => 1250, 'prev' => 1600, 'tile' => ['🌙', '#e9e7fb'], 'flags' => ['is_trending']],
            ['name' => 'ক্লাসিক জ্যাকার্ড বেডশিট — ৬/৭ ফিট (৪/৫ হাত প্রায়)','slug' => 'classic-jacquard-bedsheet-6-7-feet','cat' => 'Bedsheets', 'price' => 1050, 'prev' => 1350, 'tile' => ['🪷', '#e7f5fb'], 'flags' => ['is_trending']],
            ['name' => 'ভেলভেট টাচ বেডশিট — ৬/৭ ফিট (৪/৫ হাত প্রায়)',    'slug' => 'velvet-touch-bedsheet-6-7-feet',   'cat' => 'Bedsheets', 'price' => 1050, 'prev' => null, 'tile' => ['🧵', '#fbe9e7'], 'flags' => ['is_trending']],
            ['name' => 'কটন প্রিন্ট বেডশিট — ৫/৬ ফিট (৩/৪ হাত প্রায়)',    'slug' => 'cotton-print-bedsheet-5-6-feet',   'cat' => 'Bedsheets', 'price' => 850,  'prev' => 1100, 'tile' => ['🌼', '#fdf3e3'], 'flags' => ['is_popular']],
            ['name' => 'সলিড কালার বেডশিট — ৫/৬ ফিট (৩/৪ হাত প্রায়)',    'slug' => 'solid-color-bedsheet-5-6-feet',    'cat' => 'Bedsheets', 'price' => 850,  'prev' => null, 'tile' => ['🎨', '#eafaf0'], 'flags' => ['is_popular']],

            // ---- Other home textiles ----
            ['name' => 'উইন্টার কম্ফোর্টার — ডাবল',      'slug' => 'winter-comforter-double',   'cat' => 'Comforters',    'price' => 2450, 'prev' => 2900, 'tile' => ['🧶', '#f3e8ff'], 'flags' => ['is_popular']],
            ['name' => 'লাইট কম্ফোর্টার — সিঙ্গেল',      'slug' => 'light-comforter-single',    'cat' => 'Comforters',    'price' => 1700, 'prev' => null, 'tile' => ['🧶', '#efe6fb'], 'flags' => []],
            ['name' => 'কুইল্টেড বেড কভার — ডাবল',       'slug' => 'quilted-bed-cover-double',  'cat' => 'Bed Covers',    'price' => 1950, 'prev' => 2300, 'tile' => ['🛋️', '#e0f2fe'], 'flags' => ['is_recommended']],
            ['name' => 'এমব্রয়ডারি বেড কভার — কিং',      'slug' => 'embroidery-bed-cover-king', 'cat' => 'Bed Covers',    'price' => 2600, 'prev' => null, 'tile' => ['🛋️', '#dbeafe'], 'flags' => []],
            ['name' => 'কটন পিলো কভার — ২ পিস সেট',      'slug' => 'cotton-pillow-cover-2pc',   'cat' => 'Pillow Covers', 'price' => 350,  'prev' => 450,  'tile' => ['🪶', '#ecfdf5'], 'flags' => ['is_popular']],
            ['name' => 'ভেলভেট পিলো কভার — ২ পিস সেট',   'slug' => 'velvet-pillow-cover-2pc',   'cat' => 'Pillow Covers', 'price' => 480,  'prev' => null, 'tile' => ['🪶', '#e6faf1'], 'flags' => []],
            ['name' => 'সফট ফ্লিস কম্বল — ডাবল',         'slug' => 'soft-fleece-blanket-double','cat' => 'Blankets',      'price' => 1400, 'prev' => 1750, 'tile' => ['🧣', '#fef3c7'], 'flags' => ['is_recommended']],
            ['name' => 'মিঙ্ক কম্বল — সিঙ্গেল',           'slug' => 'mink-blanket-single',       'cat' => 'Blankets',      'price' => 1150, 'prev' => null, 'tile' => ['🧣', '#fdf0d5'], 'flags' => []],
        ];

        $out = collect();

        foreach ($defs as $def) {
            [$emoji, $bg] = $def['tile'];
            $thumb = 'images/demo/textiles/' . $def['slug'] . '.svg';
            $this->ensureTile($emoji, $bg, $thumb);

            $product = Product::updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'sku' => 'PV-' . Str::upper(str_replace('-', '', $def['slug'])),
                    'name' => $def['name'],
                    'category_id' => $categories[$def['cat']]->id,
                    'brand_id' => $brand->id,
                    'thumbnail' => $thumb,
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
                ['selling_price' => $def['price'], 'previous_price' => $def['prev'] ?? 0]
            );

            $out[$def['slug']] = $product;
        }

        return $out;
    }

    /**
     * The reference front page is a promo banner above one product gallery.
     * Every other section is switched off rather than deleted, so the richer
     * layout (categories, combos, brands, testimonials) is one toggle away in
     * Website Management → Homepage Sections.
     */
    protected function seedHomepage($categories): void
    {
        $bannerPath = 'images/demo/banners/dhamaka-offer.svg';
        $this->ensureOfferBanner($bannerPath);

        $promo = HomepageSection::updateOrCreate(
            ['title' => 'Promo Banner'],
            ['type' => 'split_banner', 'sort_order' => 1, 'is_active' => true, 'config' => null]
        );

        Banner::updateOrCreate(
            ['homepage_section_id' => $promo->id, 'title' => null],
            [
                'image_path' => $bannerPath,
                'subtitle' => null,
                'link' => '/shop',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $gallery = HomepageSection::updateOrCreate(
            ['title' => 'Product Gallery'],
            [
                'type' => 'product_row',
                'heading' => 'প্রোডাক্ট গ্যালারি',
                'subheading' => null,
                'config' => [
                    'source' => 'category',
                    'category_id' => $categories['Bedsheets']->id,
                    'limit' => 12,
                    'layout' => 'grid',
                ],
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        HomepageSection::whereNotIn('id', [$promo->id, $gallery->id])->update(['is_active' => 0]);
        Banner::where('homepage_section_id', '!=', $promo->id)->update(['is_active' => 0]);
    }

    protected function retireSupersededDemoData($categories, Brand $brand, $products): void
    {
        Category::whereNotIn('id', $categories->pluck('id'))->update(['is_active' => 0]);
        Brand::where('id', '!=', $brand->id)->update(['is_active' => 0]);
        ComboDeal::query()->update(['is_active' => 0]);

        // Only previously seeded demo stock is retired; anything the shop owner
        // added themselves (no DEMO-/PV- SKU prefix) is left alone.
        Product::whereNotIn('id', $products->pluck('id'))
            ->where(fn ($q) => $q->where('sku', 'like', 'DEMO-%')->orWhere('sku', 'like', 'PV-%'))
            ->update(['is_active' => 0]);
    }

    /** Coloured tile + emoji placeholder, written only if absent. */
    protected function ensureTile(string $emoji, string $bg, string $relativePath): void
    {
        $full = public_path($relativePath);
        if (file_exists($full)) {
            return;
        }
        if (! is_dir(dirname($full))) {
            mkdir(dirname($full), 0755, true);
        }

        file_put_contents($full,
            '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600">'
            . '<rect width="600" height="600" fill="' . $bg . '"/>'
            . '<text x="50%" y="52%" font-size="220" text-anchor="middle" dominant-baseline="middle">'
            . $emoji . '</text></svg>'
        );
    }

    /** Wordmark logo, always rewritten so it stays consistent with the brand. */
    protected function ensureBrandLogo(string $name, string $color, string $relativePath): void
    {
        $full = public_path($relativePath);
        if (! is_dir(dirname($full))) {
            mkdir(dirname($full), 0755, true);
        }

        file_put_contents($full,
            '<svg xmlns="http://www.w3.org/2000/svg" width="260" height="80">'
            . '<text x="50%" y="50%" font-size="30" font-family="Arial, Helvetica, sans-serif"'
            . ' font-weight="bold" fill="' . $color . '" text-anchor="middle" dominant-baseline="middle">'
            . htmlspecialchars(Str::upper($name)) . '</text></svg>'
        );
    }

    /**
     * The "ধামাকা অফার" hero banner: mascot, gift bag and offer copy, drawn as
     * vector art so it stays sharp at any width and needs no binary asset in a
     * repository that does not track public/.
     *
     * This is a stand-in for real brand artwork — upload the finished banner in
     * Website Management → Banners and the hero uses that instead.
     */
    protected function ensureOfferBanner(string $relativePath): void
    {
        $full = public_path($relativePath);
        if (! is_dir(dirname($full))) {
            mkdir(dirname($full), 0755, true);
        }

        $bn = "'Noto Sans Bengali','Hind Siliguri','Kalpurush',sans-serif";
        $en = "Arial, Helvetica, sans-serif";

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1400" height="380" viewBox="0 0 1400 380" role="img">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#e6007e"/><stop offset="100%" stop-color="#ff2e9a"/>
    </linearGradient>
    <linearGradient id="streak" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="#ffffff" stop-opacity="0"/>
      <stop offset="55%" stop-color="#ffd9ef" stop-opacity=".85"/>
      <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
    </linearGradient>
  </defs>

  <rect width="1400" height="380" fill="url(#bg)"/>

  <!-- light streaks, top right -->
  <g fill="none" stroke="url(#streak)" stroke-linecap="round">
    <path d="M1010 60 Q1210 20 1390 96" stroke-width="10"/>
    <path d="M1040 116 Q1230 74 1396 150" stroke-width="6" opacity=".8"/>
    <path d="M1080 178 Q1250 140 1398 208" stroke-width="4" opacity=".6"/>
    <path d="M1120 250 Q1268 214 1396 274" stroke-width="3" opacity=".45"/>
  </g>
  <circle cx="1150" cy="190" r="205" fill="#ffffff" opacity=".07"/>

  <!-- megaphone -->
  <g transform="translate(74 92)">
    <path d="M4 24 L34 24 L74 0 L74 62 L34 38 L4 38 Z" fill="#1a1a1a"/>
    <rect x="30" y="38" width="14" height="26" rx="4" fill="#1a1a1a"/>
    <g stroke="#1a1a1a" stroke-width="6" stroke-linecap="round">
      <path d="M88 8 L104 0"/><path d="M92 31 L112 31"/><path d="M88 54 L104 62"/>
    </g>
  </g>

  <!-- offer copy -->
  <text x="196" y="126" font-family="{$bn}" font-size="56" font-weight="bold" fill="#ffffff">ধামাকা অফার</text>
  <text x="110" y="204" font-family="{$bn}" font-size="38" fill="#ffffff">১ পিস বেডশিট এর সাথে</text>
  <text x="110" y="256" font-family="{$bn}" font-size="38" fill="#ffffff">একটি আকর্ষণীয় উপহার</text>
  <text x="110" y="308" font-family="{$bn}" font-size="38" fill="#ffffff">একদম ফ্রি</text>

  <!-- mascot -->
  <g>
    <ellipse cx="812" cy="300" rx="92" ry="74" fill="#ffffff"/>
    <ellipse cx="762" cy="352" rx="30" ry="18" fill="#c0006a"/>
    <ellipse cx="862" cy="352" rx="30" ry="18" fill="#c0006a"/>
    <ellipse cx="726" cy="286" rx="22" ry="42" fill="#ffffff" transform="rotate(18 726 286)"/>
    <ellipse cx="900" cy="250" rx="20" ry="46" fill="#ffffff" transform="rotate(-38 900 250)"/>

    <circle cx="812" cy="148" r="96" fill="#ffffff"/>
    <circle cx="742" cy="76" r="32" fill="#c0006a"/>
    <circle cx="882" cy="76" r="32" fill="#c0006a"/>
    <circle cx="742" cy="76" r="16" fill="#ff7ac1"/>
    <circle cx="882" cy="76" r="16" fill="#ff7ac1"/>

    <ellipse cx="775" cy="144" rx="30" ry="36" fill="#e6007e" transform="rotate(-10 775 144)"/>
    <ellipse cx="849" cy="144" rx="30" ry="36" fill="#e6007e" transform="rotate(10 849 144)"/>

    <!-- happy closed eyes -->
    <g stroke="#1a1a1a" stroke-width="6" stroke-linecap="round" fill="none">
      <path d="M762 146 q13 -14 26 0"/>
      <path d="M836 146 q13 -14 26 0"/>
    </g>

    <ellipse cx="812" cy="182" rx="14" ry="10" fill="#1a1a1a"/>
    <path d="M788 198 q24 34 48 0 z" fill="#1a1a1a"/>
    <path d="M800 208 q12 16 24 0 z" fill="#ff5fae"/>
    <circle cx="742" cy="182" r="14" fill="#ff9ed2" opacity=".75"/>
    <circle cx="882" cy="182" r="14" fill="#ff9ed2" opacity=".75"/>
  </g>

  <!-- gift bag -->
  <g>
    <path d="M968 214 q22 -40 44 0" fill="none" stroke="#ffffff" stroke-width="7" stroke-linecap="round"/>
    <path d="M1046 214 q22 -40 44 0" fill="none" stroke="#ffffff" stroke-width="7" stroke-linecap="round"/>
    <rect x="948" y="212" width="162" height="150" rx="10" fill="#e6007e" stroke="#ffffff" stroke-width="4"/>
    <circle cx="1029" cy="266" r="27" fill="#ffffff"/>
    <circle cx="1013" cy="248" r="9" fill="#1a1a1a"/>
    <circle cx="1045" cy="248" r="9" fill="#1a1a1a"/>
    <ellipse cx="1021" cy="264" rx="6" ry="7" fill="#1a1a1a"/>
    <ellipse cx="1037" cy="264" rx="6" ry="7" fill="#1a1a1a"/>
    <ellipse cx="1029" cy="277" rx="5" ry="4" fill="#1a1a1a"/>
    <text x="1029" y="320" font-family="{$en}" font-size="19" font-weight="bold" fill="#ffffff" text-anchor="middle">PANDA VALY</text>
    <text x="1029" y="341" font-family="{$en}" font-size="10" fill="#ffffff" text-anchor="middle" opacity=".9">SHOP MORE, SAVE MORE</text>
  </g>
</svg>
SVG;

        file_put_contents($full, $svg);
    }
}
