@extends('frontEnd.layouts.master')

@section('css')
<style>
    .checkout-page { background: #f5f5f5; min-height: 60vh; padding: 0 0 50px; }

    /* Page header: big title + breadcrumb with the current page highlighted */
    .co-page-head { background: #fff; border-bottom: 1px solid #ececec; padding: 26px 0 22px; margin-bottom: 22px; text-align: center; }
    .co-page-title { font-size: 26px; font-weight: 800; color: #111; margin: 0 0 8px; letter-spacing: -.3px; }
    .co-breadcrumb { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13.5px; color: #777; }
    .co-breadcrumb a { color: #666; text-decoration: none; transition: color .2s; }
    .co-breadcrumb a:hover { color: var(--primary, #f4801f); }
    .co-breadcrumb .co-crumb-sep { color: #c9c9c9; }
    .co-breadcrumb .co-crumb-current { color: var(--primary, #f4801f); font-weight: 700; }

    .co-section { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; margin-bottom: 10px; }
    .co-section-header { padding: 11px 18px; border-bottom: 1px solid #f0f0f0; font-size: 14px; font-weight: 700; color: #111; background: #fafafa; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .co-section-body { padding: 16px 18px; }
    .co-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .co-input { width: 100%; border: 1px solid #ddd; border-radius: 3px; padding: 9px 12px; font-size: 14px; color: #111; outline: none; transition: border-color .2s; background: #fff; }
    .co-input:focus { border-color: #2196F3; box-shadow: 0 0 0 3px rgba(33,150,243,.08); }
    .co-required { color: #e53935; }
    .co-error { color: #e53935; font-size: 12px; margin-top: 3px; }

    /* Phone field with the fixed 88 country prefix */
    .co-phone { display: flex; }
    .co-phone .co-phone-prefix { display: flex; align-items: center; padding: 0 12px; border: 1px solid #ddd; border-right: 0; border-radius: 3px 0 0 3px; background: #f7f7f7; font-size: 14px; font-weight: 600; color: #555; }
    .co-phone .co-input { border-radius: 0 3px 3px 0; }

    /* Same-as-shipping toggle in the Billing Address header */
    .co-toggle { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 600; color: #555; cursor: pointer; margin: 0; }
    .co-toggle input { width: 15px; height: 15px; accent-color: #16a34a; cursor: pointer; }

    /* Select2 restyled to match .co-input */
    .co-field .select2-container { width: 100% !important; }
    .co-field .select2-container--default .select2-selection--single { height: 40px; border: 1px solid #ddd; border-radius: 3px; }
    .co-field .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; padding-left: 12px; font-size: 14px; color: #111; }
    .co-field .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .co-field .select2-container--default .select2-selection--single .select2-selection__placeholder { color: #9aa0a6; }
    .co-field .select2-container--default.select2-container--focus .select2-selection--single,
    .co-field .select2-container--default.select2-container--open .select2-selection--single { border-color: #2196F3; box-shadow: 0 0 0 3px rgba(33,150,243,.08); }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: #f4801f; }
    .select2-dropdown { border-color: #ddd; }
    .select2-search--dropdown .select2-search__field { border: 1px solid #ddd; border-radius: 3px; padding: 6px 8px; }

    /* ----- Order review (left column) ----- */
    .or-item { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f2f2f2; }
    .or-item:last-child { border-bottom: 0; padding-bottom: 0; }
    .or-item:first-child { padding-top: 0; }
    .or-thumb { width: 58px; height: 58px; object-fit: contain; border: 1px solid #eee; border-radius: 6px; background: #fff; flex-shrink: 0; padding: 3px; }
    .or-info { flex: 1; min-width: 0; }
    .or-name { font-size: 14px; font-weight: 600; color: #222; margin-bottom: 6px; }
    .or-variant { font-size: 12px; color: #999; margin-bottom: 4px; }
    .or-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .or-qty { display: inline-flex; align-items: center; border: 1px solid #e2e2e2; border-radius: 20px; overflow: hidden; background: #fafafa; }
    .or-qty button { width: 26px; height: 26px; border: 0; background: transparent; color: #666; font-size: 15px; line-height: 1; cursor: pointer; }
    .or-qty button:hover:not(:disabled) { color: var(--primary, #f4801f); }
    .or-qty button:disabled { opacity: .4; cursor: not-allowed; }
    .or-qty input { width: 34px; border: 0; background: transparent; text-align: center; font-size: 13px; font-weight: 600; color: #111; pointer-events: none; }
    .or-price { font-size: 14px; font-weight: 700; color: #111; }
    .or-remove { width: 30px; height: 30px; border: 1px solid #f3c9c9; background: #fff5f5; color: #e53935; border-radius: 6px; cursor: pointer; flex-shrink: 0; transition: background .2s; }
    .or-remove:hover { background: #ffe5e5; }
    .or-empty { text-align: center; padding: 18px 0; color: #999; font-size: 13.5px; }

    /* ----- Coupon panel ----- */
    .cp-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; padding: 13px 18px; background: #fff; border: 0; cursor: pointer; font-size: 14px; font-weight: 700; color: #111; text-align: left; }
    .cp-head i { color: #999; transition: transform .2s; font-size: 13px; }
    .cp-head[aria-expanded="false"] i { transform: rotate(180deg); }
    .cp-body { padding: 0 18px 16px; }
    .cp-form { display: flex; }
    .cp-form .co-input { border-radius: 3px 0 0 3px; }
    .cp-apply { border: 0; background: var(--primary, #f4801f); color: #fff; font-size: 13.5px; font-weight: 700; padding: 0 18px; border-radius: 0 3px 3px 0; cursor: pointer; white-space: nowrap; }
    .cp-apply:hover { filter: brightness(.94); }
    .cp-apply:disabled { opacity: .65; cursor: default; }
    .cp-msg { font-size: 12.5px; margin-top: 7px; }
    .cp-msg.is-error { color: #e53935; }
    .cp-applied { display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 10px 12px; }
    .cp-applied-code { font-size: 13px; font-weight: 700; color: #15803d; }
    .cp-applied-label { font-size: 12px; color: #4d7c5a; }
    .cp-remove-btn { border: 0; background: transparent; color: #e53935; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: underline; }

    /* ----- Special notes with character counter ----- */
    .sn-counter { font-size: 11.5px; color: #999; margin-top: 6px; }

    .order-summary-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; }
    .os-header { padding: 12px 18px; border-bottom: 1px solid #e5e5e5; font-size: 15px; font-weight: 700; color: #111; }
    .os-totals { padding: 14px 18px; }
    .os-row { display: flex; justify-content: space-between; font-size: 14px; color: #444; margin-bottom: 8px; }
    .os-row.os-grand { font-weight: 700; font-size: 15px; color: #111; border-top: 2px solid #111; padding-top: 10px; margin-top: 4px; }
    .os-row.os-discount span:last-child { color: #16a34a; font-weight: 600; }
    .os-note { font-size: 11.5px; color: #999; margin-top: -4px; }

    .pm-option { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 2px solid #e0e0e0; border-radius: 4px; cursor: pointer; margin-bottom: 8px; transition: border-color .2s, background .2s; }
    .pm-option:hover { background: #fafafa; }
    .pm-option.active { border-color: #111; background: #f7f7f7; }
    .pm-option input[type=radio] { accent-color: #111; width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
    .pm-option-text { font-size: 14px; font-weight: 600; color: #333; }
    .pm-option-sub { font-size: 12px; color: #888; }
    .pm-icon { font-size: 20px; color: #555; flex-shrink: 0; }

    .co-submit-btn { display: block; width: 100%; background: #27ae60; color: #fff; text-align: center; padding: 15px; font-weight: 700; font-size: 15px; border: none; cursor: pointer; border-radius: 3px; margin-top: 12px; transition: background .2s; letter-spacing: .3px; }
    .co-submit-btn:hover { background: #219a52; }
    .co-submit-btn:disabled { background: #b9b9b9; cursor: not-allowed; }
    .co-back-link { display: block; text-align: center; margin-top: 12px; font-size: 13px; color: #777; text-decoration: none; }
    .co-back-link:hover { color: #333; }
</style>
@endsection

@section('content')
<section class="checkout-page">

    {{-- Page header + highlighted breadcrumb --}}
    <div class="co-page-head">
        <div class="container">
            <h1 class="co-page-title">Checkout</h1>
            <nav class="co-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span class="co-crumb-sep">&rsaquo;</span>
                <a href="{{ route('cart.index') }}">Cart</a>
                <span class="co-crumb-sep">&rsaquo;</span>
                <span class="co-crumb-current" aria-current="page">Checkout</span>
            </nav>
        </div>
    </div>

    <div class="container">

        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger mb-3">Please fix the errors below and try again.</div>
        @endif

        <form action="{{ route('checkout.place') }}" method="POST">
            @csrf
            <div class="row g-3 align-items-start">

                {{-- ===== LEFT ===== --}}
                <div class="col-lg-8">

                    {{-- Order review --}}
                    <div class="co-section">
                        <div class="co-section-header">
                            <span><i class="fas fa-clipboard-list me-2" style="color:#1e40af"></i> Order review</span>
                            <a href="{{ route('cart.index') }}" style="font-size:12.5px; font-weight:600; color:#777; text-decoration:none;">Edit cart</a>
                        </div>
                        <div class="co-section-body">
                            <div id="or-items">
                                @foreach($cart['items'] as $key => $item)
                                @php $itemKey = $item['key'] ?? $key; @endphp
                                <div class="or-item" data-cart-key="{{ $itemKey }}">
                                    <img class="or-thumb" src="{{ asset($item['thumbnail'] ?? 'frontEnd/assets/image/product.jpg') }}" alt="{{ $item['name'] }}">
                                    <div class="or-info">
                                        <div class="or-name">{{ $item['name'] }}</div>
                                        @if(!empty($item['variant_label']))
                                            <div class="or-variant">{{ $item['variant_label'] }}</div>
                                        @endif
                                        <div class="or-meta">
                                            <span class="or-qty">
                                                <button type="button" class="or-dec" aria-label="Decrease quantity" {{ $item['qty'] <= 1 ? 'disabled' : '' }}>&minus;</button>
                                                <input type="text" class="or-qty-val" value="{{ $item['qty'] }}" readonly tabindex="-1">
                                                <button type="button" class="or-inc" aria-label="Increase quantity">&plus;</button>
                                            </span>
                                            <span class="or-price">&#2547;{{ number_format($item['price'] * $item['qty'], 2) }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="or-remove" aria-label="Remove item" title="Remove">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            <div class="or-empty d-none" id="or-empty">
                                Your cart is now empty. <a href="{{ route('shop') }}">Continue shopping</a>.
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    <div class="co-section">
                        <div class="co-section-header">
                            <span><i class="fas fa-truck me-2" style="color:#1e40af"></i> Shipping Address</span>
                        </div>
                        <div class="co-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="co-label">Your Full Name <span class="co-required">*</span></label>
                                    <input type="text" name="shipping_name" class="co-input"
                                           value="{{ old('shipping_name', $buyer->business_name) }}" required
                                           placeholder="Your Full Name">
                                    @error('shipping_name')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">Phone Number <span class="co-required">*</span></label>
                                    <div class="co-phone">
                                        <span class="co-phone-prefix">88</span>
                                        <input type="text" name="shipping_phone" class="co-input"
                                               value="{{ old('shipping_phone', $buyer->phone) }}" required
                                               placeholder="017xxxxxxxx">
                                    </div>
                                    @error('shipping_phone')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="co-label">Email</label>
                                    <input type="email" name="shipping_email" class="co-input"
                                           value="{{ old('shipping_email', $buyer->email) }}"
                                           placeholder="example@gmail.com (Optional)">
                                    @error('shipping_email')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="co-label">Address <span class="co-required">*</span></label>
                                    <input type="text" name="shipping_address" class="co-input"
                                           value="{{ old('shipping_address', $buyer->address) }}" required
                                           placeholder="ex: House no. / building / street / area">
                                    @error('shipping_address')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 co-field">
                                    <label class="co-label">District <span class="co-required">*</span></label>
                                    <select name="district_id" id="district_id" class="co-select" data-placeholder="Select District" required>
                                        <option value=""></option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}"
                                                    data-charge="{{ $district->delivery_charge }}"
                                                    {{ (string) old('district_id', $buyer->district_id ?? '') === (string) $district->id ? 'selected' : '' }}>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('district_id')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 co-field">
                                    <label class="co-label">Thana / Upazila</label>
                                    <select name="thana_id" id="thana_id" class="co-select"
                                            data-placeholder="Select Thana (Optional)"
                                            data-district="#district_id"
                                            data-selected="{{ old('thana_id', $buyer->thana_id ?? '') }}">
                                        <option value=""></option>
                                    </select>
                                    @error('thana_id')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Billing Address --}}
                    <div class="co-section">
                        <div class="co-section-header">
                            <span><i class="fas fa-file-invoice me-2" style="color:#1e40af"></i> Billing Address</span>
                            <input type="hidden" name="billing_same_as_shipping" value="0">
                            <label class="co-toggle">
                                <input type="checkbox" id="billing_same" name="billing_same_as_shipping" value="1"
                                       {{ old('billing_same_as_shipping', '1') == '1' ? 'checked' : '' }}>
                                Same as shipping address
                            </label>
                        </div>
                        <div class="co-section-body" id="billing_fields">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="co-label">Your Full Name <span class="co-required">*</span></label>
                                    <input type="text" name="billing_name" class="co-input"
                                           value="{{ old('billing_name') }}" placeholder="Your Full Name">
                                    @error('billing_name')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">Phone Number <span class="co-required">*</span></label>
                                    <div class="co-phone">
                                        <span class="co-phone-prefix">88</span>
                                        <input type="text" name="billing_phone" class="co-input"
                                               value="{{ old('billing_phone') }}" placeholder="017xxxxxxxx">
                                    </div>
                                    @error('billing_phone')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="co-label">Email</label>
                                    <input type="email" name="billing_email" class="co-input"
                                           value="{{ old('billing_email') }}" placeholder="example@gmail.com (Optional)">
                                    @error('billing_email')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 co-field">
                                    <label class="co-label">Country</label>
                                    <select name="billing_country" class="co-select" data-placeholder="Select Country">
                                        <option value="Bangladesh" {{ old('billing_country', 'Bangladesh') === 'Bangladesh' ? 'selected' : '' }}>BANGLADESH</option>
                                    </select>
                                </div>
                                <div class="col-md-4 co-field">
                                    <label class="co-label">District <span class="co-required">*</span></label>
                                    <select name="billing_district_id" id="billing_district_id" class="co-select" data-placeholder="Select District">
                                        <option value=""></option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}"
                                                    {{ (string) old('billing_district_id') === (string) $district->id ? 'selected' : '' }}>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('billing_district_id')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 co-field">
                                    <label class="co-label">Thana / Upazila</label>
                                    <select name="billing_thana_id" id="billing_thana_id" class="co-select"
                                            data-placeholder="Select Thana (Optional)"
                                            data-district="#billing_district_id"
                                            data-selected="{{ old('billing_thana_id') }}">
                                        <option value=""></option>
                                    </select>
                                    @error('billing_thana_id')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="co-label">Address <span class="co-required">*</span></label>
                                    <input type="text" name="billing_address" class="co-input"
                                           value="{{ old('billing_address') }}"
                                           placeholder="ex: House no. / building / street / area">
                                    @error('billing_address')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ===== RIGHT ===== --}}
                <div class="col-lg-4">

                    {{-- Payment Method --}}
                    <div class="co-section mb-3">
                        <div class="co-section-header">
                            <span><i class="fas fa-credit-card me-2" style="color:#1e40af"></i> Payment Method</span>
                        </div>
                        <div class="co-section-body">
                            <label class="pm-option {{ old('payment_method', 'cod') === 'cod' ? 'active' : '' }}" id="pm-cod-label">
                                <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}
                                       onchange="selectPM(this, 'pm-cod-label')">
                                <i class="fas fa-money-bill-wave pm-icon" style="color:#27ae60"></i>
                                <div>
                                    <div class="pm-option-text">Cash On Delivery</div>
                                    <div class="pm-option-sub">Pay when you receive the order</div>
                                </div>
                            </label>
                            <label class="pm-option {{ old('payment_method') === 'bank_transfer' ? 'active' : '' }}" id="pm-bank-label">
                                <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}
                                       onchange="selectPM(this, 'pm-bank-label')">
                                <i class="fas fa-university pm-icon" style="color:#1e40af"></i>
                                <div>
                                    <div class="pm-option-text">Bank Transfer</div>
                                    <div class="pm-option-sub">Transfer to our bank account</div>
                                </div>
                            </label>
                            <label class="pm-option" style="opacity:.55; cursor:not-allowed;">
                                <input type="radio" disabled>
                                <i class="fas fa-globe pm-icon" style="color:#9ca3af"></i>
                                <div>
                                    <div class="pm-option-text">Online Payment <span class="badge bg-secondary" style="font-size:10px; vertical-align:middle;">Coming Soon</span></div>
                                    <div class="pm-option-sub">Card / mobile banking gateway</div>
                                </div>
                            </label>
                            <label class="pm-option" style="opacity:.55; cursor:not-allowed;">
                                <input type="radio" disabled>
                                <i class="fas fa-mobile-alt pm-icon" style="color:#9ca3af"></i>
                                <div>
                                    <div class="pm-option-text">bKash <span class="badge bg-secondary" style="font-size:10px; vertical-align:middle;">Coming Soon</span></div>
                                    <div class="pm-option-sub">Pay via bKash mobile wallet</div>
                                </div>
                            </label>
                            @error('payment_method')<div class="co-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Coupon / gift voucher --}}
                    <div class="co-section mb-3">
                        <button type="button" class="cp-head" id="cp-toggle"
                                aria-expanded="{{ $appliedCoupon ? 'true' : 'true' }}" aria-controls="cp-body">
                            <span>Have any coupon or gift voucher?</span>
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <div class="cp-body" id="cp-body">
                            {{-- Applied state --}}
                            <div class="cp-applied {{ $appliedCoupon ? '' : 'd-none' }}" id="cp-applied">
                                <div>
                                    <div class="cp-applied-code" id="cp-applied-code">{{ $appliedCoupon->code ?? '' }}</div>
                                    <div class="cp-applied-label" id="cp-applied-label">{{ $appliedCoupon->label ?? '' }} applied</div>
                                </div>
                                <button type="button" class="cp-remove-btn" id="cp-remove">Remove</button>
                            </div>

                            {{-- Entry form --}}
                            <div class="{{ $appliedCoupon ? 'd-none' : '' }}" id="cp-entry">
                                <div class="cp-form">
                                    <input type="text" class="co-input" id="cp-code" placeholder="Enter Coupon" autocomplete="off">
                                    <button type="button" class="cp-apply" id="cp-apply">Apply coupon</button>
                                </div>
                            </div>
                            <div class="cp-msg d-none" id="cp-msg"></div>
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="order-summary-card mb-3">
                        <div class="os-totals">
                            <div class="os-row">
                                <span>Sub total</span>
                                <span id="os-subtotal">{{ number_format($cart['total'], 2) }} BDT</span>
                            </div>
                            <div class="os-row os-discount {{ $discount > 0 ? '' : 'd-none' }}" id="os-discount-row">
                                <span>Discount</span>
                                <span id="os-discount">&minus;{{ number_format($discount, 2) }} BDT</span>
                            </div>
                            <div class="os-row">
                                <span>Delivery cost</span>
                                <span id="os-delivery">{{ number_format($deliveryCharge, 2) }} BDT</span>
                            </div>
                            <div class="os-row">
                                <span class="os-note" id="os-delivery-note">Charged by delivery district</span>
                            </div>
                            <div class="os-row os-grand">
                                <span>Total</span>
                                <span id="os-grand">{{ number_format($cart['total'] - $discount + $deliveryCharge, 2) }} BDT</span>
                            </div>
                        </div>
                    </div>

                    {{-- Special notes --}}
                    <div class="co-section mb-3">
                        <div class="co-section-header">
                            <span>Special notes <span style="font-weight:400; color:#888; font-size:12px">(Optional)</span></span>
                        </div>
                        <div class="co-section-body">
                            <textarea name="note" id="co-note" class="co-input" rows="3" maxlength="90">{{ old('note') }}</textarea>
                            <div class="sn-counter"><span id="co-note-count">{{ mb_strlen(old('note') ?? '') }}</span> / 90 characters</div>
                            @error('note')<div class="co-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="co-submit-btn">
                        <i class="fas fa-check-circle me-2"></i> Place Order
                    </button>
                    <a href="{{ route('cart.index') }}" class="co-back-link">
                        <i class="fas fa-arrow-left me-1"></i> Back to Cart
                    </a>

                </div>

            </div>
        </form>

    </div>
</section>
@endsection

@section('scripts')
<script>
function selectPM(radio, labelId) {
    document.querySelectorAll('.pm-option').forEach(function(l) { l.classList.remove('active'); });
    document.getElementById(labelId).classList.add('active');
}

// Runs on ready, deliberately AFTER the theme's script.js ready handler: that one
// does $('.select2').select2(), and Select2's own wrapper span carries the class
// "select2" — initialising here first would let the theme re-init on top of our
// widgets and render them blank.
jQuery(function ($) {
    var thanasByDistrict = @json($thanasByDistrict);
    var fallbackCharge   = {{ (float) \App\Models\District::DEFAULT_DELIVERY_CHARGE }};
    var csrf             = $('meta[name="csrf-token"]').attr('content');

    // Live totals state. The server recomputes all three on submit — these only
    // drive the preview, so they can never decide what the buyer is charged.
    var subtotal = {{ (float) $cart['total'] }};
    var discount = {{ (float) $discount }};
    var delivery = {{ (float) $deliveryCharge }};

    function money(value) {
        return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' BDT';
    }

    /* ---- searchable dropdowns ---- */
    $('.co-select').each(function () {
        var $select = $(this);
        $select.select2({
            width: '100%',
            placeholder: $select.data('placeholder') || 'Select',
            allowClear: false
        });
    });

    /* ---- district -> thana cascade (shipping + billing share this) ---- */
    function bindThanaCascade($thana) {
        var $district = $($thana.data('district'));
        var preselect = String($thana.data('selected') || '');

        function repopulate(keepPreselected) {
            var thanas = thanasByDistrict[$district.val()] || [];
            $thana.empty().append('<option value=""></option>');

            thanas.forEach(function (thana) {
                var $option = $('<option>').val(thana.id).text(thana.name);
                if (keepPreselected && String(thana.id) === preselect) {
                    $option.prop('selected', true);
                }
                $thana.append($option);
            });

            $thana.trigger('change.select2');
        }

        $district.on('change', function () { repopulate(false); });
        repopulate(true);
    }

    $('#thana_id, #billing_thana_id').each(function () { bindThanaCascade($(this)); });

    /* ---- one place that paints the summary ---- */
    var $shippingDistrict = $('#district_id');

    function readDeliveryCharge() {
        var selected = $shippingDistrict.find('option:selected');
        var charge   = selected.val() ? parseFloat(selected.data('charge')) : fallbackCharge;

        return isNaN(charge) ? fallbackCharge : charge;
    }

    function refreshTotals() {
        var selected = $shippingDistrict.find('option:selected');
        delivery = readDeliveryCharge();

        // A coupon can outgrow a shrinking cart, so clamp the preview too.
        var applied = Math.min(discount, subtotal);

        $('#os-subtotal').text(money(subtotal));
        $('#os-delivery').text(money(delivery));
        $('#os-discount').text('−' + money(applied));
        $('#os-discount-row').toggleClass('d-none', applied <= 0);
        $('#os-grand').text(money(Math.max(0, subtotal - applied) + delivery));
        $('#os-delivery-note').text(
            selected.val()
                ? 'Delivery to ' + $.trim(selected.text())
                : 'Select a district to confirm the delivery cost'
        );
    }

    $shippingDistrict.on('change', refreshTotals);
    refreshTotals();

    /* ---- order review: quantity + remove, reusing the cart endpoints ---- */
    function syncCartWidgets(cart, itemsHtml) {
        var count = cart.count || 0;
        var total = Number(cart.total || 0);
        var pretty = total.toLocaleString('en-US');

        $('.total-cart, .cs-count').text(count);
        $('.cs-total-val').text(pretty);
        if (itemsHtml) {
            $('#cs-items-wrap').html(itemsHtml);
        }

        // Keep the theme's floating cart pill in step (same shape as js.blade.php).
        var $floating = $('.floating-cart');
        if ($floating.length) {
            if (count > 0) {
                $floating.show();
                $floating.find('div').eq(1).text(count + ' Items');
                $floating.find('div').eq(2).text('৳' + pretty);
            } else {
                $floating.hide();
            }
        }
    }

    function applyCartResponse(res, $row) {
        var key  = $row.data('cart-key');
        var item = res.cart.items ? res.cart.items[key] : null;

        if (item) {
            $row.find('.or-qty-val').val(item.qty);
            $row.find('.or-dec').prop('disabled', item.qty <= 1);
            $row.find('.or-price').text('৳' + Number(item.price * item.qty).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        } else {
            $row.remove();
        }

        subtotal = Number(res.cart.total || 0);
        // The coupon is revalidated against the new subtotal server-side; a cart
        // edited below the minimum drops it, so re-read it rather than assume.
        refreshCoupon();
        refreshTotals();
        syncCartWidgets(res.cart, res.cart_items_html);

        var empty = $('#or-items .or-item').length === 0;
        $('#or-empty').toggleClass('d-none', !empty);
        $('.co-submit-btn').prop('disabled', empty);
    }

    function cartRequest(url, method, data, $row) {
        $.ajax({ url: url, method: method, data: $.extend({ _token: csrf }, data) })
            .done(function (res) { applyCartResponse(res, $row); })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Could not update your cart.';
                if (window.toastr) { toastr.error(msg); } else { alert(msg); }
            });
    }

    $('#or-items').on('click', '.or-inc, .or-dec', function () {
        var $row = $(this).closest('.or-item');
        var qty  = parseInt($row.find('.or-qty-val').val(), 10) || 1;

        qty = $(this).hasClass('or-inc') ? qty + 1 : Math.max(1, qty - 1);
        cartRequest('{{ route('cart.update') }}', 'POST', { cart_key: $row.data('cart-key'), qty: qty }, $row);
    });

    $('#or-items').on('click', '.or-remove', function () {
        var $row = $(this).closest('.or-item');
        cartRequest('{{ route('cart.remove') }}', 'POST', { cart_key: $row.data('cart-key') }, $row);
    });

    /* ---- coupon / gift voucher ---- */
    var $cpMsg = $('#cp-msg');

    function showCouponMessage(text, isError) {
        $cpMsg.text(text || '').toggleClass('is-error', !!isError).toggleClass('d-none', !text);
    }

    function setCouponApplied(code, label) {
        $('#cp-applied-code').text(code);
        $('#cp-applied-label').text(label ? label + ' applied' : 'applied');
        $('#cp-applied').removeClass('d-none');
        $('#cp-entry').addClass('d-none');
    }

    function clearCouponApplied() {
        $('#cp-applied').addClass('d-none');
        $('#cp-entry').removeClass('d-none');
        $('#cp-code').val('');
    }

    // Re-read the coupon after a cart change: the server may have dropped it.
    function refreshCoupon() {
        if ($('#cp-applied').hasClass('d-none')) {
            return;
        }

        var code = $.trim($('#cp-applied-code').text());
        if (!code) {
            return;
        }

        $.ajax({ url: '{{ route('checkout.coupon.apply') }}', method: 'POST', data: { _token: csrf, code: code } })
            .done(function (res) {
                discount = Number(res.discount || 0);
                refreshTotals();
            })
            .fail(function (xhr) {
                discount = 0;
                clearCouponApplied();
                showCouponMessage((xhr.responseJSON && xhr.responseJSON.message) || 'Your coupon is no longer valid.', true);
                refreshTotals();
            });
    }

    $('#cp-toggle').on('click', function () {
        var expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', expanded ? 'false' : 'true');
        $('#cp-body').slideToggle(150);
    });

    $('#cp-apply').on('click', function () {
        var $btn  = $(this);
        var code = $.trim($('#cp-code').val());

        if (!code) {
            showCouponMessage('Please enter a coupon code.', true);
            return;
        }

        $btn.prop('disabled', true).text('Applying…');
        showCouponMessage('');

        $.ajax({ url: '{{ route('checkout.coupon.apply') }}', method: 'POST', data: { _token: csrf, code: code } })
            .done(function (res) {
                discount = Number(res.discount || 0);
                setCouponApplied(res.code, res.label);
                showCouponMessage(res.message, false);
                refreshTotals();
            })
            .fail(function (xhr) {
                showCouponMessage((xhr.responseJSON && xhr.responseJSON.message) || 'Could not apply this coupon.', true);
            })
            .always(function () {
                $btn.prop('disabled', false).text('Apply coupon');
            });
    });

    $('#cp-code').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#cp-apply').trigger('click');
        }
    });

    $('#cp-remove').on('click', function () {
        $.ajax({ url: '{{ route('checkout.coupon.remove') }}', method: 'POST', data: { _token: csrf, _method: 'DELETE' } })
            .done(function (res) {
                discount = 0;
                clearCouponApplied();
                showCouponMessage(res.message, false);
                refreshTotals();
            })
            .fail(function () { showCouponMessage('Could not remove the coupon.', true); });
    });

    /* ---- special notes counter ---- */
    var $note = $('#co-note');
    $note.on('input', function () {
        $('#co-note-count').text($(this).val().length);
    });

    /* ---- billing address mirrors shipping unless unchecked ---- */
    var $sameToggle    = $('#billing_same');
    var $billingFields = $('#billing_fields');

    function toggleBillingFields() {
        var same = $sameToggle.is(':checked');
        $billingFields.toggle(!same);
        // Disabled inputs are not submitted, so a mirrored billing address
        // never sends half-filled values the server would have to sort out.
        $billingFields.find('input, select, textarea').prop('disabled', same);
    }

    $sameToggle.on('change', toggleBillingFields);
    toggleBillingFields();
});
</script>
@endsection
