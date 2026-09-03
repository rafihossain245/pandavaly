@extends('frontEnd.layouts.landing')

{{-- Filters the gallery in place, like the search box beside it — this funnel
     has no category pages to send anyone to. --}}
@section('header-nav')
    @if($categories->count() > 1)
        <div class="lp-cat">
            <i class="fas fa-layer-group"></i>
            <select id="lpCategory" aria-label="ক্যাটাগরি">
                <option value="">সব ক্যাটাগরি</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
@endsection

@section('content')
@php
    $priceOf = function ($p) {
        $row = $p->product_prices->first();
        return [
            'now' => (float) ($row->selling_price ?? $p->selling_price ?? 0),
            'was' => (float) ($row->previous_price ?? 0),
        ];
    };
    $bn = fn ($n) => str_replace(range(0, 9), ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], number_format((float) $n));

    // Money stays in Latin digits. Shoppers compare prices against Facebook ads,
    // SMS and the courier's slip, all of which use them, and a taka figure is
    // read as a number rather than as part of the surrounding Bengali sentence.
    $money = fn ($n) => '৳' . number_format((float) $n);

    // Everything the funnel can sell, in the shape the picker needs. Emitted
    // once so the gallery, the order form and the floating pill all read from
    // a single source and cannot disagree about price or name.
    $catalog = $gallery->mapWithKeys(fn ($p) => [$p->id => [
        'id' => $p->id,
        'name' => $p->name,
        'sku' => $p->sku,
        'price' => $priceOf($p)['now'],
        'was' => $priceOf($p)['was'],
        'thumb' => $p->thumbnail ? asset($p->thumbnail) : asset('frontEnd/assets/image/product.jpg'),
        // Thumbnail first, then any extra shots the shop uploaded, deduplicated
        // so a thumbnail that is also in the image list is not shown twice.
        'images' => collect([$p->thumbnail])
            ->merge($p->product_images->pluck('image_path'))
            ->filter()
            ->unique()
            ->map(fn ($path) => asset($path))
            ->values()
            ->all() ?: [asset('frontEnd/assets/image/product.jpg')],
    ]]);
@endphp

{{-- ============================ HERO ============================ --}}
@php
    // A single full-width offer banner, uploaded in Website Management → Banners.
    // The artwork carries its own headline, so no text is overlaid on top of it.
    $heroBanner = $slides->first();
@endphp
<section class="lp-hero" id="hero">
    <div class="lp-container">
        @php
            $heroSrc = $heroBanner?->image_path ?: 'images/demo/banners/dhamaka-offer.svg';
            $heroSize = \App\Support\ImageFile::dimensions($heroSrc);
        @endphp
        <a class="lp-hero-banner" href="#gallery">
            {{-- The banner is the largest thing above the fold, so it is the LCP
                 element: fetched at high priority, never lazy, and carrying its
                 real pixel size so nothing below it shifts when it arrives. --}}
            <img src="{{ asset($heroSrc) }}"
                 alt="{{ $heroBanner->title ?? ($setting->announcement ?: ($setting->title ?? 'Panda Valy')) }}"
                 @if($heroSize) width="{{ $heroSize[0] }}" height="{{ $heroSize[1] }}" @endif
                 fetchpriority="high" decoding="async">
        </a>
    </div>
</section>

{{-- ========================== GALLERY =========================== --}}
@if($gallery->count())
<section class="lp-section" id="gallery">
    <div class="lp-container">
        {{-- Headings are shop-owned (Website Settings → Landing page); the
             wording the page shipped with is the fallback. --}}
        <div class="lp-head">
            <h2>{{ $setting->copy('landing_gallery_heading') }}</h2>
            <p>{{ $setting->copy('landing_gallery_subheading') }}</p>
            <div class="lp-divider"><i class="fas fa-star"></i></div>
        </div>
        <div class="lp-gallery" id="lpGallery">
            @foreach($gallery as $p)
                @php $pr = $priceOf($p); @endphp
                <div class="lp-gallery-item" data-search="{{ Str::lower($p->name . ' ' . $p->sku) }}"
                     data-category="{{ $p->category_id }}">
                    {{-- Opens the full-screen viewer; a design is bought on the
                         picture, so it has to be examinable at full size. --}}
                    <button type="button" class="lp-gallery-media" data-view="{{ $p->id }}"
                            aria-label="{{ $p->name }} — ছবি দেখুন">
                        <img src="{{ $p->thumbnail ? asset($p->thumbnail) : asset('frontEnd/assets/image/product.jpg') }}"
                             alt="{{ $p->name }}" loading="lazy" decoding="async"
                             width="600" height="600">
                        <span class="lp-gallery-code">Code: {{ $p->sku }}</span>
                        <span class="lp-gallery-zoom"><i class="fas fa-magnifying-glass-plus"></i></span>
                    </button>
                    <div class="lp-gallery-body">
                        <span class="lp-gallery-name">{{ $p->name }}</span>
                        <span class="lp-gallery-price">
                            @if($pr['was'] > $pr['now'])<del>{{ $money($pr['was']) }}</del>@endif
                            {{ $money($pr['now']) }}
                        </span>
                        <div class="lp-gallery-actions">
                            <button type="button" class="lp-mini lp-mini-outline" data-add="{{ $p->id }}">
                                <i class="fas fa-bag-shopping"></i> Add To Cart
                            </button>
                            <button type="button" class="lp-mini lp-mini-solid" data-buy="{{ $p->id }}">
                                <i class="fas fa-bolt"></i> Buy Now
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="lp-picked-empty" id="lpNoMatch" style="display:none;">এই ফিল্টারে কোনো ডিজাইন পাওয়া যায়নি।</p>
    </div>
</section>
@endif

{{-- ========================== REVIEWS =========================== --}}
@php
    // The same admin-managed section the multi-page homepage uses, so reviews
    // are written once under Homepage Sections. active() is what makes the
    // section's on/off switch and its schedule apply to the funnel too.
    $reviewSection = \App\Models\HomepageSection::active()->where('type', 'testimonials')->first();
    $reviews = $reviewSection?->config['items'] ?? [];
@endphp
@if(count($reviews))
<section class="lp-section lp-section-alt">
    <div class="lp-container">
        <div class="lp-head">
            <h2>{{ $reviewSection->heading ?: 'আমাদের কাস্টমাররা কী বলছেন?' }}</h2>
            @if(filled($reviewSection->subheading))
                <p>{{ $reviewSection->subheading }}</p>
            @endif
        </div>
        <div class="lp-rating">
            <div class="lp-stars">★ ★ ★ ★ ★</div>
            <p>{{ $bn(count($reviews)) }}+ কাস্টমার ফিডব্যাক</p>
        </div>
        <div class="lp-reviews">
            @foreach($reviews as $r)
                <div class="lp-review">
                    <div class="lp-review-stars">
                        @for($i = 0; $i < max(1, min(5, (int) ($r['rating'] ?? 5))); $i++)★@endfor
                    </div>
                    <p class="lp-review-body">{{ $r['body'] }}</p>
                    <div class="lp-review-who">
                        <span class="lp-review-avatar">{{ Str::upper(Str::substr($r['name'], 0, 1)) }}</span>
                        <span>
                            <span class="lp-review-name">{{ $r['name'] }}</span>
                            @if(filled($r['role'] ?? null))
                                <span class="lp-review-role">{{ $r['role'] }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ========================= ORDER FORM ========================= --}}
<section class="lp-section lp-order" id="order-form">
    <div class="lp-container">
        <div class="lp-head">
            <h2>{{ $setting->copy('landing_order_heading') }}</h2>
            <div class="lp-divider"><i class="fas fa-star"></i></div>
        </div>

        @if($errors->any())
            <div class="lp-alert lp-alert-error">
                <strong>অর্ডারটি সম্পন্ন হয়নি:</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif
        @if(session('error'))
            <div class="lp-alert lp-alert-error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('landing.order') }}" class="lp-order-card" id="lpOrderForm">
            @csrf
            <div class="lp-order-grid">
                <div class="lp-order-col">
                    <p class="lp-order-legend"><i class="fas fa-bag-shopping"></i> আপনার নির্বাচন</p>
                    {{-- Rows are rendered by JS from the gallery/package picks, so the
                         form, the header badge and the floating pill always agree. --}}
                    <div class="lp-pick" id="lpPicked"></div>
                    <div class="lp-picked-empty" id="lpPickedEmpty">
                        উপরের গ্যালারি থেকে পছন্দের ডিজাইনে <strong>Add To Cart</strong> চাপুন।
                    </div>

                    <p class="lp-order-legend" style="margin-top:22px;"><i class="fas fa-user"></i> আপনার তথ্য</p>
                    <div class="lp-field">
                        <label for="shipping_name">আপনার নাম <span class="req">*</span></label>
                        <input type="text" id="shipping_name" name="shipping_name" required
                               value="{{ old('shipping_name') }}"
                               class="lp-input @error('shipping_name') is-bad @enderror" placeholder="আপনার পুরো নাম">
                        @error('shipping_name')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label for="shipping_phone">মোবাইল নম্বর <span class="req">*</span></label>
                        <input type="tel" id="shipping_phone" name="shipping_phone" required
                               value="{{ old('shipping_phone') }}"
                               class="lp-input @error('shipping_phone') is-bad @enderror" placeholder="01XXXXXXXXX">
                        @error('shipping_phone')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label for="shipping_email">ইমেইল</label>
                        <input type="email" id="shipping_email" name="shipping_email"
                               value="{{ old('shipping_email') }}"
                               class="lp-input @error('shipping_email') is-bad @enderror"
                               placeholder="example@gmail.com (ঐচ্ছিক)">
                        @error('shipping_email')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label for="district_id">জেলা <span class="req">*</span></label>
                        <select id="district_id" name="district_id" required
                                class="lp-input @error('district_id') is-bad @enderror">
                            <option value="">-- জেলা নির্বাচন করুন --</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" data-charge="{{ $d->delivery_charge }}"
                                        {{ (string) old('district_id') === (string) $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('district_id')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label for="shipping_address">সম্পূর্ণ ঠিকানা <span class="req">*</span></label>
                        <textarea id="shipping_address" name="shipping_address" required
                                  class="lp-input @error('shipping_address') is-bad @enderror"
                                  placeholder="বাসা/রোড, এলাকা, থানা">{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label for="note">কালার কোড / নোট</label>
                        <input type="text" id="note" name="note" value="{{ old('note') }}"
                               class="lp-input" placeholder="যেমন: Code 401">
                    </div>
                </div>

                <div class="lp-order-col">
                    <p class="lp-order-legend"><i class="fas fa-receipt"></i> আপনার অর্ডার</p>
                    <div id="lpSummaryLines">
                        <p class="lp-summary-empty">এখনো কোনো পণ্য নির্বাচন করা হয়নি।</p>
                    </div>
                    <div class="lp-summary-row"><span>সাবটোটাল</span><strong id="lpSubtotal">৳0</strong></div>
                    <div class="lp-summary-row"><span>ডেলিভারি চার্জ</span><strong id="lpShipping">৳0</strong></div>
                    <div class="lp-summary-row is-total"><span>সর্বমোট</span><strong id="lpTotal">৳0</strong></div>

                    <div class="lp-cod">
                        <strong>ক্যাশ অন ডেলিভারি</strong><br>
                        {{ $setting->copy('landing_cod_note') }}
                    </div>

                    <button type="submit" class="lp-btn lp-btn-solid lp-btn-block lp-btn-lg" id="lpSubmit">
                        <i class="fas fa-lock"></i> অর্ডার কনফার্ম করুন
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="lp-toast" id="lpToast"><i class="fas fa-circle-check"></i> <span></span></div>

{{-- Full-screen image viewer. Built empty and filled by JS from the same
     CATALOG the order form reads, so the price and the name inside it can
     never disagree with the card that opened it. --}}
<div class="lp-lightbox" id="lpLightbox" role="dialog" aria-modal="true" aria-label="প্রোডাক্ট ছবি" hidden>
    <button type="button" class="lp-lb-x" data-lb-close aria-label="বন্ধ করুন"><i class="fas fa-xmark"></i></button>
    <button type="button" class="lp-lb-arrow is-prev" data-lb-step="-1" aria-label="আগেরটি"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="lp-lb-arrow is-next" data-lb-step="1" aria-label="পরেরটি"><i class="fas fa-chevron-right"></i></button>

    <div class="lp-lb-inner">
        <div class="lp-lb-stage">
            <img id="lpLbImage" src="" alt="">
        </div>
        <div class="lp-lb-thumbs" id="lpLbThumbs"></div>
        <div class="lp-lb-bar">
            <div class="lp-lb-meta">
                <span class="lp-lb-name" id="lpLbName"></span>
                <span class="lp-lb-price" id="lpLbPrice"></span>
            </div>
            <div class="lp-lb-actions">
                <button type="button" class="lp-mini lp-mini-outline" id="lpLbAdd">
                    <i class="fas fa-bag-shopping"></i> Add To Cart
                </button>
                <button type="button" class="lp-mini lp-mini-solid" id="lpLbBuy">
                    <i class="fas fa-bolt"></i> Buy Now
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
(function () {
    var CATALOG = @json($catalog);
    var OLD = @json((object) old('items', []));

    var BN = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    function bn(str) { return String(str).replace(/\d/g, function (d) { return BN[d]; }); }
    // Latin digits, matching the prices printed on the gallery cards. Only the
    // amounts: quantities stay Bengali because they read inside Bengali lines.
    function money(n) {
        return '৳' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // id -> qty. The single source of truth; every view is rendered from it.
    var picks = {};
    Object.keys(OLD || {}).forEach(function (id) {
        var q = parseInt(OLD[id], 10) || 0;
        if (q > 0 && CATALOG[id]) picks[id] = q;
    });

    var $picked = $('#lpPicked'), $empty = $('#lpPickedEmpty'),
        $lines = $('#lpSummaryLines'), $pill = $('[data-cart-pill]');

    function toast(msg) {
        var $t = $('#lpToast');
        $t.find('span').text(msg);
        $t.addClass('is-on');
        clearTimeout($t.data('timer'));
        $t.data('timer', setTimeout(function () { $t.removeClass('is-on'); }, 1900));
    }

    function render() {
        var rows = '', lines = '', subtotal = 0, count = 0;

        Object.keys(picks).forEach(function (id) {
            var item = CATALOG[id], qty = picks[id];
            if (!item || qty < 1) return;

            var line = item.price * qty;
            subtotal += line;
            count += qty;

            rows +=
                '<div class="lp-pick-row is-on" data-id="' + id + '">' +
                    '<img class="lp-pick-thumb" src="' + item.thumb + '" alt="">' +
                    '<span class="lp-pick-info">' +
                        '<span class="lp-pick-name">' + $('<i>').text(item.name).html() + '</span>' +
                        '<span class="lp-pick-price">' + money(item.price) + '</span>' +
                    '</span>' +
                    '<span class="lp-qty">' +
                        '<button type="button" class="lp-qty-down">−</button>' +
                        '<input type="number" name="items[' + id + ']" class="lp-qty-input" value="' + qty + '" min="1" max="99" readonly>' +
                        '<button type="button" class="lp-qty-up">+</button>' +
                    '</span>' +
                    '<button type="button" class="lp-picked-remove" title="বাদ দিন"><i class="fas fa-xmark"></i></button>' +
                '</div>';

            lines +=
                '<div class="lp-summary-row"><span>' + $('<i>').text(item.name).html() +
                ' × ' + bn(qty) + '</span><strong>' + money(line) + '</strong></div>';
        });

        $picked.html(rows);
        $empty.toggle(count === 0);
        $lines.html(lines || '<p class="lp-summary-empty">এখনো কোনো পণ্য নির্বাচন করা হয়নি।</p>');

        var charge = count > 0 ? (parseFloat($('#district_id option:selected').data('charge')) || 0) : 0;
        $('#lpSubtotal').text(money(subtotal));
        $('#lpShipping').text(money(charge));
        $('#lpTotal').text(money(subtotal + charge));

        $('[data-cart-count]').text(bn(count));
        $('[data-cart-total]').text(money(subtotal));
        if ($pill.length) $pill.prop('hidden', count === 0);

        // Reflect membership on the gallery buttons.
        $('[data-add]').each(function () {
            $(this).toggleClass('is-added', !!picks[$(this).data('add')]);
        });
    }

    function add(id, silent) {
        id = String(id);
        if (!CATALOG[id]) return;
        picks[id] = (picks[id] || 0) + 1;
        render();
        if (!silent) toast(CATALOG[id].name + ' যুক্ত হয়েছে');
    }

    $(document).on('click', '[data-add]', function () { add($(this).data('add')); });

    $(document).on('click', '[data-buy]', function () {
        add($(this).data('buy'), true);
        document.getElementById('order-form').scrollIntoView({ behavior: 'smooth' });
    });

    $(document).on('click', '.lp-qty-up, .lp-qty-down', function () {
        var $row = $(this).closest('.lp-pick-row'), id = String($row.data('id'));
        picks[id] = Math.max(1, Math.min(99, (picks[id] || 1) + ($(this).hasClass('lp-qty-up') ? 1 : -1)));
        render();
    });

    $(document).on('click', '.lp-picked-remove', function () {
        delete picks[String($(this).closest('.lp-pick-row').data('id'))];
        render();
    });

    $(document).on('change', '#district_id', render);

    // Live gallery filter — there is no results page to navigate to. Search
    // text and the header's category picker are one filter, applied together,
    // so choosing a category does not silently discard what was typed.
    function filterGallery() {
        var q = ($('#lpSearch').val() || '').trim().toLowerCase(),
            cat = String($('#lpCategory').val() || ''),
            shown = 0;

        $('#lpGallery .lp-gallery-item').each(function () {
            var $item = $(this),
                hit = (!q || ($item.data('search') || '').indexOf(q) > -1)
                    && (!cat || String($item.data('category')) === cat);
            $item.toggle(hit);
            if (hit) shown++;
        });

        $('#lpNoMatch').toggle(shown === 0);
    }

    $(document).on('input', '#lpSearch', filterGallery);

    // A search typed on another page (Track Order, receipt) arrives as ?q=.
    // Seed the box with it and jump to the gallery, so the query is not lost
    // just because the shopper was not on the funnel when they typed it.
    (function () {
        var q = new URLSearchParams(window.location.search).get('q');
        if (!q) return;
        $('#lpSearch').val(q);
        filterGallery();
        var g = document.getElementById('gallery');
        if (g) g.scrollIntoView();
    })();

    $(document).on('change', '#lpCategory', function () {
        filterGallery();
        document.getElementById('gallery').scrollIntoView({ behavior: 'smooth' });
    });

    /* ------------------------- Image viewer ------------------------- */
    // Steps through whatever the gallery is currently showing, so a filtered
    // gallery is a filtered viewer too.
    var $lb = $('#lpLightbox'), lbId = null, lbShot = 0;

    function visibleIds() {
        return $('#lpGallery .lp-gallery-item:visible').find('[data-view]')
            .map(function () { return String($(this).data('view')); }).get();
    }

    function paintViewer() {
        var item = CATALOG[lbId];
        if (!item) return;

        var shots = item.images && item.images.length ? item.images : [item.thumb];
        lbShot = Math.max(0, Math.min(shots.length - 1, lbShot));

        $('#lpLbImage').attr({ src: shots[lbShot], alt: item.name });
        $('#lpLbName').text(item.name + (item.sku ? ' — ' + item.sku : ''));
        $('#lpLbPrice').html(
            (item.was > item.price ? '<del>' + money(item.was) + '</del> ' : '') + money(item.price)
        );

        // A single shot needs no strip; the arrows still move between products.
        var thumbs = '';
        if (shots.length > 1) {
            shots.forEach(function (src, i) {
                thumbs += '<button type="button" class="lp-lb-thumb' + (i === lbShot ? ' is-on' : '') +
                          '" data-lb-shot="' + i + '"><img src="' + src + '" alt=""></button>';
            });
        }
        $('#lpLbThumbs').html(thumbs).toggle(shots.length > 1);

        $('#lpLbAdd').toggleClass('is-added', !!picks[lbId]);
    }

    function openViewer(id) {
        if (!CATALOG[id]) return;
        lbId = String(id);
        lbShot = 0;
        paintViewer();
        $lb.prop('hidden', false);
        $('body').addClass('lp-locked');
    }

    function closeViewer() {
        $lb.prop('hidden', true);
        $('body').removeClass('lp-locked');
    }

    // Within a product while it has more shots, then on to the next product.
    function stepViewer(dir) {
        var item = CATALOG[lbId];
        if (!item) return;

        var shots = item.images && item.images.length ? item.images : [item.thumb],
            next = lbShot + dir;

        if (next >= 0 && next < shots.length) {
            lbShot = next;
            paintViewer();
            return;
        }

        var ids = visibleIds(), at = ids.indexOf(String(lbId));
        if (at === -1 || ids.length < 2) return;

        lbId = ids[(at + dir + ids.length) % ids.length];
        var count = (CATALOG[lbId].images || []).length || 1;
        lbShot = dir < 0 ? count - 1 : 0;
        paintViewer();
    }

    $(document).on('click', '[data-view]', function () { openViewer($(this).data('view')); });
    $(document).on('click', '[data-lb-close]', closeViewer);
    $(document).on('click', '[data-lb-step]', function () { stepViewer(parseInt($(this).data('lb-step'), 10)); });
    $(document).on('click', '[data-lb-shot]', function () { lbShot = parseInt($(this).data('lb-shot'), 10); paintViewer(); });

    // Clicking the backdrop closes; clicking the picture or the bar does not.
    $lb.on('click', function (e) { if (e.target === this) closeViewer(); });

    $(document).on('keydown', function (e) {
        if ($lb.prop('hidden')) return;
        if (e.key === 'Escape') closeViewer();
        if (e.key === 'ArrowRight') stepViewer(1);
        if (e.key === 'ArrowLeft') stepViewer(-1);
    });

    $('#lpLbAdd').on('click', function () { add(lbId); paintViewer(); });
    $('#lpLbBuy').on('click', function () {
        add(lbId, true);
        closeViewer();
        document.getElementById('order-form').scrollIntoView({ behavior: 'smooth' });
    });

    $('#lpOrderForm').on('submit', function (e) {
        if (!Object.keys(picks).length) {
            e.preventDefault();
            toast('অন্তত একটি পণ্য নির্বাচন করুন');
            document.getElementById('gallery').scrollIntoView({ behavior: 'smooth' });
            return;
        }
        $('#lpSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেস হচ্ছে...');
    });

    render();
})();
</script>
@endsection
