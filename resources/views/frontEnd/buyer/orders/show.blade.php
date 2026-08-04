@extends('frontEnd.layouts.master')

@section('css')
<style>
    .bo-page { background: #f5f5f5; min-height: 60vh; padding: 28px 0 50px; }

    /* Timeline */
    .order-timeline { display: flex; align-items: flex-start; gap: 0; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px; }
    .tl-step { flex: 1; min-width: 90px; text-align: center; position: relative; }
    .tl-step:not(:last-child)::after { content: ''; position: absolute; top: 18px; left: 50%; width: 100%; height: 2px; background: #e0e0e0; z-index: 0; }
    .tl-step.done::after  { background: #27ae60; }
    .tl-dot { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #e0e0e0; background: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; position: relative; z-index: 1; font-size: 14px; color: #aaa; }
    .tl-step.done    .tl-dot { background: #27ae60; border-color: #27ae60; color: #fff; }
    .tl-step.current .tl-dot { background: #1e40af; border-color: #1e40af; color: #fff; box-shadow: 0 0 0 4px rgba(30,64,175,.15); }
    .tl-step.cancelled .tl-dot { background: #dc2626; border-color: #dc2626; color: #fff; }
    .tl-label { font-size: 11px; font-weight: 600; color: #aaa; line-height: 1.3; }
    .tl-step.done    .tl-label { color: #27ae60; }
    .tl-step.current .tl-label { color: #1e40af; }
    .tl-step.cancelled .tl-label { color: #dc2626; }

    /* Cards */
    .bo-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 4px; margin-bottom: 12px; }
    .bo-card-header { padding: 12px 18px; border-bottom: 1px solid #f0f0f0; font-size: 14px; font-weight: 700; color: #111; background: #fafafa; }
    .bo-card-body { padding: 18px; }

    /* Bank payment panel */
    .bank-panel { background: linear-gradient(135deg, #1e3a8a, #1e40af); color: #fff; border-radius: 6px; padding: 22px; margin-bottom: 12px; }
    .bank-panel h4 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
    .bank-panel .amount { font-size: 28px; font-weight: 800; margin: 10px 0 16px; }
    .bank-detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,.15); font-size: 14px; }
    .bank-detail-row:last-child { border-bottom: none; }
    .bank-detail-label { opacity: .75; }
    .bank-detail-value { font-weight: 700; }

    /* Upload form */
    .slip-form { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 18px; }
    .sf-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .sf-input { width: 100%; border: 1px solid #ddd; border-radius: 3px; padding: 9px 12px; font-size: 14px; color: #111; outline: none; background: #fff; }
    .sf-input:focus { border-color: #2196F3; }
    .sf-submit { background: #1e40af; color: #fff; border: none; padding: 11px 24px; font-size: 14px; font-weight: 700; border-radius: 3px; cursor: pointer; transition: background .2s; }
    .sf-submit:hover { background: #1e3a8a; }

    /* Status banners */
    .status-notice { padding: 14px 18px; border-radius: 4px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .sn-pending { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .sn-verified { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .sn-cancelled { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    .sn-info { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }

    .order-table thead th { background: #f8f9fa; font-size: 13px; color: #555; padding: 10px 14px; border-bottom: 2px solid #e9ecef; }
    .order-table tbody td { padding: 10px 14px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
    .sum-row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 14px; color: #444; }
    .sum-row.grand { font-weight: 700; font-size: 16px; color: #111; border-top: 2px solid #111; padding-top: 10px; margin-top: 4px; }
</style>
@endsection

@section('content')
@include('frontEnd.buyer.partials.layout-start')

@php
    $isBankTransfer = $order->payment_method === 'bank_transfer';
    $isCOD          = !$isBankTransfer;

    // COD steps
    $codSteps = [
        ['key' => 'pending',    'icon' => 'fa-clock',           'label' => 'Submitted'],
        ['key' => 'approved',   'icon' => 'fa-check',           'label' => 'Approved'],
        ['key' => 'processing', 'icon' => 'fa-cog',             'label' => 'Processing'],
        ['key' => 'delivered',  'icon' => 'fa-map-marker-alt',  'label' => 'Delivered'],
        ['key' => 'completed',  'icon' => 'fa-flag-checkered',  'label' => 'Completed'],
    ];
    // Bank transfer steps
    $bankSteps = [
        ['key' => 'pending',           'icon' => 'fa-clock',           'label' => 'Submitted'],
        ['key' => 'approved',          'icon' => 'fa-check',           'label' => 'Approved'],
        ['key' => 'payment_requested', 'icon' => 'fa-university',      'label' => 'Pay Now'],
        ['key' => 'payment_verified',  'icon' => 'fa-shield-alt',      'label' => 'Verified'],
        ['key' => 'processing',        'icon' => 'fa-cog',             'label' => 'Processing'],
        ['key' => 'delivered',         'icon' => 'fa-map-marker-alt',  'label' => 'Delivered'],
        ['key' => 'completed',         'icon' => 'fa-flag-checkered',  'label' => 'Completed'],
    ];
    $steps = $isBankTransfer ? $bankSteps : $codSteps;
    $stepKeys = array_column($steps, 'key');
    $currentIdx = array_search($order->status, $stepKeys);
    $isCancelled = $order->status === 'cancelled';
@endphp

<div class="buyer-panel bo-page" style="background:transparent; padding: 0">

    {{-- Back + order title --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('buyer.orders') }}" class="small text-muted">&larr; My Orders</a>
            <h4 class="mt-1 mb-0 fw-bold">{{ $order->order_no }}</h4>
            <small class="text-muted">Placed {{ $order->created_at->format('d M Y, h:i A') }}</small>
        </div>
        @if(in_array($order->status, ['delivered', 'completed']))
            <form action="{{ route('buyer.orders.reorder', $order) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">Reorder</button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="status-notice sn-verified mb-3"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="status-notice sn-cancelled mb-3"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="status-notice sn-pending mb-3"><i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}</div>
    @endif

    {{-- Timeline --}}
    @if(!$isCancelled)
    <div class="bo-card mb-3">
        <div class="bo-card-body">
            <div class="order-timeline">
                @foreach($steps as $i => $step)
                @php
                    $isDone    = $currentIdx !== false && $i < $currentIdx;
                    $isCurrent = $currentIdx !== false && $i === $currentIdx;
                @endphp
                <div class="tl-step {{ $isDone ? 'done' : ($isCurrent ? 'current' : '') }}">
                    <div class="tl-dot">
                        <i class="fas {{ $isDone ? 'fa-check' : $step['icon'] }}"></i>
                    </div>
                    <div class="tl-label">{{ $step['label'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="status-notice sn-cancelled mb-3">
        <i class="fas fa-times-circle"></i> This order has been cancelled.
    </div>
    @endif

    {{-- Bank Transfer Payment Section --}}
    @if($isBankTransfer && !$isCancelled)

        @if($order->status === 'approved' || $order->status === 'payment_requested')

            @if($order->payment_status === 'pending_verification')
                {{-- Slip uploaded, waiting verification --}}
                <div class="status-notice sn-pending">
                    <i class="fas fa-clock"></i>
                    Your payment slip has been submitted. We are verifying it — this usually takes a few hours.
                </div>
            @elseif($order->payment_status === 'verified')
                <div class="status-notice sn-verified">
                    <i class="fas fa-shield-alt"></i> Payment verified! Your order is now being processed.
                </div>
            @else
                {{-- Show bank details + upload form --}}
                <div class="bank-panel">
                    <h4><i class="fas fa-check-circle me-2"></i>Your Order is Approved!</h4>
                    <p style="opacity:.8; font-size:13px; margin-bottom:6px">Please transfer the following amount to complete your order:</p>
                    <div class="amount">৳{{ number_format($order->total, 2) }}</div>
                    <div class="bank-detail-row">
                        <span class="bank-detail-label">Bank</span>
                        <span class="bank-detail-value">{{ \App\Http\Controllers\OrderController::BANK_NAME }}</span>
                    </div>
                    <div class="bank-detail-row">
                        <span class="bank-detail-label">Account Number</span>
                        <span class="bank-detail-value">{{ \App\Http\Controllers\OrderController::BANK_ACCOUNT }}</span>
                    </div>
                    <div class="bank-detail-row">
                        <span class="bank-detail-label">Account Name</span>
                        <span class="bank-detail-value">{{ \App\Http\Controllers\OrderController::BANK_HOLDER }}</span>
                    </div>
                </div>

                {{-- Upload form --}}
                <div class="slip-form">
                    <div class="fw-bold mb-3" style="font-size:15px"><i class="fas fa-upload me-2 text-blue-600"></i>Upload Payment Slip</div>
                    @if($errors->any())
                        <div class="status-notice sn-cancelled mb-3">
                            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                        </div>
                    @endif
                    <form action="{{ route('buyer.orders.upload-slip', $order) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="sf-label">Transaction / Reference ID <span style="color:red">*</span></label>
                                <input type="text" name="transaction_id" class="sf-input" value="{{ old('transaction_id') }}" placeholder="e.g. TXN123456789" required>
                            </div>
                            <div class="col-md-6">
                                <label class="sf-label">Bank Name <span style="color:red">*</span></label>
                                <input type="text" name="bank_name" class="sf-input" value="{{ old('bank_name') }}" placeholder="e.g. DBBL, bKash, Nagad" required>
                            </div>
                            <div class="col-12">
                                <label class="sf-label">Payment Slip (JPG, PNG, PDF — max 4MB) <span style="color:red">*</span></label>
                                <input type="file" name="payment_slip" class="sf-input" accept=".jpg,.jpeg,.png,.pdf" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="sf-submit">
                                    <i class="fas fa-upload me-2"></i>Submit Payment Slip
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

        @elseif($order->payment_status === 'verified')
            <div class="status-notice sn-verified">
                <i class="fas fa-shield-alt"></i>
                Payment has been verified. Transaction ID: <strong>{{ $order->payment_transaction_id }}</strong>
            </div>
        @endif

    @elseif($isCOD && in_array($order->status, ['approved', 'processing', 'delivered']))
        <div class="status-notice sn-info">
            <i class="fas fa-money-bill-wave"></i>
            Cash on Delivery — please keep <strong>৳{{ number_format($order->total, 2) }}</strong> ready when your order arrives.
        </div>
    @endif

    {{-- Items Table --}}
    <div class="bo-card">
        <div class="bo-card-header"><i class="fas fa-list me-2 text-blue-600"></i>Order Items</div>
        <div class="bo-card-body p-0">
            <table class="order-table w-100" style="border-collapse:collapse">
                <thead>
                    <tr>
                        <th style="text-align:left">Product</th>
                        <th style="text-align:center">Qty</th>
                        <th style="text-align:right">Price</th>
                        <th style="text-align:right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->productSku?->product?->name ?? 'Product' }}
                            @if($item->productSku?->variant_label)
                                <div class="text-muted small">{{ $item->productSku->variant_label }}</div>
                            @endif
                        </td>
                        <td style="text-align:center">{{ $item->qty }}</td>
                        <td style="text-align:right">৳{{ number_format($item->price, 2) }}</td>
                        <td style="text-align:right; font-weight:700">৳{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bo-card-body" style="border-top:1px solid #f0f0f0">
            <div class="sum-row"><span>Subtotal</span><span>৳{{ number_format($order->subtotal, 2) }}</span></div>
            <div class="sum-row">
                <span>Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                <span>-৳{{ number_format($order->discount, 2) }}</span>
            </div>
            <div class="sum-row"><span>Tax</span><span>৳{{ number_format($order->tax, 2) }}</span></div>
            <div class="sum-row"><span>Delivery cost</span><span>৳{{ number_format($order->shipping_charge, 2) }}</span></div>
            <div class="sum-row grand"><span>Grand Total</span><span>৳{{ number_format($order->total, 2) }}</span></div>
        </div>
    </div>

    {{-- Delivery Info --}}
    <div class="bo-card">
        <div class="bo-card-header"><i class="fas fa-map-marker-alt me-2 text-blue-600"></i>Delivery Details</div>
        <div class="bo-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small mb-1">Name</div>
                    <div class="fw-bold">{{ $order->shipping_name ?: $order->contact_person }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small mb-1">Phone</div>
                    <div class="fw-bold">{{ $order->shipping_phone }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small mb-1">Shipping Address</div>
                    <div>{{ $order->shipping_address_line }}</div>
                </div>
                <div class="col-12">
                    <div class="text-muted small mb-1">Billing Address</div>
                    <div>
                        {{ $order->billing_address_line }}
                        @if($order->billing_same_as_shipping)
                            <span class="text-muted small">(same as shipping)</span>
                        @endif
                    </div>
                </div>
                @if($order->purchase_ref_no)
                <div class="col-md-6">
                    <div class="text-muted small mb-1">Purchase Ref No</div>
                    <div class="fw-bold">{{ $order->purchase_ref_no }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($order->invoice)
    <div class="text-end">
        <a class="btn btn-outline-primary btn-sm" href="{{ route('buyer.invoices.show', $order->invoice) }}">
            <i class="fas fa-file-invoice me-1"></i> View Invoice
        </a>
    </div>
    @endif

</div>

@include('frontEnd.buyer.partials.layout-end')
@endsection
