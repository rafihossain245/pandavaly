{{--
    The no-JavaScript fallbacks for the pixels loaded in <head>. They are here
    rather than in tracking.blade.php because <noscript> may only hold link,
    style and meta inside <head> — an iframe or img there is invalid markup.
--}}
@if ($setting?->gtm_container_id)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $setting->gtm_container_id }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

@if ($setting?->facebook_pixel_id)
    <noscript><img height="1" width="1" style="display:none" alt=""
        src="https://www.facebook.com/tr?id={{ $setting->facebook_pixel_id }}&ev=PageView&noscript=1"></noscript>
@endif
