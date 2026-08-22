@extends('frontEnd.layouts.master')

{{-- Reached from the one-page funnel, which sells without accounts, cart or
     category browsing — so this page drops that chrome too. --}}
@section('chrome', 'bare')

@section('css')
<style>
    .track-page { background: #f5f5f5; min-height: 60vh; padding: 28px 0 50px; }
    .track-search-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 6px; padding: 24px; margin-bottom: 24px; }
    .track-order-no { font-size: 20px; font-weight: 800; color: var(--primary); letter-spacing: .3px; }
    .track-step { width: 46px; height: 46px; border-radius: 50%; display: grid; place-items: center; border: 2px solid #e5edf5; background: #f3f7fb; font-weight: 800; color: #9aa8b8; margin: 0 auto; }
    .track-step.active { border-color: var(--primary); background: #fff; color: var(--primary); }
    .track-step.done { border-color: #27ae60; background: #27ae60; color: #fff; }
    .track-step-line { flex: 1; height: 2px; background: #e5edf5; margin-top: 23px; }
    .track-step-line.done { background: #27ae60; }
    .co-section { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; margin-bottom: 16px; }
    .co-section-header { padding: 12px 18px; border-bottom: 1px solid #f0f0f0; font-size: 15px; font-weight: 700; color: #111; background: #fafafa; }
    .co-section-body { padding: 18px; }

    /* ---- Order placed dialog ---- */
    .placed-modal .modal-content { border: none; border-radius: 14px; padding: 6px; }
    .placed-eyebrow { color: var(--primary); font-size: 12px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
    .placed-tick { width: 68px; height: 68px; border-radius: 50%; background: var(--primary); color: #fff; display: grid; place-items: center; font-size: 30px; margin: 14px auto 18px; }
    /* Draws the tick on once, so the dialog reads as a confirmation rather than a
       static panel. Skipped for anyone who asked for less motion. */
    @media (prefers-reduced-motion: no-preference) {
        .placed-tick { animation: placed-pop .35s ease-out; }
        @keyframes placed-pop { from { transform: scale(.6); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    }
    .placed-facts { background: #fbfbfb; border: 1px solid #eee; border-radius: 8px; padding: 6px 16px; margin-top: 20px; text-align: left; }
    .placed-fact { display: flex; justify-content: space-between; gap: 12px; padding: 11px 0; font-size: 14px; }
    .placed-fact + .placed-fact { border-top: 1px dashed #e5e5e5; }
    .placed-fact span:first-child { color: #6b7280; }
    .placed-fact span:last-child { font-weight: 700; color: #111; text-align: right; }
</style>
@endsection

@section('content')
@php
    // The tracker is reached both from checkout and from the nav, so the copy at
    // the top has to work for a shopper who has just paid and one who is chasing
    // an old order.
    $paidSoFar = (float) ($order?->advance_paid ?? 0);
    $amountDue = $order ? max(0, (float) $order->total - $paidSoFar) : 0;
    $paymentStatus = $order?->payment_status;
    $paymentBadge = match ($paymentStatus) {
        'paid' => 'bg-success',
        'partial' => 'bg-warning text-dark',
        default => 'bg-danger',
    };
@endphp
<section class="track-page">
    <div class="container">
        <div class="text-center mb-4">
            <p class="text-orange fw-bold mb-1" style="color:var(--primary);">&bull; LIVE ORDER TRACKING</p>
            <h1 class="fw-bold">Track Your Order</h1>
            @if($order)
                <p class="text-muted mb-1">Real-time updates on your shipment progress</p>
                <p class="text-muted small mb-0">Order Number</p>
                <p class="track-order-no mb-0">#{{ $order->order_no }}</p>
            @else
                <p class="text-muted">Enter your order number and phone number to see real-time status</p>
            @endif
        </div>

        <div class="track-search-card">
            <form method="GET" action="{{ route('track-order') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Order Number</label>
                    <input type="text" name="order_no" class="form-control" placeholder="e.g. SO-20260727..." value="{{ $searchOrderNo }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="Phone used on the order" value="{{ $searchPhone }}" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Track Order</button>
                </div>
            </form>
        </div>

        @if($error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        @if($order)
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="co-section">
                        <div class="co-section-header d-flex justify-content-between align-items-center">
                            <span>Order Timeline</span>
                            <span class="badge bg-warning text-dark text-uppercase">{{ str_replace('_', ' ', $order->status) }}</span>
                        </div>
                        <div class="co-section-body">
                            @if($order->status === 'cancelled')
                                <div class="alert alert-danger mb-0">This order has been cancelled.</div>
                            @else
                                @php $currentStep = $order->trackerStep(); @endphp
                                <div class="d-flex align-items-start">
                                    @foreach($steps as $stepNumber => $label)
                                        <div class="text-center" style="flex: 1;">
                                            <div class="track-step {{ $stepNumber < $currentStep ? 'done' : ($stepNumber === $currentStep ? 'active' : '') }}">
                                                {{ $stepNumber < $currentStep ? '✓' : $stepNumber }}
                                            </div>
                                            <p class="small mt-2 mb-0">{{ $label }}</p>
                                            @if($stepNumber === 1)
                                                <p class="text-muted mb-0" style="font-size:11px;">{{ $order->created_at?->format('d M') }}</p>
                                            @endif
                                        </div>
                                        @if($stepNumber < count($steps))
                                            <div class="track-step-line {{ $stepNumber < $currentStep ? 'done' : '' }}"></div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="co-section">
                        <div class="co-section-header d-flex justify-content-between">
                            <span>Products</span>
                            <span class="text-muted">{{ $order->items->count() }} item(s)</span>
                        </div>
                        <div class="co-section-body">
                            @foreach($order->items as $item)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <div class="fw-semibold">{{ $item->productSku->product->name ?? 'Product' }}</div>
                                        <div class="text-muted small">Qty: {{ $item->qty }}</div>
                                    </div>
                                    <div class="fw-bold">{{ number_format($item->line_total, 2) }} BDT</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="co-section">
                        <div class="co-section-header">Order Summary</div>
                        <div class="co-section-body">
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>{{ number_format($order->subtotal, 2) }} BDT</span></div>
                            @if($order->discount > 0)
                                <div class="d-flex justify-content-between mb-2"><span>Discount</span><span class="text-success">&minus; {{ number_format($order->discount, 2) }} BDT</span></div>
                            @endif
                            <div class="d-flex justify-content-between mb-2"><span>Delivery Fee</span><span>+ {{ number_format($order->shipping_charge, 2) }} BDT</span></div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5"><span>Grand Total</span><span>{{ number_format($order->total, 2) }} BDT</span></div>
                            <div class="d-flex justify-content-between mt-3 text-success"><span>Total Paid</span><span>{{ number_format($paidSoFar, 2) }} BDT</span></div>
                            <div class="d-flex justify-content-between text-danger fw-bold"><span>Amount Due</span><span>{{ number_format($amountDue, 2) }} BDT</span></div>

                            <p class="text-muted small mb-1 mt-3 text-uppercase">Payment Status</p>
                            <span class="badge {{ $paymentBadge }} text-uppercase">{{ str_replace('_', ' ', $paymentStatus ?? 'unknown') }}</span>
                        </div>
                    </div>

                    <div class="co-section">
                        <div class="co-section-header">Shipping Details</div>
                        <div class="co-section-body">
                            <p class="text-muted small mb-1 text-uppercase">Customer</p>
                            <p class="fw-semibold">{{ $order->shipping_name }}</p>
                            <p class="text-muted small mb-1 text-uppercase">Phone</p>
                            <p class="fw-semibold">{{ $order->shipping_phone }}</p>
                            <p class="text-muted small mb-1 text-uppercase">Delivery Address</p>
                            <p class="fw-semibold">{{ $deliveryAddress }}</p>
                            <p class="text-muted small mb-1 text-uppercase">Payment Method</p>
                            <p class="fw-semibold mb-0">{{ Str::upper($order->payment_method ?? '—') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

@if($justPlaced && $order)
    <div class="modal fade placed-modal" id="orderPlacedModal" tabindex="-1" aria-labelledby="orderPlacedTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <span class="placed-eyebrow">&check; Confirmed</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-0 px-4 pb-4">
                    <div class="placed-tick"><i class="fas fa-check"></i></div>
                    <h3 class="fw-bold mb-2" id="orderPlacedTitle">Order Placed!</h3>
                    <p class="text-muted mb-0">Your order has been received and is being processed.</p>

                    <div class="placed-facts">
                        <div class="placed-fact"><span>Order No</span><span>#{{ $order->order_no }}</span></div>
                        <div class="placed-fact"><span>Total</span><span>{{ number_format($order->total, 2) }} BDT</span></div>
                        <div class="placed-fact"><span>Payment</span><span>{{ Str::upper($order->payment_method ?? '—') }}</span></div>
                        <div class="placed-fact"><span>Placed on</span><span>{{ $order->created_at?->format('d M Y') }}</span></div>
                    </div>

                    {{-- The tracker is already behind this dialog, so "view status" only
                         has to dismiss it — no second round trip to the server. --}}
                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        <a href="{{ route('shop') }}" class="btn btn-outline-secondary flex-fill">Continue Shopping</a>
                        <button type="button" class="btn btn-primary flex-fill" data-bs-dismiss="modal">View Order Status</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
@if($justPlaced && $order)
{{-- Purchase belongs here, not on the confirmation view: checkout redirects a
     shopper who has just paid to this tracker, so this is the real thank-you
     page. $justPlaced comes from flashed session data, so it is already gone on
     a refresh; the sessionStorage guard covers the back button and bfcache
     restoring the page from memory. --}}
<script>
    (function () {
        if (!window.goeTrack) return;

        var once = 'purchase.{{ $order->id }}';
        try {
            if (sessionStorage.getItem(once)) return;
            sessionStorage.setItem(once, '1');
        } catch (e) {
            // Private mode can refuse storage; reporting twice beats not at all.
        }

        goeTrack('purchase', @json(\App\Services\Tracking::orderPayload($order)));
    })();
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('orderPlacedModal');
        if (el && window.bootstrap?.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    });
</script>
@endif
@endsection
