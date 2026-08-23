<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'slug',
        'sku',
        'category_id',
        'sub_category_id',
        'brand_id',
        'unit_id',
        'supplier_id',
        'purchase_price',
        'selling_price',
        'stock_qty',
        'moq',
        'thumbnail',
        'is_trending',
        'is_popular',
        'is_recommended',
        'is_active'
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('home_trending_products');
            Cache::forget('home_popular_products');
            Cache::forget('home_recommended_products');
        });

        static::deleted(function () {
            Cache::forget('home_trending_products');
            Cache::forget('home_popular_products');
            Cache::forget('home_recommended_products');
        });
    }


    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    /**
     * Short, human-quotable product code.
     *
     * Customers read this aloud on the phone and type it into the order form
     * ("কালার কোড: 401"), so it is a 3-digit number, not a slug of the product
     * name. Each category owns a hundred-block (Bedsheets 1xx, Comforters 2xx …)
     * so the code also says which range an item belongs to.
     */
    public static function nextCode(?int $categoryId): string
    {
        $block = 100;

        if ($categoryId) {
            // Position among active categories decides the block, so the numbers
            // stay stable as long as the category order does.
            $position = Category::where('is_active', 1)
                ->orderBy('sort_order')->orderBy('id')
                ->pluck('id')->search($categoryId);

            if ($position !== false) {
                $block = ($position + 1) * 100;
            }
        }

        // This model does not use SoftDeletes, so a plain query already sees
        // every row — including soft-deleted ones whose code the unique index
        // still reserves.
        $taken = static::query()
            ->whereBetween('sku', [(string) $block, (string) ($block + 99)])
            ->pluck('sku')
            ->map(fn ($sku) => (int) $sku)
            ->all();

        for ($code = $block + 1; $code <= $block + 99; $code++) {
            if (! in_array($code, $taken, true)) {
                return (string) $code;
            }
        }

        // Block full — fall back to something unique rather than colliding.
        return (string) (static::max('id') + 1000);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function product_prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
    public function product_images()
    {
        return $this->hasMany(ProductwiseImage::class);
    }
    public function product_specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }
    public function skus()
    {
        return $this->hasMany(ProductSku::class)->orderBy('position')->orderBy('id');
    }

    public function activeSkus()
    {
        return $this->hasMany(ProductSku::class)->where('is_active', true)->orderBy('position')->orderBy('id');
    }

    public function hasVariants(): bool
    {
        return $this->relationLoaded('skus')
            ? $this->skus->isNotEmpty()
            : $this->skus()->exists();
    }

    /**
     * The variant a shopper lands on when the product page opens: first active
     * one that is in stock, else the first active one, so the page always has a
     * price to show even when everything is sold out.
     */
    public function defaultSku(): ?ProductSku
    {
        $skus = $this->relationLoaded('skus') ? $this->skus : $this->skus()->get();
        $active = $skus->where('is_active', true);

        return $active->firstWhere(fn ($sku) => $sku->stock_qty > 0) ?? $active->first();
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function isWishlistedBy(?int $buyerId): bool
    {
        if (! $buyerId) {
            return false;
        }

        return $this->relationLoaded('wishlists')
            ? $this->wishlists->contains('buyer_id', $buyerId)
            : $this->wishlists()->where('buyer_id', $buyerId)->exists();
    }
}
