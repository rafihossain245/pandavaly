@extends('frontEnd.layouts.master')

@section('css')
<style>
    .checkout-page { background: #f5f5f5; min-height: 60vh; padding: 28px 0 50px; }
    .co-breadcrumb { font-size: 13px; color: #666; margin-bottom: 20px; }
    .co-breadcrumb a { color: #333; text-decoration: none; }
    .co-breadcrumb a:hover { text-decoration: underline; }
    .co-breadcrumb span { margin: 0 6px; }

    .co-section { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; margin-bottom: 10px; }
    .co-section-header { padding: 11px 18px; border-bottom: 1px solid #f0f0f0; font-size: 14px; font-weight: 700; color: #111; background: #fafafa; }
    .co-section-body { padding: 16px 18px; }
    .co-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .co-input { width: 100%; border: 1px solid #ddd; border-radius: 3px; padding: 9px 12px; font-size: 14px; color: #111; outline: none; transition: border-color .2s; background: #fff; }
    .co-input:focus { border-color: #2196F3; box-shadow: 0 0 0 3px rgba(33,150,243,.08); }
    .co-required { color: #e53935; }
    .co-error { color: #e53935; font-size: 12px; margin-top: 3px; }

    .order-summary-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; position: sticky; top: 20px; }
    .os-header { padding: 12px 18px; border-bottom: 1px solid #e5e5e5; font-size: 15px; font-weight: 700; color: #111; }
    .os-item { display: flex; justify-content: space-between; align-items: flex-start; padding: 10px 18px; border-bottom: 1px solid #f5f5f5; }
    .os-item-name { font-size: 13px; font-weight: 600; color: #111; margin-bottom: 2px; }
    .os-item-qty { font-size: 12px; color: #888; }
    .os-item-price { font-size: 13px; font-weight: 700; color: #111; white-space: nowrap; }
    .os-totals { padding: 14px 18px; }
    .os-row { display: flex; justify-content: space-between; font-size: 14px; color: #444; margin-bottom: 8px; }
    .os-row.os-grand { font-weight: 700; font-size: 15px; color: #111; border-top: 2px solid #111; padding-top: 10px; margin-top: 4px; }

    .pm-option { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 2px solid #e0e0e0; border-radius: 4px; cursor: pointer; margin-bottom: 8px; transition: border-color .2s, background .2s; }
    .pm-option:hover { background: #fafafa; }
    .pm-option.active { border-color: #111; background: #f7f7f7; }
    .pm-option input[type=radio] { accent-color: #111; width: 16px; height: 16px; flex-shrink: 0; cursor: pointer; }
    .pm-option-text { font-size: 14px; font-weight: 600; color: #333; }
    .pm-option-sub { font-size: 12px; color: #888; }
    .pm-icon { font-size: 20px; color: #555; flex-shrink: 0; }

    .co-submit-btn { display: block; width: 100%; background: #27ae60; color: #fff; text-align: center; padding: 15px; font-weight: 700; font-size: 15px; border: none; cursor: pointer; border-radius: 3px; margin-top: 12px; transition: background .2s; letter-spacing: .3px; }
    .co-submit-btn:hover { background: #219a52; }
    .co-back-link { display: block; text-align: center; margin-top: 12px; font-size: 13px; color: #777; text-decoration: none; }
    .co-back-link:hover { color: #333; }
</style>
@endsection

@section('content')
<section class="checkout-page">
    <div class="container">

        {{-- Breadcrumb --}}
        <div class="co-breadcrumb">
            <a href="{{ route('home') }}"><i class="fas fa-home"></i></a>
            <span>&rarr;</span>
            <a href="{{ route('cart.index') }}">Cart</a>
            <span>&rarr;</span>
            <span>Checkout</span>
        </div>

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

                    {{-- Billing Info --}}
                    <div class="co-section">
                        <div class="co-section-header">
                            <i class="fas fa-building me-2" style="color:#1e40af"></i> Billing Information
                        </div>
                        <div class="co-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="co-label">Company Name <span class="co-required">*</span></label>
                                    <input type="text" name="company_name" class="co-input @error('company_name') is-invalid @enderror"
                                           value="{{ old('company_name', $buyer->business_name) }}" required>
                                    @error('company_name')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">Contact Person <span class="co-required">*</span></label>
                                    <input type="text" name="contact_person" class="co-input @error('contact_person') is-invalid @enderror"
                                           value="{{ old('contact_person') }}" required placeholder="Full name of contact">
                                    @error('contact_person')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">Phone <span class="co-required">*</span></label>
                                    <input type="text" name="phone" class="co-input @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $buyer->phone) }}" required>
                                    @error('phone')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">Email</label>
                                    <input type="email" name="email" class="co-input @error('email') is-invalid @enderror"
                                           value="{{ old('email', $buyer->email) }}">
                                    @error('email')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery Contact --}}
                    <div class="co-section">
                        <div class="co-section-header">
                            <i class="fas fa-user-tag me-2" style="color:#1e40af"></i> Delivery Contact
                            <span style="font-weight:400; color:#888; font-size:12px"> (if different from above)</span>
                        </div>
                        <div class="co-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="co-label">Delivery Contact Name</label>
                                    <input type="text" name="delivery_contact_name" class="co-input"
                                           value="{{ old('delivery_contact_name') }}" placeholder="Receiver name at delivery">
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">Delivery Contact Phone</label>
                                    <input type="text" name="delivery_contact_phone" class="co-input"
                                           value="{{ old('delivery_contact_phone') }}" placeholder="Receiver phone">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="co-section">
                        <div class="co-section-header">
                            <i class="fas fa-map-marker-alt me-2" style="color:#1e40af"></i> Delivery Address
                        </div>
                        <div class="co-section-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="co-label">Address <span class="co-required">*</span></label>
                                    <input type="text" name="address" class="co-input @error('address') is-invalid @enderror"
                                           value="{{ old('address', $buyer->address) }}" required placeholder="Street address, area">
                                    @error('address')<div class="co-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">City</label>
                                    <input type="text" name="city" class="co-input"
                                           value="{{ old('city', $buyer->city) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="co-label">Postal Code</label>
                                    <input type="text" name="postal_code" class="co-input"
                                           value="{{ old('postal_code', $buyer->postal_code) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Details --}}
                    <div class="co-section">
                        <div class="co-section-header">
                            <i class="fas fa-file-alt me-2" style="color:#1e40af"></i> Order Details
                        </div>
                        <div class="co-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="co-label">Purchase Reference No</label>
                                    <input type="text" name="purchase_ref_no" class="co-input"
                                           value="{{ old('purchase_ref_no') }}" placeholder="Your internal PO / reference number">
                                </div>
                                <div class="col-12">
                                    <label class="co-label">Order Note</label>
                                    <textarea name="note" class="co-input" rows="3"
                                              placeholder="Special instructions, delivery notes…">{{ old('note') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ===== RIGHT ===== --}}
                <div class="col-lg-4">

                    {{-- Order Summary --}}
                    <div class="order-summary-card mb-3">
                        <div class="os-header">Order Summary</div>

                        @foreach($cart['items'] as $item)
                        <div class="os-item">
                            <div style="flex:1; min-width:0; padding-right:10px">
                                <div class="os-item-name">{{ $item['name'] }}</div>
                                @if(!empty($item['variant_label']))
                                    <div class="os-item-variant text-muted small">{{ $item['variant_label'] }}</div>
                                @endif
                                <div class="os-item-qty">Qty: {{ $item['qty'] }}</div>
                            </div>
                            <div class="os-item-price">Tk&nbsp;{{ number_format($item['price'] * $item['qty']) }}</div>
                        </div>
                        @endforeach

                        <div class="os-totals">
                            <div class="os-row">
                                <span>Subtotal</span>
                                <span>Tk {{ number_format($cart['total']) }}</span>
                            </div>
                            <div class="os-row">
                                <span>Shipping</span>
                                <span style="color:#888">Tk 0</span>
                            </div>
                            <div class="os-row">
                                <span>VAT</span>
                                <span style="color:#888">—</span>
                            </div>
                            <div class="os-row os-grand">
                                <span>Grand Total</span>
                                <span>Tk {{ number_format($cart['total']) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method --}}
                    <div class="co-section mb-3">
                        <div class="co-section-header">
                            <i class="fas fa-credit-card me-2" style="color:#1e40af"></i> Payment Method
                        </div>
                        <div class="co-section-body">
                            <label class="pm-option active" id="pm-cod-label">
                                <input type="radio" name="payment_method" value="cod" checked
                                       onchange="selectPM(this, 'pm-cod-label')">
                                <i class="fas fa-money-bill-wave pm-icon" style="color:#27ae60"></i>
                                <div>
                                    <div class="pm-option-text">Cash On Delivery</div>
                                    <div class="pm-option-sub">Pay when you receive the order</div>
                                </div>
                            </label>
                            <label class="pm-option" id="pm-bank-label">
                                <input type="radio" name="payment_method" value="bank_transfer"
                                       onchange="selectPM(this, 'pm-bank-label')">
                                <i class="fas fa-university pm-icon" style="color:#1e40af"></i>
                                <div>
                                    <div class="pm-option-text">Bank Transfer</div>
                                    <div class="pm-option-sub">Transfer to our bank account</div>
                                </div>
                            </label>
                            @error('payment_method')<div class="co-error">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="co-submit-btn">
                        <i class="fas fa-check-circle me-2"></i> Submit Order
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
</script>
@endsection
