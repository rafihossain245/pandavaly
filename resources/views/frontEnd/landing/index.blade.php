@extends('frontEnd.layouts.landing')

@section('header-nav')
    @if($categories->count())
        <nav class="lp-desktop-categories" aria-label="পণ্যের ক্যাটাগরি">
            <a href="{{ route('home') }}#products" data-category-value="" data-category-slug="">সব পণ্য</a>
            @foreach($categories as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}#products"
                   data-category-value="{{ $category->id }}" data-category-slug="{{ $category->slug }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    @endif
@endsection

@section('mobile-drawer')
    <div class="lp-drawer-backdrop" data-category-backdrop></div>
    <aside class="lp-category-drawer" id="lpCategoryDrawer" aria-hidden="true" aria-label="ক্যাটাগরি মেনু">
        <div class="lp-drawer-header">
            <a href="{{ route('home') }}" class="lp-drawer-logo">
                <img src="{{ asset($setting->logo_path ?: 'frontEnd/assets/image/logo.png') }}" alt="{{ $setting->title ?? 'Panda Valy' }}">
            </a>
            <button type="button" data-category-close aria-label="মেনু বন্ধ করুন"><i class="fas fa-xmark"></i></button>
        </div>
        <nav class="lp-drawer-links">
            <a href="{{ route('home') }}#products" data-category-value="" data-category-slug="" class="is-active">
                <span>সব পণ্য</span><i class="fas fa-chevron-right"></i>
            </a>
            @foreach($categories as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}#products"
                   data-category-value="{{ $category->id }}" data-category-slug="{{ $category->slug }}">
                    <span>{{ $category->name }}</span><i class="fas fa-chevron-right"></i>
                </a>
            @endforeach
        </nav>
    </aside>
@endsection

@section('content')
@php
    $priceOf = function ($product) {
        $row = $product->product_prices->first();
        return [
            'now' => (float) ($row->selling_price ?? $product->selling_price ?? 0),
            'was' => (float) ($row->previous_price ?? 0),
        ];
    };
    $money = fn ($amount) => '৳' . number_format((float) $amount);
    $featuredProducts = $gallery->where('is_trending', true)->take(10);
    $catalog = $gallery->mapWithKeys(fn ($product) => [$product->id => [
        'id' => $product->id,
        'name' => $product->name,
        'price' => $priceOf($product)['now'],
        'thumb' => $product->thumbnail ? asset($product->thumbnail) : asset('frontEnd/assets/image/product.jpg'),
    ]]);
@endphp

@php
    $heroBanner = $slides->first();
    $heroSrc = $heroBanner?->image_path ?: 'images/demo/banners/dhamaka-offer.svg';
    $heroSize = \App\Support\ImageFile::dimensions($heroSrc);
@endphp
<section class="lp-hero" id="hero">
    <div class="lp-container">
        <a class="lp-hero-banner" href="#products">
            <img src="{{ asset($heroSrc) }}"
                 alt="{{ $heroBanner->title ?? ($setting->announcement ?: ($setting->title ?? 'Panda Valy')) }}"
                 @if($heroSize) width="{{ $heroSize[0] }}" height="{{ $heroSize[1] }}" @endif
                 fetchpriority="high" decoding="async">
        </a>
    </div>
</section>

@if($featuredProducts->isNotEmpty())
<section class="lp-catalog-section lp-featured-section">
    <div class="lp-container">
        <div class="lp-section-title">
            <div><span>আমাদের পছন্দ</span><h2>ফিচার্ড প্রোডাক্ট</h2></div>
            <a href="#products">সব দেখুন <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="lp-product-grid">
            @foreach($featuredProducts as $p)
                @include('frontEnd.landing.product-card', ['p' => $p])
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="lp-catalog-section lp-all-products" id="products">
    <div class="lp-container">
        <div class="lp-section-title">
            <div><span id="lpCategoryEyebrow">সব ক্যাটাগরি</span><h2 id="lpProductsHeading">সকল প্রোডাক্ট</h2></div>
            <strong class="lp-result-count"><b id="lpResultCount">{{ $gallery->count() }}</b> টি পণ্য</strong>
        </div>
        <div class="lp-product-grid" id="lpAllProducts">
            @foreach($gallery as $p)
                @include('frontEnd.landing.product-card', ['p' => $p])
            @endforeach
        </div>
        <p class="lp-picked-empty" id="lpNoMatch" hidden>এই ক্যাটাগরিতে কোনো পণ্য পাওয়া যায়নি।</p>
    </div>
</section>

<section class="lp-section lp-order" id="order-form">
    <div class="lp-container">
        <div class="lp-head">
            <h2>{{ $setting->copy('landing_order_heading') }}</h2>
            <p>অ্যাকাউন্ট ছাড়াই কয়েক ধাপে অর্ডার সম্পন্ন করুন</p>
            <div class="lp-divider"><i class="fas fa-star"></i></div>
        </div>

        @if($errors->any())
            <div class="lp-alert lp-alert-error">
                <strong>অর্ডারটি সম্পন্ন হয়নি:</strong>
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if(session('error'))<div class="lp-alert lp-alert-error">{{ session('error') }}</div>@endif

        <form method="POST" action="{{ route('landing.order') }}" class="lp-order-card" id="lpOrderForm">
            @csrf
            <div class="lp-order-grid">
                <div class="lp-order-col">
                    <p class="lp-order-legend"><i class="fas fa-bag-shopping"></i> আপনার নির্বাচন</p>
                    <div class="lp-pick" id="lpPicked"></div>
                    <div class="lp-picked-empty" id="lpPickedEmpty">উপরের পণ্য থেকে <strong>কার্টে যোগ করুন</strong> বাটনে চাপুন।</div>

                    <p class="lp-order-legend" style="margin-top:22px;"><i class="fas fa-user"></i> ডেলিভারি তথ্য</p>
                    <div class="lp-field">
                        <label for="shipping_name">আপনার নাম <span class="req">*</span></label>
                        <input type="text" id="shipping_name" name="shipping_name" required value="{{ old('shipping_name') }}"
                               class="lp-input @error('shipping_name') is-bad @enderror" placeholder="আপনার পুরো নাম">
                        @error('shipping_name')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label for="shipping_phone">মোবাইল নম্বর <span class="req">*</span></label>
                        <input type="tel" id="shipping_phone" name="shipping_phone" required inputmode="tel"
                               value="{{ old('shipping_phone') }}" class="lp-input @error('shipping_phone') is-bad @enderror" placeholder="01XXXXXXXXX">
                        @error('shipping_phone')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label>ডেলিভারি এলাকা <span class="req">*</span></label>
                        <div class="lp-delivery-options">
                            <label class="lp-delivery-option">
                                <input type="radio" name="delivery_zone" value="dhaka" {{ old('delivery_zone') === 'dhaka' ? 'checked' : '' }} required>
                                <span>ঢাকার ভিতরে</span>
                            </label>
                            <label class="lp-delivery-option">
                                <input type="radio" name="delivery_zone" value="outside" {{ old('delivery_zone') === 'outside' ? 'checked' : '' }}>
                                <span>ঢাকার বাইরে</span>
                            </label>
                        </div>
                        @error('delivery_zone')<p class="lp-err">{{ $message }}</p>@enderror
                    </div>
                    <div class="lp-field">
                        <label for="district_id">জেলা <span class="req">*</span></label>
                        <select id="district_id" name="district_id" required class="lp-input @error('district_id') is-bad @enderror">
                            <option value="">-- জেলা নির্বাচন করুন --</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->id }}"
                                        data-zone="{{ strtolower($district->name) === 'dhaka' ? 'dhaka' : 'outside' }}"
                                        data-charge="{{ $district->delivery_charge }}"
                                        {{ (string) old('district_id') === (string) $district->id ? 'selected' : '' }}>{{ $district->name }}</option>
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
                        <label for="note">বিশেষ নির্দেশনা <span>(ঐচ্ছিক)</span></label>
                        <input type="text" id="note" name="note" value="{{ old('note') }}" maxlength="90"
                               class="lp-input" placeholder="কালার কোড বা প্রয়োজনীয় তথ্য">
                    </div>
                </div>

                <div class="lp-order-col">
                    <p class="lp-order-legend"><i class="fas fa-receipt"></i> অর্ডার সামারি</p>
                    <div id="lpSummaryLines"><p class="lp-summary-empty">এখনো কোনো পণ্য নির্বাচন করা হয়নি।</p></div>
                    <div class="lp-summary-row"><span>সাবটোটাল</span><strong id="lpSubtotal">৳0</strong></div>
                    <div class="lp-summary-row"><span>ডেলিভারি চার্জ</span><strong id="lpShipping">৳0</strong></div>
                    <div class="lp-summary-row is-total"><span>সর্বমোট</span><strong id="lpTotal">৳0</strong></div>
                    <div class="lp-cod"><strong><i class="fas fa-money-bill-wave"></i> ক্যাশ অন ডেলিভারি</strong><br>{{ $setting->copy('landing_cod_note') }}</div>

                    <label class="lp-terms-check">
                        <input type="checkbox" name="accepted_terms" value="1" required {{ old('accepted_terms') ? 'checked' : '' }}>
                        <span>অর্ডার করে আমি <a href="{{ route('page.show', 'delivery-return-policy') }}" target="_blank" rel="noopener">রিটার্ন নীতি</a> ও <a href="{{ route('page.show', 'terms-and-conditions') }}" target="_blank" rel="noopener">শর্তাবলী</a> গ্রহণ করছি।</span>
                    </label>
                    @error('accepted_terms')<p class="lp-err">অর্ডার করতে নীতিমালা গ্রহণ করুন।</p>@enderror

                    <button type="submit" class="lp-btn lp-btn-solid lp-btn-block lp-btn-lg" id="lpSubmit">
                        <i class="fas fa-lock"></i> অর্ডার নিশ্চিত করুন
                    </button>
                    <p class="lp-order-assurance"><i class="fas fa-shield-heart"></i> আপনার তথ্য নিরাপদ থাকবে</p>
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
    var OLD = @json((object) (old('items') ?: $prefillItems));
    var CATEGORIES = @json($categories->mapWithKeys(fn ($category) => [(string) $category->id => ['name' => $category->name, 'slug' => $category->slug]]));
    var BN = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];

    function bn(value) { return String(value).replace(/\d/g, function (digit) { return BN[digit]; }); }
    function money(value) { return '৳' + Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

    var picks = {};
    Object.keys(OLD || {}).forEach(function (id) {
        var quantity = parseInt(OLD[id], 10) || 0;
        if (quantity > 0 && CATALOG[id]) picks[id] = quantity;
    });

    var $picked = $('#lpPicked');
    var $empty = $('#lpPickedEmpty');
    var $summary = $('#lpSummaryLines');
    var $desktopPill = $('[data-cart-pill]');

    function toast(message) {
        var $toast = $('#lpToast');
        $toast.find('span').text(message);
        $toast.addClass('is-on');
        clearTimeout($toast.data('timer'));
        $toast.data('timer', setTimeout(function () { $toast.removeClass('is-on'); }, 1900));
    }

    function renderOrder() {
        var rows = '', lines = '', subtotal = 0, count = 0;
        Object.keys(picks).forEach(function (id) {
            var item = CATALOG[id], quantity = picks[id];
            if (!item || quantity < 1) return;
            var lineTotal = item.price * quantity;
            subtotal += lineTotal;
            count += quantity;
            rows += '<div class="lp-pick-row is-on" data-id="' + id + '">' +
                '<img class="lp-pick-thumb" src="' + item.thumb + '" alt="">' +
                '<span class="lp-pick-info"><span class="lp-pick-name">' + $('<i>').text(item.name).html() + '</span>' +
                '<span class="lp-pick-price">' + money(item.price) + '</span></span>' +
                '<span class="lp-qty"><button type="button" class="lp-qty-down">−</button>' +
                '<input type="number" name="items[' + id + ']" class="lp-qty-input" value="' + quantity + '" min="1" max="99" readonly>' +
                '<button type="button" class="lp-qty-up">+</button></span>' +
                '<button type="button" class="lp-picked-remove" aria-label="বাদ দিন"><i class="fas fa-xmark"></i></button></div>';
            lines += '<div class="lp-summary-row"><span>' + $('<i>').text(item.name).html() + ' × ' + bn(quantity) +
                '</span><strong>' + money(lineTotal) + '</strong></div>';
        });

        $picked.html(rows);
        $empty.toggle(count === 0);
        $summary.html(lines || '<p class="lp-summary-empty">এখনো কোনো পণ্য নির্বাচন করা হয়নি।</p>');
        var charge = count > 0 ? (parseFloat($('#district_id option:selected').data('charge')) || 0) : 0;
        $('#lpSubtotal').text(money(subtotal));
        $('#lpShipping').text(money(charge));
        $('#lpTotal').text(money(subtotal + charge));
        $('[data-cart-count]').text(bn(count));
        $('.lp-badge').toggle(count > 0);
        $('[data-cart-total]').text(money(subtotal));
        $('.lp-action-cart-copy [data-cart-total]').toggle(count > 0);
        $desktopPill.prop('hidden', count === 0);
        $('[data-order]').toggleClass('is-added', function () { return !!picks[String($(this).data('order'))]; });
    }

    function openOrder(productId) {
        productId = String(productId);
        if (!CATALOG[productId]) return;
        if (!picks[productId]) picks[productId] = 1;
        renderOrder();
        toast(CATALOG[productId].name + ' কার্টে যোগ হয়েছে');
    }

    $(document).on('click', '[data-order]', function () { openOrder($(this).data('order')); });
    $(document).on('click', '.lp-qty-up, .lp-qty-down', function () {
        var id = String($(this).closest('.lp-pick-row').data('id'));
        picks[id] = Math.max(1, Math.min(99, (picks[id] || 1) + ($(this).hasClass('lp-qty-up') ? 1 : -1)));
        renderOrder();
    });
    $(document).on('click', '.lp-picked-remove', function () {
        delete picks[String($(this).closest('.lp-pick-row').data('id'))];
        renderOrder();
    });
    $(document).on('change', '#district_id', renderOrder);

    function filterDistricts() {
        var zone = $('input[name="delivery_zone"]:checked').val();
        var $select = $('#district_id');
        var $placeholder = $select.find('option[value=""]').first();
        var selectedValue = String($select.val() || '');
        var selectedZone = $select.find('option:selected').data('zone');

        // The placeholder must stay enabled. Previously it was included in
        // this filter, so the browser could keep displaying an old, disabled
        // district (for example Habiganj after switching back to Dhaka).
        $placeholder.prop('hidden', false).prop('disabled', false);
        $select.find('option[value]:not([value=""])').each(function () {
            var matches = !zone || String($(this).data('zone')) === String(zone);
            $(this).prop('hidden', !matches).prop('disabled', !matches);
        });

        if (selectedValue && zone && String(selectedZone) !== String(zone)) {
            if (zone === 'dhaka') {
                // Dhaka is the only valid district for the inside-Dhaka zone.
                $select.val($select.find('option[data-zone="dhaka"]:not(:disabled)').first().val());
            } else {
                $select.val('');
                $placeholder.prop('selected', true);
            }
        }
        renderOrder();
    }
    $(document).on('change', 'input[name="delivery_zone"]', filterDistricts);

    var activeCategory = '';
    function filterProducts(updateUrl) {
        var query = ($('#lpSearch').val() || '').trim().toLowerCase();
        var shown = 0;
        $('#lpAllProducts [data-product-card]').each(function () {
            var $card = $(this);
            var match = (!query || String($card.data('search')).indexOf(query) > -1) &&
                (!activeCategory || String($card.data('category')) === String(activeCategory));
            $card.toggle(match);
            if (match) shown++;
        });
        $('.lp-featured-section').toggle(!query && !activeCategory);
        $('#lpNoMatch').prop('hidden', shown !== 0);
        $('#lpResultCount').text(bn(shown));
        var category = CATEGORIES[String(activeCategory)] || null;
        $('#lpCategoryEyebrow').text(category ? 'নির্বাচিত ক্যাটাগরি' : 'সব ক্যাটাগরি');
        $('#lpProductsHeading').text(category ? category.name : (query ? 'সার্চ ফলাফল' : 'সকল প্রোডাক্ট'));
        $('[data-category-value]').removeClass('is-active').filter('[data-category-value="' + activeCategory + '"]').addClass('is-active');
        if (updateUrl && window.history && window.history.replaceState) {
            var url = new URL(window.location.href);
            category ? url.searchParams.set('category', category.slug) : url.searchParams.delete('category');
            window.history.replaceState({}, '', url.pathname + url.search + '#products');
        }
    }

    $(document).on('click', '[data-category-value]', function (event) {
        event.preventDefault();
        activeCategory = String($(this).data('category-value') || '');
        filterProducts(true);
        if (window.closePandaCategoryDrawer) window.closePandaCategoryDrawer();
        document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
    });
    $(document).on('input', '#lpSearch', function () { filterProducts(false); });

    var params = new URLSearchParams(window.location.search);
    var initialSlug = params.get('category');
    if (initialSlug) {
        Object.keys(CATEGORIES).some(function (id) {
            if (CATEGORIES[id].slug !== initialSlug) return false;
            activeCategory = id;
            return true;
        });
    }
    if (params.get('q')) $('#lpSearch').val(params.get('q'));

    $('#lpOrderForm').on('submit', function (event) {
        if (!Object.keys(picks).length) {
            event.preventDefault();
            toast('অন্তত একটি পণ্য নির্বাচন করুন');
            document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
            return;
        }
        $('#lpSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> প্রসেস হচ্ছে...');
    });

    filterDistricts();
    filterProducts(false);
    renderOrder();
    @if($errors->any() || $prefillItems)
        document.getElementById('order-form')?.scrollIntoView();
    @endif
})();
</script>
@endsection
