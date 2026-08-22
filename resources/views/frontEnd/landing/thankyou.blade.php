@extends('frontEnd.layouts.landing')
@section('page-title', 'অর্ডার সম্পন্ন হয়েছে')

@section('content')
@php
    $bn = fn ($n) => str_replace(range(0, 9), ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], number_format((float) $n));
@endphp
<section class="lp-section">
    <div class="lp-container" style="max-width: 720px;">
        <div class="lp-thanks">
            <div class="lp-thanks-tick"><i class="fas fa-check"></i></div>
            <h2>{{ $setting->copy('landing_thankyou_heading') }}</h2>
            <p>{{ $setting->copy('landing_thankyou_note') }}</p>
        </div>

        <div class="lp-receipt">
            <div class="lp-receipt-meta">
                <div><span>অর্ডার নম্বর</span><strong>{{ $order->order_no }}</strong></div>
                <div><span>তারিখ</span><strong>{{ $order->created_at->format('d M Y') }}</strong></div>
                <div><span>সর্বমোট</span><strong>৳{{ $bn($order->total) }}</strong></div>
                <div><span>পেমেন্ট</span><strong>{{ $order->payment_method === 'bank_transfer' ? 'ব্যাংক ট্রান্সফার' : 'ক্যাশ অন ডেলিভারি' }}</strong></div>
            </div>

            <table class="lp-receipt-table">
                <thead><tr><th>পণ্য</th><th style="text-align:right">মোট</th></tr></thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->productSku?->product?->name ?? 'পণ্য' }} × {{ $bn($item->qty) }}</td>
                            <td style="text-align:right">৳{{ $bn($item->line_total) }}</td>
                        </tr>
                    @endforeach
                    <tr><td>সাবটোটাল</td><td style="text-align:right">৳{{ $bn($order->subtotal) }}</td></tr>
                    @if($order->discount > 0)
                        <tr><td>ডিসকাউন্ট</td><td style="text-align:right">−৳{{ $bn($order->discount) }}</td></tr>
                    @endif
                    <tr><td>ডেলিভারি চার্জ</td><td style="text-align:right">৳{{ $bn($order->shipping_charge) }}</td></tr>
                    <tr class="is-total"><td>সর্বমোট</td><td style="text-align:right">৳{{ $bn($order->total) }}</td></tr>
                </tbody>
            </table>

            <div class="lp-receipt-address">
                <strong>ডেলিভারি ঠিকানা</strong>
                <p>
                    {{ $order->shipping_name }}<br>
                    {{ $order->shipping_address }}<br>
                    @if($order->district)<span>{{ $order->district->name }}</span><br>@endif
                    {{ $order->shipping_phone }}
                </p>
                @if(filled($order->note))
                    <strong>নোট</strong>
                    <p>{{ $order->note }}</p>
                @endif
            </div>
        </div>

        <div style="text-align:center; margin-top:24px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('track-order', ['order_no' => $order->order_no]) }}" class="lp-btn lp-btn-outline">
                <i class="fas fa-truck-fast"></i> অর্ডার ট্র্যাক করুন
            </a>
            <a href="{{ route('home') }}" class="lp-btn lp-btn-solid">
                <i class="fas fa-cart-shopping"></i> আরও অর্ডার করুন
            </a>
        </div>
    </div>
</section>
@endsection
