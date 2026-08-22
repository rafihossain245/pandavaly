@extends('frontEnd.layouts.landing')

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

    // Everything the funnel can sell, in the shape the picker needs. Emitted
    // once so the gallery, the order form and the floating pill all read from
    // a single source and cannot disagree about price or name.
    $catalog = $gallery->mapWithKeys(fn ($p) => [$p->id => [
        'id' => $p->id,
        'name' => $p->name,
        'price' => $priceOf($p)['now'],
        'thumb' => $p->thumbnail ? asset($p->thumbnail) : asset('frontEnd/assets/image/product.jpg'),
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
        <a class="lp-hero-banner" href="#gallery">
            <img src="{{ $heroBanner?->image_path ? asset($heroBanner->image_path) : asset('images/demo/banners/dhamaka-offer.svg') }}"
                 alt="{{ $heroBanner->title ?? ($setting->announcement ?: ($setting->title ?? 'Panda Valy')) }}">
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
                <div class="lp-gallery-item" data-search="{{ Str::lower($p->name . ' ' . $p->sku) }}">
                    <div class="lp-gallery-media">
                        <img src="{{ $p->thumbnail ? asset($p->thumbnail) : asset('frontEnd/assets/image/product.jpg') }}"
                             alt="{{ $p->name }}" loading="lazy">
                        <span class="lp-gallery-code">Code: {{ $p->sku }}</span>
                    </div>
                    <div class="lp-gallery-body">
                        <span class="lp-gallery-name">{{ $p->name }}</span>
                        <span class="lp-gallery-price">
                            @if($pr['was'] > $pr['now'])<del>৳{{ $bn($pr['was']) }}</del>@endif
                            ৳{{ $bn($pr['now']) }}
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
        <p class="lp-picked-empty" id="lpNoMatch" style="display:none;">এই নামে কোনো ডিজাইন পাওয়া যায়নি।</p>
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
                    <div class="lp-summary-row"><span>সাবটোটাল</span><strong id="lpSubtotal">৳০</strong></div>
                    <div class="lp-summary-row"><span>ডেলিভারি চার্জ</span><strong id="lpShipping">৳০</strong></div>
                    <div class="lp-summary-row is-total"><span>সর্বমোট</span><strong id="lpTotal">৳০</strong></div>

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
@endsection

@section('js')
<script>
(function () {
    var CATALOG = @json($catalog);
    var OLD = @json((object) old('items', []));

    var BN = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    function bn(str) { return String(str).replace(/\d/g, function (d) { return BN[d]; }); }
    function money(n) {
        return '৳' + bn(Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
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

    // Live gallery filter — there is no results page to navigate to.
    $(document).on('input', '#lpSearch', function () {
        var q = $(this).val().trim().toLowerCase(), shown = 0;
        $('#lpGallery .lp-gallery-item').each(function () {
            var hit = !q || ($(this).data('search') || '').indexOf(q) > -1;
            $(this).toggle(hit);
            if (hit) shown++;
        });
        $('#lpNoMatch').toggle(shown === 0);
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
