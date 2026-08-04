@extends('emails.layout')
@section('subject', 'Invoice Ready')
@section('content')

@php $invoice = $order->invoice; @endphp

<h2 style="margin:0 0 6px;font-size:22px;color:#111827;">Your Invoice is Ready</h2>
<p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
    Hi <strong>{{ $order->contact_person }}</strong>, your order is now being processed.
    Please find your invoice details below.
</p>

{{-- Invoice badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:24px;">
<tr><td style="padding:16px 20px;">
    <div style="font-size:12px;color:#16a34a;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Invoice Number</div>
    <div style="font-size:20px;font-weight:bold;color:#15803d;font-family:monospace;">{{ $invoice?->invoice_no ?? '—' }}</div>
    <div style="font-size:13px;color:#6b7280;margin-top:4px;">
        Order: <strong>{{ $order->order_no }}</strong>
        &nbsp;·&nbsp;
        Date: {{ $invoice?->invoice_date?->format('d M Y') ?? now()->format('d M Y') }}
        &nbsp;·&nbsp;
        Due: {{ $invoice?->due_date?->format('d M Y') ?? '30 days' }}
    </div>
</td></tr>
</table>

{{-- Items --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:20px;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;">
<thead>
<tr style="background:#f9fafb;">
    <th style="text-align:left;padding:10px 14px;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Product</th>
    <th style="text-align:center;padding:10px 14px;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Qty</th>
    <th style="text-align:right;padding:10px 14px;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Unit Price</th>
    <th style="text-align:right;padding:10px 14px;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Total</th>
</tr>
</thead>
<tbody>
@foreach($order->items as $item)
<tr>
    <td style="padding:10px 14px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;">
        {{ $item->productSku?->product?->name ?? 'Product' }}
        @if($item->productSku?->variant_label)
            <div style="color:#9ca3af;font-size:12px;">{{ $item->productSku->variant_label }}</div>
        @endif
    </td>
    <td style="padding:10px 14px;font-size:13px;color:#374151;text-align:center;border-bottom:1px solid #f3f4f6;">{{ $item->qty }}</td>
    <td style="padding:10px 14px;font-size:13px;color:#374151;text-align:right;border-bottom:1px solid #f3f4f6;">Tk {{ number_format($item->price, 2) }}</td>
    <td style="padding:10px 14px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">Tk {{ number_format($item->line_total, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>

{{-- Totals --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
@php $rows = [
    ['Subtotal', number_format($order->subtotal, 2)],
    ['Discount', number_format($order->discount, 2)],
    ['Tax / VAT', number_format($order->tax, 2)],
    ['Delivery cost', number_format($order->shipping_charge, 2)],
]; @endphp
@foreach($rows as [$label, $val])
<tr>
    <td style="padding:5px 0;font-size:13px;color:#6b7280;">{{ $label }}</td>
    <td style="padding:5px 0;font-size:13px;color:#374151;text-align:right;">Tk {{ $val }}</td>
</tr>
@endforeach
<tr style="border-top:2px solid #111827;">
    <td style="padding:10px 0 0;font-size:15px;font-weight:700;color:#111827;">Grand Total</td>
    <td style="padding:10px 0 0;font-size:15px;font-weight:700;color:#1e40af;text-align:right;">Tk {{ number_format($order->total, 2) }}</td>
</tr>
</table>

@if($order->payment_method === 'cod')
<table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;margin-bottom:24px;">
<tr><td style="padding:14px 18px;font-size:13px;color:#1e40af;">
    <strong>Cash on Delivery:</strong> Payment of <strong>Tk {{ number_format($order->total, 2) }}</strong> is due upon delivery.
</td></tr>
</table>
@endif

<p style="margin:0;font-size:13px;color:#6b7280;">
    View your full invoice anytime in your
    <a href="{{ route('buyer.orders.show', $order) }}" style="color:#1e40af;font-weight:600;">buyer dashboard</a>.
</p>

@endsection
