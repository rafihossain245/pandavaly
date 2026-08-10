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

    protected $fillable = [
        'title',
        'logo_path',
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
        'facebook_pixel_id',
        'ga4_measurement_id',
        'gtm_container_id',
    ];

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
