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
}
