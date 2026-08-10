@extends('layout.app')
@section('meta-information')
    <title>Order {{ $order->order_no }}</title>
@endsection
@section('css')
<style>
    .order-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.08); overflow: hidden; margin-bottom: 16px; }
    .order-card-header { padding: 13px 20px; border-bottom: 1px solid #f0f0f0; font-weight: 700; font-size: 14px; color: #111; background: #fafafa; display: flex; align-items: center; gap: 8px; }
    .order-card-body { padding: 18px 20px; }
    .info-row { display: flex; margin-bottom: 10px; font-size: 14px; }
    .info-label { width: 170px; flex-shrink: 0; color: #777; font-weight: 500; }
    .info-value { color: #111; font-weight: 600; }
    .badge-status { display: inline-block; padding: .3rem .75rem; border-radius: 999px; font-size: .78rem; font-weight: 700; }
    .payment-badge { display: inline-block; padding: .25rem .65rem; border-radius: 5px; font-size: .78rem; font-weight: 700; }
    .pm-cod  { background: #dcfce7; color: #166534; }
    .pm-bank { background: #dbeafe; color: #1e40af; }

    /* Workflow progress */
    .wf-steps { display: flex; margin-bottom: 20px; }
    .wf-step { flex: 1; text-align: center; padding: 10px 4px 8px; font-size: 11px; font-weight: 600; color: #bbb; border-bottom: 3px solid #e5e5e5; }
    .wf-step i { display: block; font-size: 15px; margin-bottom: 4px; }
    .wf-step.done    { color: #27ae60; border-color: #27ae60; }
    .wf-step.current { color: #1e40af; border-color: #1e40af; }
    .wf-step.cancelled { color: #dc2626; border-color: #dc2626; }

    /* Action buttons */
    .action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border: none; border-radius: 5px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity .2s; text-decoration: none; }
    .action-btn:hover { opacity: .88; }
    .btn-approve  { background: #27ae60; color: #fff; }
    .btn-process  { background: #8b5cf6; color: #fff; }
    .btn-request  { background: #f59e0b; color: #fff; }
    .btn-deliver  { background: #0d9488; color: #fff; }
    .btn-complete { background: #16a34a; color: #fff; }
    .btn-cancel   { background: #dc2626; color: #fff; }
    .btn-verify   { background: #1e40af; color: #fff; }

    /* Slip preview */
    .slip-preview { border: 2px dashed #d1d5db; border-radius: 6px; padding: 14px; background: #f9fafb; }
    .slip-preview img { max-width: 100%; max-height: 280px; border-radius: 4px; object-fit: contain; display: block; margin: 0 auto; }

    .items-table thead th { background: #f8f9fa; font-size: 13px; color: #555; padding: 10px 14px; border-bottom: 2px solid #e9ecef; }
    .items-table tbody td { padding: 10px 14px; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
    .sum-row { display: flex; justify-content: space-between; padding: 7px 0; font-size: 14px; color: #444; }
    .sum-row.grand { font-weight: 700; font-size: 16px; color: #111; border-top: 2px solid #111; padding-top: 10px; margin-top: 4px; }

    /* ---- Courier handover ---- */
    .courier-box { margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 9px; padding: 14px 16px; background: #fafafa; }
    .courier-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 10px; }
    .courier-head i { color: #6b7280; margin-right: 5px; }
    .courier-tag { font-size: 11px; font-weight: 600; border-radius: 999px; padding: 3px 9px; }
    .courier-tag-ok { background: #ecfdf5; color: #047857; }
    .courier-tag-warn { background: #fffbeb; color: #b45309; }
    .courier-tag-muted { background: #f3f4f6; color: #6b7280; }
    .courier-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .courier-grid span { display: block; font-size: 11.5px; color: #6b7280; text-transform: uppercase; letter-spacing: .03em; margin-bottom: 2px; }
    .courier-grid strong { font-size: 13.5px; color: #111827; word-break: break-all; }
    .courier-note { font-size: 13px; color: #6b7280; margin: 0 0 10px; }
    .courier-error { font-size: 13px; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 11px; margin: 0 0 10px; }
    .courier-attempts { color: #9ca3af; }
</style>
@endsection
@section('main-content')

@php
    $role        = Str::slug(Auth::user()->getRoleNames()->first());
    $isCancelled = $order->status === 'cancelled';
    $isBankTransfer = $order->payment_method === 'bank_transfer';

    $codSteps  = [
        ['key'=>'pending',   'icon'=>'fa-clock',          'label'=>'Pending'],
        ['key'=>'approved',  'icon'=>'fa-check',          'label'=>'Approved'],
        ['key'=>'processing','icon'=>'fa-cog',            'label'=>'Processing'],
        ['key'=>'delivered', 'icon'=>'fa-map-marker-alt', 'label'=>'Delivered'],
        ['key'=>'completed', 'icon'=>'fa-flag-checkered', 'label'=>'Completed'],
    ];
    $bankSteps = [
        ['key'=>'pending',           'icon'=>'fa-clock',          'label'=>'Pending'],
        ['key'=>'approved',          'icon'=>'fa-check',          'label'=>'Approved'],
        ['key'=>'payment_requested', 'icon'=>'fa-university',     'label'=>'Pay Request'],
        ['key'=>'payment_verified',  'icon'=>'fa-shield-alt',     'label'=>'Pay Verified'],
        ['key'=>'processing',        'icon'=>'fa-cog',            'label'=>'Processing'],
        ['key'=>'delivered',         'icon'=>'fa-map-marker-alt', 'label'=>'Delivered'],
        ['key'=>'completed',         'icon'=>'fa-flag-checkered', 'label'=>'Completed'],
    ];
    $steps      = $isBankTransfer ? $bankSteps : $codSteps;
    $stepKeys   = array_column($steps, 'key');
    $currentIdx = array_search($order->status, $stepKeys);

    $paymentStatusClass = $order->payment_status === 'verified'
        ? 'text-green-700'
        : ($order->payment_status === 'pending_verification' ? 'text-yellow-700' : 'text-red-600');
@endphp

<div style="max-width:980px; margin:0 auto">

    {{-- Back --}}
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('role.orders.index', ['role'=>$role]) }}" class="text-blue-600 text-sm hover:underline">
            <i class="fas fa-arrow-left mr-1"></i> Back to Orders
        </a>
        <span class="text-gray-400 text-sm">{{ $order->created_at->format('d M Y, h:i A') }}</span>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded mb-4 text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-2 rounded mb-4 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Order Header + Actions --}}
    <div class="order-card">
        <div class="order-card-body">

            {{-- Title row --}}
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <h2 class="text-xl font-bold text-gray-900 m-0">{{ $order->order_no }}</h2>
                <span class="badge-status {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
                @if($isBankTransfer)
                    <span class="payment-badge pm-bank"><i class="fas fa-university me-1"></i>Bank Transfer</span>
                @else
                    <span class="payment-badge pm-cod"><i class="fas fa-money-bill-wave me-1"></i>COD</span>
                @endif
                <span class="text-sm font-semibold {{ $paymentStatusClass }}">
                    <i class="fas fa-circle-dot me-1"></i>
                    Payment: {{ ucfirst(str_replace('_', ' ', $order->payment_status ?? 'unpaid')) }}
                </span>
            </div>

            {{-- Workflow progress bar --}}
            @if(!$isCancelled)
            <div class="wf-steps">
                @foreach($steps as $i => $step)
                @php
                    $done    = $currentIdx !== false && $i < $currentIdx;
                    $current = $currentIdx !== false && $i === $currentIdx;
                @endphp
                <div class="wf-step {{ $done ? 'done' : ($current ? 'current' : '') }}">
                    <i class="fas {{ $done ? 'fa-check' : $step['icon'] }}"></i>
                    {{ $step['label'] }}
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded text-sm mb-4">
                <i class="fas fa-times-circle me-1"></i> This order has been cancelled.
            </div>
            @endif

            {{-- Action buttons --}}
            @if(count($transitions) || $canVerifyPayment)
            <div class="flex flex-wrap gap-2">
                {{-- Status transition buttons --}}
                @foreach($transitions as $status => $label)
                <form method="POST"
                      action="{{ route('role.orders.status', ['role'=>$role, 'order'=>$order->id]) }}"
                      onsubmit="return confirm('{{ $status === 'cancelled' ? 'Cancel this order?' : 'Set status to ' . str_replace('_',' ',$status) . '?' }}')">
                    @csrf
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button type="submit" class="action-btn
                        {{ in_array($status,['approved','confirmed']) ? 'btn-approve' : '' }}
                        {{ $status === 'payment_requested' ? 'btn-request' : '' }}
                        {{ $status === 'processing'        ? 'btn-process' : '' }}
                        {{ $status === 'delivered'         ? 'btn-deliver' : '' }}
                        {{ $status === 'completed'         ? 'btn-complete': '' }}
                        {{ $status === 'cancelled'         ? 'btn-cancel'  : '' }}">
                        <i class="fas
                            {{ in_array($status,['approved','confirmed']) ? 'fa-check' : '' }}
                            {{ $status === 'payment_requested' ? 'fa-university'      : '' }}
                            {{ $status === 'processing'        ? 'fa-cog'             : '' }}
                            {{ $status === 'delivered'         ? 'fa-map-marker-alt'  : '' }}
                            {{ $status === 'completed'         ? 'fa-flag-checkered'  : '' }}
                            {{ $status === 'cancelled'         ? 'fa-times'           : '' }}"></i>
                        {{ $label }}
                    </button>
                </form>
                @endforeach

                {{-- Verify Payment button --}}
                @if($canVerifyPayment)
                <form method="POST"
                      action="{{ route('role.orders.verify-payment', ['role'=>$role, 'order'=>$order->id]) }}"
                      onsubmit="return confirm('Verify this payment and proceed the order?')">
                    @csrf
                    <button type="submit" class="action-btn btn-verify">
                        <i class="fas fa-shield-alt"></i> Verify Payment
                    </button>
                </form>
                @endif
            </div>
            @endif

            {{-- Courier handover. Shows the consignment once Steadfast has one,
                 the failure reason when a push was rejected, and a manual button
                 so a failure can be retried without waiting for the cron. --}}
            @php $consignment = $courier['consignment']; @endphp
            <div class="courier-box">
                <div class="courier-head">
                    <span><i class="fas fa-truck-fast"></i> Steadfast Courier</span>
                    @if($courier['driver'] !== 'api')
                        <span class="courier-tag courier-tag-warn">Test mode &mdash; no real shipments</span>
                    @elseif($courier['auto_push'])
                        <span class="courier-tag courier-tag-ok">Auto-send on</span>
                    @else
                        <span class="courier-tag courier-tag-muted">Auto-send off</span>
                    @endif
                </div>

                @if($consignment && $consignment->isAccepted())
                    <div class="courier-grid">
                        <div>
                            <span>Consignment</span>
                            <strong>{{ $consignment->consignment_id }}</strong>
                        </div>
                        <div>
                            <span>Tracking code</span>
                            <strong>{{ $consignment->tracking_code ?? '—' }}</strong>
                        </div>
                        <div>
                            <span>Delivery status</span>
                            <strong>{{ $consignment->statusLabel() }}</strong>
                        </div>
                        <div>
                            <span>Collect on delivery</span>
                            <strong>৳ {{ number_format((float) $consignment->cod_amount, 2) }}</strong>
                        </div>
                        <div>
                            <span>Sent</span>
                            <strong>{{ $consignment->pushed_at?->format('d M Y, h:i A') ?? '—' }}</strong>
                        </div>
                    </div>
                @else
                    @if($consignment && $consignment->last_error)
                        <p class="courier-error">
                            <i class="fas fa-circle-exclamation"></i>
                            Last attempt failed: {{ $consignment->last_error }}
                            <span class="courier-attempts">({{ $consignment->attempts }} {{ Str::plural('attempt', $consignment->attempts) }})</span>
                        </p>
                    @elseif($courier['blocked_reason'])
                        <p class="courier-note">{{ $courier['blocked_reason'] }}</p>
                    @else
                        <p class="courier-note">Not handed to the courier yet.</p>
                    @endif

                    @if(! $courier['configured'] && $courier['driver'] === 'api')
                        <p class="courier-error">
                            <i class="fas fa-key"></i>
                            Steadfast API credentials are missing — add STEADFAST_API_KEY and STEADFAST_SECRET_KEY.
                        </p>
                    @endif

                    @unless($courier['blocked_reason'])
                        <form method="POST"
                              action="{{ route('role.orders.send-to-courier', ['role'=>$role, 'order'=>$order->id]) }}"
                              onsubmit="return confirm('{{ $courier['driver'] === 'api' ? 'Create a real Steadfast shipment for this order?' : 'Send this order to the courier (test mode)?' }}')">
                            @csrf
                            <button type="submit" class="action-btn btn-process">
                                <i class="fas fa-paper-plane"></i>
                                {{ $consignment && $consignment->last_error ? 'Retry courier send' : 'Send to courier' }}
                            </button>
                        </form>
                    @endunless
                @endif
            </div>

        </div>
    </div>

    <div class="row g-3">

        {{-- Left --}}
        <div class="col-lg-7">

            {{-- Buyer Info --}}
            <div class="order-card">
                <div class="order-card-header"><i class="fas fa-truck text-blue-600"></i> Shipping Address</div>
                <div class="order-card-body">
                    <div class="info-row"><div class="info-label">Customer</div><div class="info-value">{{ $order->shipping_name ?: $order->contact_person }}</div></div>
                    <div class="info-row"><div class="info-label">Phone</div><div class="info-value">{{ $order->shipping_phone }}</div></div>
                    <div class="info-row"><div class="info-label">Email</div><div class="info-value">{{ $order->shipping_email ?: '—' }}</div></div>
                    @if($order->delivery_contact_name || $order->delivery_contact_phone)
                    <div class="info-row">
                        <div class="info-label">Delivery Contact</div>
                        <div class="info-value">{{ $order->delivery_contact_name }}
                            @if($order->delivery_contact_phone)<br><span style="color:#666; font-weight:400">{{ $order->delivery_contact_phone }}</span>@endif
                        </div>
                    </div>
                    @endif
                    <div class="info-row"><div class="info-label">District</div><div class="info-value">{{ $order->district?->name ?: '—' }}</div></div>
                    <div class="info-row"><div class="info-label">Thana</div><div class="info-value">{{ $order->thana?->name ?: '—' }}</div></div>
                    <div class="info-row"><div class="info-label">Address</div><div class="info-value">{{ $order->shipping_address_line ?: '—' }}</div></div>
                    @if($order->purchase_ref_no)
                    <div class="info-row"><div class="info-label">Purchase Ref No</div><div class="info-value">{{ $order->purchase_ref_no }}</div></div>
                    @endif
                    @if($order->note)
                    <div class="info-row"><div class="info-label">Note</div><div class="info-value" style="white-space:pre-wrap">{{ $order->note }}</div></div>
                    @endif
                </div>
            </div>

            {{-- Billing Address --}}
            <div class="order-card">
                <div class="order-card-header"><i class="fas fa-file-invoice text-blue-600"></i> Billing Address</div>
                <div class="order-card-body">
                    @if($order->billing_same_as_shipping)
                        <p class="text-sm text-gray-500 m-0"><i class="fas fa-check-circle text-green-500 me-1"></i> Same as the shipping address.</p>
                    @else
                        <div class="info-row"><div class="info-label">Name</div><div class="info-value">{{ $order->billing_name ?: '—' }}</div></div>
                        <div class="info-row"><div class="info-label">Phone</div><div class="info-value">{{ $order->billing_phone ?: '—' }}</div></div>
                        <div class="info-row"><div class="info-label">Email</div><div class="info-value">{{ $order->billing_email ?: '—' }}</div></div>
                        <div class="info-row"><div class="info-label">Address</div><div class="info-value">{{ $order->billing_address_line ?: '—' }}</div></div>
                    @endif
                </div>
            </div>

            {{-- Payment Slip (bank transfer) --}}
            @if($isBankTransfer && $order->payment_slip_path)
            <div class="order-card">
                <div class="order-card-header"><i class="fas fa-file-image text-blue-600"></i> Payment Slip</div>
                <div class="order-card-body">
                    <div class="info-row"><div class="info-label">Transaction ID</div><div class="info-value">{{ $order->payment_transaction_id }}</div></div>
                    <div class="info-row"><div class="info-label">Buyer's Bank</div><div class="info-value">{{ $order->payment_bank_name }}</div></div>
                    <div class="info-row"><div class="info-label">Uploaded</div><div class="info-value">{{ $order->payment_slip_uploaded_at?->format('d M Y, h:i A') }}</div></div>
                    <div class="slip-preview mt-2">
                        @php $ext = pathinfo($order->payment_slip_path, PATHINFO_EXTENSION); @endphp
                        @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                            <img src="{{ asset($order->payment_slip_path) }}" alt="Payment Slip">
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-file-pdf fa-3x text-red-500 mb-2 d-block"></i>
                                <a href="{{ asset($order->payment_slip_path) }}" target="_blank" class="text-blue-600 text-sm">View PDF Slip</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @elseif($isBankTransfer && $order->status === 'payment_requested')
            <div class="order-card">
                <div class="order-card-header"><i class="fas fa-clock text-yellow-500"></i> Awaiting Payment Slip</div>
                <div class="order-card-body">
                    <p class="text-sm text-gray-500 m-0">Payment request has been sent. Waiting for buyer to upload their payment slip.</p>
                </div>
            </div>
            @endif

        </div>

        {{-- Right: Items + Summary --}}
        <div class="col-lg-5">

            <div class="order-card">
                <div class="order-card-header"><i class="fas fa-list text-blue-600"></i> Order Items</div>
                <div class="p-0">
                    <table class="items-table w-100" style="border-collapse:collapse">
                        <thead>
                            <tr>
                                <th style="text-align:left">Product</th>
                                <th style="text-align:center">Qty</th>
                                <th style="text-align:right">Price</th>
                                <th style="text-align:right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>
                                    {{ $productNames[$item->id] ?? '—' }}
                                    @if($variantLabels[$item->id] ?? null)
                                        <div class="text-muted small">{{ $variantLabels[$item->id] }}</div>
                                    @endif
                                </td>
                                <td style="text-align:center">{{ $item->qty }}</td>
                                <td style="text-align:right">Tk {{ number_format($item->price, 2) }}</td>
                                <td style="text-align:right; font-weight:700">Tk {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="order-card-body" style="border-top:1px solid #f0f0f0">
                    <div class="sum-row"><span>Subtotal</span><span>Tk {{ number_format($order->subtotal, 2) }}</span></div>
                    <div class="sum-row">
                        <span>Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span>
                        <span>Tk {{ number_format($order->discount, 2) }}</span>
                    </div>
                    <div class="sum-row"><span>Tax / VAT</span><span>Tk {{ number_format($order->tax, 2) }}</span></div>
                    <div class="sum-row">
                        <span>Delivery Charge{{ $order->district ? ' (' . $order->district->name . ')' : '' }}</span>
                        <span>Tk {{ number_format($order->shipping_charge, 2) }}</span>
                    </div>
                    <div class="sum-row grand"><span>Grand Total</span><span>Tk {{ number_format($order->total, 2) }}</span></div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
