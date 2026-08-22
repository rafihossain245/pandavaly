<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * Social platforms the storefront can link to, in display order.
     * Single source of truth for the settings form, the header strip and the
     * footer — adding a platform here surfaces it in all three.
     */
    public const SOCIAL_PLATFORMS = [
        'facebook_url' => ['label' => 'Facebook', 'icon' => 'fa-brands fa-facebook-f', 'placeholder' => 'https://facebook.com/yourpage'],
        'instagram_url' => ['label' => 'Instagram', 'icon' => 'fa-brands fa-instagram', 'placeholder' => 'https://instagram.com/yourpage'],
        'twitter_url' => ['label' => 'X (Twitter)', 'icon' => 'fa-brands fa-x-twitter', 'placeholder' => 'https://x.com/yourpage'],
        'youtube_url' => ['label' => 'YouTube', 'icon' => 'fa-brands fa-youtube', 'placeholder' => 'https://youtube.com/@yourchannel'],
        'linkedin_url' => ['label' => 'LinkedIn', 'icon' => 'fa-brands fa-linkedin-in', 'placeholder' => 'https://linkedin.com/company/yourpage'],
        'tiktok_url' => ['label' => 'TikTok', 'icon' => 'fa-brands fa-tiktok', 'placeholder' => 'https://tiktok.com/@yourpage'],
    ];

    /**
     * Marketing pixels the storefront can load, in display order. We store the
     * ID only and build the official loader ourselves, so the pattern is part
     * of the contract: anything that does not match is a pasted snippet or a
     * typo, and is rejected rather than silently shipped to every visitor.
     *
     * Adding a platform here surfaces it in the settings form; it still needs
     * a block in frontEnd/layouts/tracking.blade.php to actually load.
     */
    public const TRACKING_FIELDS = [
        'facebook_pixel_id' => [
            'label' => 'Meta Pixel ID',
            'icon' => 'fa-brands fa-facebook-f',
            'colour' => '#1877f2',
            'placeholder' => '1234567890123456',
            'pattern' => '/^\d{15,16}$/',
            'help' => 'Events Manager → Data sources → your pixel. Digits only, no code.',
            'error' => 'The Meta Pixel ID is 15–16 digits. Copy just the ID from Events Manager, not the whole snippet.',
        ],
        'ga4_measurement_id' => [
            'label' => 'GA4 Measurement ID',
            'icon' => 'fa-solid fa-chart-simple',
            'colour' => '#e8710a',
            'placeholder' => 'G-XXXXXXXXXX',
            'pattern' => '/^G-[A-Z0-9]{4,20}$/',
            'help' => 'Google Analytics → Admin → Data streams. Leave empty if GA4 already fires through Tag Manager.',
            'error' => 'A GA4 Measurement ID looks like G-XXXXXXXXXX.',
        ],
        'gtm_container_id' => [
            'label' => 'Tag Manager container',
            'icon' => 'fa-solid fa-tags',
            'colour' => '#4285f4',
            'placeholder' => 'GTM-XXXXXXX',
            'pattern' => '/^GTM-[A-Z0-9]{4,12}$/',
            'help' => 'Optional. Every shop event is also pushed to the dataLayer, so new tags need no code change.',
            'error' => 'A Tag Manager container ID looks like GTM-XXXXXXX.',
        ],
    ];

    /**
     * Wording on the one-page funnel that belongs to the shop, with the copy
     * the page shipped with as the default. One source of truth for three
     * places: the settings form (labels + placeholders), the funnel and the
     * receipt. Blank in the database means "use the default", so clearing a
     * field restores the original line instead of emptying the page.
     */
    public const LANDING_COPY = [
        'landing_gallery_heading' => [
            'label' => 'Gallery heading',
            'default' => 'প্রোডাক্ট গ্যালারি',
        ],
        'landing_gallery_subheading' => [
            'label' => 'Gallery subheading',
            'default' => 'পছন্দের ডিজাইনে Add To Cart চাপলেই নিচের অর্ডার তালিকায় যুক্ত হবে।',
        ],
        'landing_order_heading' => [
            'label' => 'Order form heading',
            'default' => 'অর্ডার কনফার্ম করতে নিচের ফর্মটি পূরণ করুন',
        ],
        'landing_cod_note' => [
            'label' => 'Cash on delivery note',
            'default' => 'প্রোডাক্ট হাতে পাওয়ার পর মূল্য পরিশোধ করুন।',
        ],
        'landing_thankyou_heading' => [
            'label' => 'Order received heading',
            'default' => 'ধন্যবাদ! আপনার অর্ডারটি গ্রহণ করা হয়েছে।',
        ],
        'landing_thankyou_note' => [
            'label' => 'Order received note',
            'default' => 'আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে যোগাযোগ করবেন।',
        ],
    ];

    protected $fillable = [
        'title',
        'tagline',
        'meta_description',
        'announcement',
        'announcement_enabled',
        'logo_path',
        'logo_light_path',
        'favicon_path',
        'contact_email',
        'contact_phone',
        'address',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'youtube_url',
        'linkedin_url',
        'tiktok_url',
        'play_store_url',
        'app_store_url',
        'facebook_pixel_id',
        'ga4_measurement_id',
        'gtm_container_id',
        'landing_gallery_heading',
        'landing_gallery_subheading',
        'landing_order_heading',
        'landing_cod_note',
        'landing_thankyou_heading',
        'landing_thankyou_note',
    ];

    /**
     * A piece of funnel copy, falling back to the wording the page shipped
     * with. Templates call this instead of reading the column directly so a
     * cleared field can never render as a blank heading.
     */
    public function copy(string $key): string
    {
        $value = trim((string) ($this->{$key} ?? ''));

        return $value !== '' ? $value : (self::LANDING_COPY[$key]['default'] ?? '');
    }

    /**
     * What a share card or a search result should say about the shop. Falls
     * back to the footer tagline, which is the only other one-line description
     * the shop has written, so social previews are never empty.
     */
    public function metaDescription(): string
    {
        foreach ([$this->meta_description, $this->tagline] as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * Only the platforms that actually have a link, ready to render.
     * Returns [['key','label','icon','url'], …].
     */
    public function socialLinks(): array
    {
        $links = [];

        foreach (self::SOCIAL_PLATFORMS as $key => $platform) {
            if (blank($this->{$key})) {
                continue;
            }

            $links[] = [
                'key' => $key,
                'label' => $platform['label'],
                'icon' => $platform['icon'],
                'url' => $this->{$key},
            ];
        }

        return $links;
    }

    public function hasSocialLinks(): bool
    {
        return $this->socialLinks() !== [];
    }

    /**
     * Whether any pixel is configured. Used to skip the tracking partial
     * entirely, so an unconfigured shop ships no marketing JavaScript.
     */
    public function hasTracking(): bool
    {
        foreach (array_keys(self::TRACKING_FIELDS) as $key) {
            if (filled($this->{$key})) {
                return true;
            }
        }

        return false;
    }
}
