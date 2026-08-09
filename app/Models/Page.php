<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A storefront content page (About us, Refund Policy, …). Pages grouped under a
 * PageCategory make up one footer column.
 */
class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'link_url',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(PageCategory::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('position')->orderBy('title');
    }

    /**
     * Where this entry points. A page with its own content resolves to
     * /page/{slug}; one that only exists to link somewhere uses link_url.
     */
    public function url(): string
    {
        if (filled($this->link_url)) {
            return $this->link_url;
        }

        return route('page.show', $this->slug);
    }

    /** A link-only entry has no body of its own to render. */
    public function isLinkOnly(): bool
    {
        return filled($this->link_url);
    }

    public function hasContent(): bool
    {
        return filled(trim(strip_tags((string) $this->content)));
    }

    /**
     * Slugs are the public URL, so they are derived from the title and made
     * unique rather than trusted from the form.
     */
    public static function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: 'page';
        $candidate = $base;
        $suffix = 2;

        while (static::withTrashed()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base . '-' . $suffix++;
        }

        return $candidate;
    }
}
