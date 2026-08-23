@extends('layout.app')
@section('meta-information')
    <title>Invoice {{ $invoice->invoice_no }}</title>
@endsection

@section('css')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
<style>
    /* ---- Brand invoice ------------------------------------------------
       Sized to A4 width so "Print / Save PDF" produces the same document
       that is shown on screen. Colours are the storefront palette. */
    .inv-sheet {
        --ink: #1a1a1a; --brand: #e6007e; --tint: #fdeaf4; --line: #f2c9e0;
        width: 210mm; max-width: 100%; margin: 0 auto; background: #fff;
        border: 1px solid #e6e6ec; padding: 26px 30px 18px;
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: var(--ink);
    }
    .inv-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; }
    .inv-logo-plate { background: var(--brand); border-radius: 8px; padding: 9px 16px; display: inline-flex; align-items: center; }
    .inv-logo { max-height: 44px; max-width: 210px; width: auto; object-fit: contain; display: block; }
    .inv-brand-name { font-size: 21px; font-weight: 800; color: var(--brand); letter-spacing: .5px; }
    .inv-brand-sub { font-size: 10px; letter-spacing: 4px; color: #b8b8c2; text-transform: uppercase; }
    .inv-qr { border: 2px solid var(--brand); border-radius: 6px; padding: 5px; flex-shrink: 0; }
    .inv-qr img { display: block; width: 78px; height: 78px; }

    /* Title band: two magenta bars with INVOICE knocked out between them. */
    .inv-title-row { display: flex; align-items: center; gap: 14px; margin: 6px 0 20px; }
    .inv-title-bar { flex: 1; height: 20px; background: var(--brand); }
    .inv-title { font-size: 25px; font-weight: 800; letter-spacing: 1px; white-space: nowrap; }

    .inv-meta { display: grid; grid-template-columns: 1.35fr 1.15fr .8fr; gap: 16px; margin-bottom: 20px; }
    .inv-meta-label { font-size: 12.5px; font-weight: 700; margin-bottom: 6px; }
    .inv-meta-value { font-size: 12.5px; line-height: 1.65; color: #3a3a44; }
    .inv-order-no { font-weight: 800; color: var(--brand); font-size: 13px; white-space: nowrap; letter-spacing: -.2px; }

    table.inv-items { width: 100%; border-collapse: collapse; border: 1px solid var(--line); }
    table.inv-items thead th {
        background: var(--brand); color: #fff; font-size: 11.5px; font-weight: 700;
        letter-spacing: .3px; padding: 8px 10px; text-align: left;
    }
    table.inv-items tbody td { padding: 8px 10px; font-size: 12.5px; border-bottom: 1px solid var(--line); }
    table.inv-items tbody tr:nth-child(even) td { background: var(--tint); }
    .inv-num { text-align: right; white-space: nowrap; }
    .inv-center { text-align: center; }
    /* Blank rows keep the table a consistent height on short orders, as on a
       pre-printed pad — without them a one-line invoice looks unfinished. */
    .inv-filler td { height: 26px; }

    .inv-lower { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; margin-top: 18px; }
    .inv-thanks { font-family: 'Caveat', 'Segoe Script', cursive; font-size: 40px; font-weight: 600; color: var(--brand); line-height: .95; }
    .inv-thanks small { display: block; font-family: inherit; font-size: 24px; color: #8b8b99; margin-top: -2px; }

    .inv-totals { width: 250px; flex-shrink: 0; }
    .inv-total-row { display: flex; justify-content: space-between; font-size: 12.5px; padding: 5px 10px; }
    .inv-total-row.is-grand { background: var(--brand); color: #fff; font-weight: 800; font-size: 14px; padding: 8px 10px; margin-top: 4px; }

    .inv-foot { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; margin-top: 20px; }
    .inv-pay-label { font-size: 12.5px; font-weight: 700; margin-bottom: 3px; }
    .inv-pay-value { font-size: 12.5px; color: #3a3a44; }
    .inv-sign { text-align: center; font-size: 11.5px; color: #6b6b7b; border-top: 1px solid var(--ink); padding-top: 4px; min-width: 165px; }
    .inv-contact {
        margin-top: 18px; padding-top: 10px; border-top: 1px solid #eee;
        text-align: center; font-size: 10.5px; color: #8b8b99; line-height: 1.7;
    }

    @media print {
        /* Print the invoice ALONE. Everything else on this screen belongs to the
           admin panel — sidebar, sticky header, breadcrumb, "System Online"
           footer — and none of it belongs on a document handed to a customer. */
        #sidebar,
        #mobileMenuButton,
        .admin-footer,
        header.header,
        .no-print { display: none !important; }

        /* The shell also shapes the page: a 16rem sidebar offset, full-height
           column and 1.5rem padding would print the sheet in a narrow strip. */
        body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
        .md\:ml-64 { margin-left: 0 !important; }
        .min-h-screen { min-height: 0 !important; }
        main { padding: 0 !important; }

        .inv-sheet {
            border: none; padding: 0; margin: 0;
            width: 100%; max-width: none;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        /* Colour bands and tinted rows are the design, not decoration — keep
           them even when the browser's "Background graphics" box is unticked. */
        table.inv-items thead th, .inv-total-row.is-grand, .inv-title-bar,
        .inv-logo-plate, .inv-qr, table.inv-items tbody tr:nth-child(even) td {
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        /* Never split the table or the totals across two sheets. */
        table.inv-items { page-break-inside: avoid; }
        .inv-lower, .inv-foot { page-break-inside: avoid; }

        @page { size: A4; margin: 12mm; }
    }
</style>
@endsection

@section('main-content')
@php
    $role    = Str::slug(Auth::user()->getRoleNames()->first());
    $setting = \App\Models\Setting::first();
    $order   = $invoice->salesOrder;

    // Prefer what the shopper typed at checkout — that is who the goods go to.
    $toName  = $order->shipping_name    ?? $invoice->buyer?->business_name;
    $toPhone = $order->shipping_phone   ?? $invoice->buyer?->phone;
    $toAddr  = $order->shipping_address ?? $invoice->buyer?->address;
    $area    = $order?->district?->name;

    $delivery = (float) ($order->shipping_charge ?? 0);
    $discount = (float) $invoice->discount;
    $payment  = ($order->payment_method ?? 'cod') === 'bank_transfer' ? 'Bank / bKash' : 'COD';

    // Scans to the public tracker, so a customer holding the printed invoice
    // can check delivery without phoning the shop.
    try {
        $qr = \App\Support\Qr::svgDataUri(
            $order ? route('track-order', ['order_no' => $order->order_no]) : url('/')
        );
    } catch (\Throwable $e) {
        $qr = null;
    }

    $rows = $invoice->items;
    $filler = max(0, 6 - $rows->count());
@endphp

<div class="no-print flex items-center justify-between mb-5">
    <a href="{{ route('role.invoices.index', ['role' => $role]) }}" class="text-blue-600 text-sm hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Invoices
    </a>
    <button onclick="window.print()"
            class="flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-gray-700">
        <i class="fas fa-print"></i> Print / Save PDF
    </button>
</div>

<div class="inv-sheet">

    <div class="inv-head">
        <div>
            @if($setting?->logo_path)
                <span class="inv-logo-plate">
                    <img class="inv-logo" src="{{ asset($setting->logo_path) }}" alt="{{ $setting->title ?? 'Panda Valy' }}">
                </span>
            @else
                <div class="inv-brand-name">{{ Str::upper($setting->title ?? 'Panda Valy') }}</div>
                <div class="inv-brand-sub">Shopping center</div>
            @endif
        </div>
        @if($qr)
            <div class="inv-qr"><img src="{{ $qr }}" alt="Scan to track this order"></div>
        @endif
    </div>

    <div class="inv-title-row">
        <span class="inv-title-bar"></span>
        <span class="inv-title">INVOICE</span>
        <span class="inv-title-bar"></span>
    </div>

    <div class="inv-meta">
        <div>
            <div class="inv-meta-label">Invoice to :</div>
            <div class="inv-meta-value">
                <strong>{{ $toName ?: '—' }}</strong><br>
                @if($toPhone){{ $toPhone }}<br>@endif
                @if($toAddr){{ $toAddr }}@if($area), {{ $area }}@endif @endif
            </div>
        </div>
        <div>
            <div class="inv-meta-label">Order ID :</div>
            <div class="inv-order-no">#{{ $order->order_no ?? $invoice->invoice_no }}</div>
        </div>
        <div>
            <div class="inv-meta-label">Date :</div>
            <div class="inv-meta-value">{{ $invoice->invoice_date?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>

    <table class="inv-items">
        <thead>
            <tr>
                <th style="width:44px">SL</th>
                <th>Item Description</th>
                <th class="inv-num" style="width:88px">Price</th>
                <th class="inv-center" style="width:56px">Qty</th>
                <th class="inv-num" style="width:106px">Amount tk.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $i => $item)
                <tr>
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        {{ $item->productSku?->product?->name ?? '—' }}
                        @if($item->productSku?->variant_label)
                            <div style="font-size:11px; color:#8b8b99">{{ $item->productSku->variant_label }}</div>
                        @endif
                    </td>
                    <td class="inv-num">{{ number_format($item->price, 0) }}</td>
                    <td class="inv-center">{{ $item->qty }}</td>
                    <td class="inv-num">{{ number_format($item->line_total, 0) }}</td>
                </tr>
            @endforeach
            @for($i = 0; $i < $filler; $i++)
                <tr class="inv-filler"><td></td><td></td><td></td><td></td><td></td></tr>
            @endfor
        </tbody>
    </table>

    <div class="inv-lower">
        <div class="inv-thanks">thank you<small>for purchase</small></div>
        <div class="inv-totals">
            <div class="inv-total-row"><span>Sub Total :</span><span>{{ number_format($invoice->subtotal, 0) }}</span></div>
            @if($discount > 0)
                <div class="inv-total-row"><span>Discount :</span><span>− {{ number_format($discount, 0) }}</span></div>
            @endif
            <div class="inv-total-row"><span>Delivery Charge :</span><span>{{ number_format($delivery, 0) }}</span></div>
            <div class="inv-total-row is-grand"><span>Total :</span><span>{{ number_format($invoice->total, 0) }}</span></div>
        </div>
    </div>

    <div class="inv-foot">
        <div>
            <div class="inv-pay-label">Payment Info:</div>
            <div class="inv-pay-value">Payment type : <strong>{{ $payment }}</strong></div>
        </div>
        <div class="inv-sign">Authorised Sign</div>
    </div>

    <div class="inv-contact">
        @if($setting?->address){{ $setting->address }}@endif
        @if($setting?->contact_phone), {{ $setting->contact_phone }}@endif
        @if($setting?->contact_email)<br>{{ $setting->contact_email }}@endif
    </div>
</div>
@endsection
