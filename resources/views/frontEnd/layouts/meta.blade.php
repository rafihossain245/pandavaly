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
    $metaDescription = $setting?->metaDescription() ?: '';
    $metaImage = trim($__env->yieldContent('og-image'))
        ?: asset($setting->logo_path ?? 'frontEnd/assets/image/logo.png');
@endphp
<meta name="description" content="{{ $metaDescription }}">
<meta property="og:type" content="{{ trim($__env->yieldContent('og-type')) ?: 'website' }}">
<meta property="og:site_name" content="{{ $setting->title ?? '' }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:secure_url" content="{{ $metaImage }}">
<meta property="og:url" content="{{ url()->current() }}">
{{-- The catalogue and every heading are Bengali, which is what a share card
     should be tagged as regardless of the admin UI language. --}}
<meta property="og:locale" content="bn_BD">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $metaTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
