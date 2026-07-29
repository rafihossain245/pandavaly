@extends('frontEnd.layouts.master')

@section('css')
<style>
    .track-page { background: #f5f5f5; min-height: 60vh; padding: 28px 0 50px; }
    .track-search-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 6px; padding: 24px; margin-bottom: 24px; }
    .track-step { width: 46px; height: 46px; border-radius: 50%; display: grid; place-items: center; border: 2px solid #e5edf5; background: #f3f7fb; font-weight: 800; color: #9aa8b8; margin: 0 auto; }
    .track-step.active { border-color: #f47b32; background: #fff; color: #f47b32; }
    .track-step.done { border-color: #27ae60; background: #27ae60; color: #fff; }
    .track-step-line { flex: 1; height: 2px; background: #e5edf5; margin-top: 23px; }
    .track-step-line.done { background: #27ae60; }
    .co-section { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; margin-bottom: 16px; }
    .co-section-header { padding: 12px 18px; border-bottom: 1px solid #f0f0f0; font-size: 15px; font-weight: 700; color: #111; background: #fafafa; }
    .co-section-body { padding: 18px; }
</style>
@endsection

@section('content')
<section class="track-page">
    <div class="container">
        <div class="text-center mb-4">
            <p class="text-orange fw-bold mb-1" style="color:#f47b32;">&bull; LIVE ORDER TRACKING</p>
            <h1 class="fw-bold">Track Your Order</h1>
            <p class="text-muted">Enter your order number and phone number to see real-time status</p>
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
                            <span>Order Timeline &mdash; #{{ $order->order_no }}</span>
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
                                    <div class="fw-bold">Tk {{ number_format($item->line_total, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="co-section">
                        <div class="co-section-header">Order Summary</div>
                        <div class="co-section-body">
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>Tk {{ number_format($order->subtotal, 2) }}</span></div>
                            <div class="d-flex justify-content-between mb-2"><span>Discount</span><span>Tk {{ number_format($order->discount, 2) }}</span></div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold fs-5"><span>Grand Total</span><span>Tk {{ number_format($order->total, 2) }}</span></div>
                            <div class="d-flex justify-content-between mt-2 text-danger fw-bold"><span>Amount Due</span><span>Tk {{ number_format(max(0, $order->total - $order->advance_paid), 2) }}</span></div>
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
                            <p class="fw-semibold mb-0">{{ $deliveryAddress }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
