@extends('frontEnd.layouts.master')

@section('css')
<style>
    .confirm-page { background: #f5f5f5; min-height: 80vh; padding: 0 0 50px; }

    .confirm-banner { background: #fff3cd; border-bottom: 1px solid #ffc107; padding: 12px 20px; font-size: 13px; color: #555; text-align: center; line-height: 1.6; }

    .confirm-card { max-width: 680px; margin: 32px auto 0; background: #fff; border: 1px solid #e5e5e5; border-radius: 6px; overflow: hidden; }

    .confirm-header { padding: 30px 36px 24px; text-align: center; border-bottom: 1px solid #f0f0f0; }
    .confirm-brand { font-size: 22px; font-weight: 800; color: #111; letter-spacing: -0.5px; margin-bottom: 16px; }
    .confirm-brand span { color: #27ae60; }
    .confirm-check { width: 56px; height: 56px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
    .confirm-check i { font-size: 24px; color: #16a34a; }
    .confirm-order-no { font-size: 18px; font-weight: 700; color: #111; margin-bottom: 6px; }
    .confirm-thank { font-size: 22px; font-weight: 800; color: #111; margin-bottom: 8px; }
    .confirm-sub { font-size: 14px; color: #666; }

    .confirm-status-row { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; padding: 20px 36px; border-bottom: 1px solid #f0f0f0; background: #fafafa; }
    .status-pill { display: flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; border: 1px solid; }
    .status-pill.order-s  { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .status-pill.payment-m{ background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
    .status-pill.payment-s{ background: #fee2e2; color: #991b1b; border-color: #fca5a5; }

    .confirm-contact { padding: 24px 36px; text-align: center; border-bottom: 1px solid #f0f0f0; }
    .confirm-contact p { font-size: 14px; color: #444; margin-bottom: 6px; }
    .confirm-contact .phone { font-size: 16px; font-weight: 700; color: #111; }
    .confirm-contact .hours { font-size: 13px; color: #888; }

    .confirm-facebook { padding: 20px 36px; text-align: center; background: #f0f4ff; border-bottom: 1px solid #e0e7ff; }
    .confirm-facebook p { font-size: 13px; color: #555; margin-bottom: 12px; }
    .fb-btn { display: inline-flex; align-items: center; gap: 10px; background: #1877f2; color: #fff; padding: 10px 22px; border-radius: 5px; font-weight: 700; font-size: 14px; text-decoration: none; transition: background .2s; }
    .fb-btn:hover { background: #166fe5; color: #fff; }
    .fb-btn i { font-size: 20px; }

    .confirm-policy { padding: 18px 36px; font-size: 12px; color: #888; line-height: 1.7; text-align: center; border-bottom: 1px solid #f0f0f0; }

    .confirm-account { display: flex; gap: 14px; padding: 18px 36px; background: #f0fdf4; border-bottom: 1px solid #bbf7d0; text-align: left; }
    .confirm-account i { font-size: 20px; color: #16a34a; flex-shrink: 0; margin-top: 2px; }
    .confirm-account strong { font-size: 14px; color: #15803d; }
    .confirm-account p { font-size: 13px; color: #4d7c5a; margin: 4px 0 0; line-height: 1.6; }
    .confirm-account a { color: #15803d; font-weight: 600; }

    .confirm-actions { padding: 24px 36px; display: flex; gap: 12px; justify-content: center; }
    .btn-continue { background: #fff; border: 2px solid #111; color: #111; padding: 12px 28px; font-weight: 700; font-size: 14px; border-radius: 4px; text-decoration: none; transition: all .2s; }
    .btn-continue:hover { background: #111; color: #fff; }
    .btn-track { background: #1e40af; color: #fff; padding: 12px 28px; font-weight: 700; font-size: 14px; border-radius: 4px; text-decoration: none; transition: background .2s; }
    .btn-track:hover { background: #1e3a8a; color: #fff; }
</style>
@endsection

@section('content')
<section class="confirm-page">

    {{-- Warning Banner --}}
    <div class="confirm-banner">
        কারিগরি ত্রুটির কারণে পণ্যের মূল্য অসংগতিপূর্ণ হলে, ইপালশপ কর্তৃপক্ষ অর্ডার বাতিলের অধিকার সংরক্ষণ করে।
        অনুগ্রহ করে কাস্টমার সাপোর্ট এজেন্টের কনফার্মেশন ব্যতীত কোনো ধরনের পেমেন্ট প্রসেড না করার অনুরোধ করা হচ্ছে।
    </div>

    <div class="confirm-card">

        {{-- Header --}}
        <div class="confirm-header">
            <div class="confirm-brand">epal<span>shop</span></div>
            <div class="confirm-check"><i class="fas fa-check"></i></div>
            <div class="confirm-order-no">Order# {{ $order->order_no }}</div>
            <div class="confirm-thank">Thank you for your order!</div>
            <div class="confirm-sub">We will contact you soon to verify the order.</div>
        </div>

        {{-- Status Pills --}}
        <div class="confirm-status-row">
            <div class="status-pill order-s">
                <i class="fas fa-clock"></i> Order Status: Pending Approval
            </div>
            <div class="status-pill payment-m">
                <i class="{{ $order->payment_method === 'bank_transfer' ? 'fas fa-university' : 'fas fa-money-bill-wave' }}"></i>
                Payment: {{ $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Cash On Delivery' }}
            </div>
            <div class="status-pill payment-s">
                <i class="fas fa-circle-xmark"></i> Payment Status: Unpaid
            </div>
        </div>

        {{-- Contact --}}
        <div class="confirm-contact">
            <p>Should you have any questions about your order, feel free to call us on</p>
            <div class="phone">{{ $setting->contact_phone ?? '16793 / 09678002003' }}</div>
            <div class="hours">(9 AM – 7 PM, Sat – Thu)</div>
        </div>

        {{-- Facebook --}}
        <div class="confirm-facebook">
            <p>Have a minute? Like us on Facebook to keep up to date with all our offers and announcements.</p>
            <a href="#" class="fb-btn">
                <i class="fab fa-facebook"></i> Find us on Facebook
            </a>
        </div>

        {{-- Return Policy --}}
        <div class="confirm-policy">
            You may return a product if you find any manufacturing flaw in it. You have 24 hours after delivery
            to return the product. If it does not reach our office within 24 hours it will be considered a warranty issue.
            The product should be returned with the box and all included accessories.
            It has to be claimed by physically coming to the relative branch.
        </div>

        {{-- Account created for a first-time guest --}}
        @if(session('account_created'))
        <div class="confirm-account">
            <i class="fas fa-user-check"></i>
            <div>
                <strong>We created an account for you</strong>
                <p>
                    You are signed in, so this order and every future one is saved in your dashboard.
                    Set a password from
                    <a href="{{ route('buyer.password.edit') }}">Change Password</a>
                    so you can log back in later.
                </p>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="confirm-actions">
            <a href="{{ route('shop') }}" class="btn-continue">Continue Shopping</a>
            @auth('buyer')
                <a href="{{ route('buyer.orders.show', $order) }}" class="btn-track">
                    <i class="fas fa-map-marker-alt me-1"></i> Track Your Order
                </a>
            @else
                {{-- Guest whose order attached to an existing account: the public
                     tracker works without a session. --}}
                <a href="{{ route('track-order', ['order_no' => $order->order_no, 'phone' => $order->shipping_phone]) }}" class="btn-track">
                    <i class="fas fa-map-marker-alt me-1"></i> Track Your Order
                </a>
            @endauth
        </div>

    </div>
</section>
@endsection
