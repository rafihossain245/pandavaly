{{--
    Marketing pixels — Meta, GA4 and Google Tag Manager.

    Included from the storefront master only. It must never reach the admin
    layout, or staff clicking through the dashboard would be counted as
    shoppers and poison every conversion figure.

    IDs come from Website Settings; an empty one loads nothing. Pages report
    events by calling window.goeTrack('add_to_cart', payload) with the canonical
    payload App\Services\Tracking builds, and the helper below translates it
    into each platform's own vocabulary.

    Placed high in <head> so PageView fires before the page's CSS and images.
--}}
@php
    $metaPixelId = $setting->facebook_pixel_id ?? null;
    $ga4Id       = $setting->ga4_measurement_id ?? null;
    $gtmId       = $setting->gtm_container_id ?? null;
@endphp

@if ($metaPixelId || $ga4Id || $gtmId)
    @if ($gtmId)
        {{-- Google Tag Manager --}}
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;
            j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{{ $gtmId }}');
        </script>
    @endif

    @if ($ga4Id)
        {{-- Google Analytics 4 --}}
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $ga4Id }}');
        </script>
    @endif

    @if ($metaPixelId)
        {{-- Meta Pixel --}}
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window,document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $metaPixelId }}');
            fbq('track', 'PageView');
        </script>
    @endif

    <script>
    /* ===== SHOP EVENT DISPATCH ===== */
    window.goeTrack = (function () {
        // Which pixels are actually configured. Sniffing window.dataLayer would
        // not do: GA4's own loader creates it too, so a shop without Tag Manager
        // would still get dataLayer pushes nothing consumes.
        var TARGETS = {
            meta: {{ $metaPixelId ? 'true' : 'false' }},
            ga4:  {{ $ga4Id ? 'true' : 'false' }},
            gtm:  {{ $gtmId ? 'true' : 'false' }}
        };

        var CURRENCY = '{{ \App\Services\Tracking::CURRENCY }}';

        // Canonical event -> the name each platform knows it by.
        var EVENTS = {
            view_item:       { meta: 'ViewContent',      ga4: 'view_item' },
            add_to_cart:     { meta: 'AddToCart',        ga4: 'add_to_cart' },
            add_to_wishlist: { meta: 'AddToWishlist',    ga4: 'add_to_wishlist' },
            begin_checkout:  { meta: 'InitiateCheckout', ga4: 'begin_checkout' },
            purchase:        { meta: 'Purchase',         ga4: 'purchase' },
            search:          { meta: 'Search',           ga4: 'search' }
        };

        function eventId() {
            if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
            return 'e-' + Date.now() + '-' + Math.random().toString(16).slice(2);
        }

        function itemsOf(payload) {
            return Array.isArray(payload.items) ? payload.items : [];
        }

        function metaParams(payload) {
            var items = itemsOf(payload);
            var params = {
                currency: payload.currency || CURRENCY,
                value: Number(payload.value || 0)
            };

            if (items.length) {
                params.content_type = 'product';
                params.content_ids = items.map(function (item) { return String(item.id); });
                params.contents = items.map(function (item) {
                    return {
                        id: String(item.id),
                        quantity: Number(item.quantity || 1),
                        item_price: Number(item.price || 0)
                    };
                });
                params.num_items = items.reduce(function (sum, item) {
                    return sum + Number(item.quantity || 1);
                }, 0);

                // Only meaningful for a single product, e.g. a product page.
                if (items.length === 1) {
                    if (items[0].name) params.content_name = items[0].name;
                    if (items[0].category) params.content_category = items[0].category;
                }
            }

            if (payload.search_term) params.search_string = payload.search_term;
            if (payload.transaction_id) params.order_id = String(payload.transaction_id);

            return params;
        }

        function ga4Params(payload) {
            var params = {
                currency: payload.currency || CURRENCY,
                value: Number(payload.value || 0),
                items: itemsOf(payload).map(function (item, index) {
                    var mapped = {
                        item_id: String(item.id),
                        item_name: item.name || 'Product',
                        price: Number(item.price || 0),
                        quantity: Number(item.quantity || 1),
                        index: index
                    };
                    if (item.category) mapped.item_category = item.category;
                    return mapped;
                })
            };

            if (payload.search_term) params.search_term = payload.search_term;
            if (payload.transaction_id) params.transaction_id = String(payload.transaction_id);

            return params;
        }

        return function (event, payload) {
            var names = EVENTS[event];
            if (!names) return;

            payload = payload || {};
            // Callers pass their own ID where a server-side copy of the same
            // event may follow (orders); anything else gets a throwaway one.
            var id = payload.event_id || eventId();

            try {
                if (TARGETS.meta && window.fbq) {
                    fbq('track', names.meta, metaParams(payload), { eventID: id });
                }

                if (TARGETS.ga4 && window.gtag) {
                    gtag('event', names.ga4, ga4Params(payload));
                }

                if (TARGETS.gtm && window.dataLayer) {
                    // GA4 merges successive ecommerce objects, so clear it first
                    // or the previous event's items leak into this one.
                    dataLayer.push({ ecommerce: null });
                    dataLayer.push({ event: names.ga4, event_id: id, ecommerce: ga4Params(payload) });
                }
            } catch (e) {
                // Measurement must never break the page it is measuring.
                if (window.console) console.warn('tracking:', e);
            }
        };
    })();
    </script>

    @if (request()->routeIs('shop') && filled(request('q')))
        {{-- Search lives here rather than in the shop view: the query is on the
             request, so no page needs to know about tracking to report it. --}}
        <script>goeTrack('search', { search_term: @json(request('q')) });</script>
    @endif
@endif
