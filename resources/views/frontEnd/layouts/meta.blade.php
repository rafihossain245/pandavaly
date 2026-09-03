@php
    /**
     * Search-engine and share-card metadata for every storefront page. The
     * template shipped with literal placeholders ("title", "description",
     * "company name"), so a link posted to Facebook or WhatsApp previewed as
     * the word "title" — fatal for a shop that sells through those channels.
     *
     * Titles come from the page's own @section('page-title'); a page with a
     * better share image (a product, say) sets @section('og-image'). Everything
     * else is shop-owned, from Website Settings.
     */
    $metaTitle = trim($__env->yieldContent('page-title')) ?: ($setting->title ?? config('app.name'));
    $metaDescription = trim($__env->yieldContent('meta-description'))
        ?: ($setting?->metaDescription() ?: '');
    // Page override first (a product shares its own photo), then the shop's
    // share image from Website Settings, then the logo as a last resort.
    //
    // The SVG guard lives here rather than in each page: no major platform
    // renders an SVG share card, so a product whose photo is an SVG would post
    // as a blank tile. Falling through to the shop image is always better than
    // sharing nothing.
    $pageImage = trim($__env->yieldContent('og-image'));

    if ($pageImage !== '' && str_ends_with(strtolower(parse_url($pageImage, PHP_URL_PATH) ?? ''), '.svg')) {
        $pageImage = '';
    }

    $metaImage = $pageImage
        ?: ($setting?->shareImage() ? asset($setting->shareImage()) : asset('frontEnd/assets/image/logo.png'));
@endphp
<meta name="description" content="{{ $metaDescription }}">
<meta property="og:type" content="{{ trim($__env->yieldContent('og-type')) ?: 'website' }}">
<meta property="og:site_name" content="{{ $setting->title ?? '' }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:secure_url" content="{{ $metaImage }}">
{{-- Facebook renders a small card on first scrape unless it is told the size
     up front; these match the 1200x630 the settings form asks for. --}}
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $metaTitle }}">
<meta property="og:url" content="{{ url()->current() }}">
<link rel="canonical" href="{{ trim($__env->yieldContent('canonical')) ?: url()->current() }}">
{{-- The catalogue and every heading are Bengali, which is what a share card
     should be tagged as regardless of the admin UI language. --}}
<meta property="og:locale" content="bn_BD">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
