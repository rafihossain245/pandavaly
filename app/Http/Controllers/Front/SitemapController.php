<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => now()->toAtomString(), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('shop'), 'lastmod' => now()->toAtomString(), 'changefreq' => 'daily', 'priority' => '0.9'],
        ])
            ->merge(Product::query()
                ->where('is_active', true)
                ->whereNotNull('slug')
                ->get(['slug', 'updated_at'])
                ->map(fn (Product $product) => [
                    'loc' => route('product.details', $product->slug),
                    'lastmod' => $product->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ]))
            ->merge(Category::query()
                ->where('is_active', true)
                ->whereNotNull('slug')
                ->get(['slug', 'updated_at'])
                ->map(fn (Category $category) => [
                    'loc' => route('shop', ['category' => $category->slug]),
                    'lastmod' => $category->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ]))
            ->merge(Page::active()
                ->whereNull('link_url')
                ->whereNotNull('slug')
                ->get(['slug', 'updated_at'])
                ->map(fn (Page $page) => [
                    'loc' => route('page.show', $page->slug),
                    'lastmod' => $page->updated_at?->toAtomString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.6',
                ]));

        return response()
            ->view('frontEnd.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
