@extends('emails.layout')
@section('subject', 'Order Confirmed')
@section('content')

{{-- Greeting --}}
<h2 style="margin:0 0 6px;font-size:22px;color:#111827;">Order Confirmed!</h2>
<p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
    Hi <strong>{{ $order->contact_person }}</strong>, thank you for your order.
    We have received it and it is now <strong>pending approval</strong>.
    You will be notified once it is approved.
</p>

{{-- Order badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;margin-bottom:24px;">
<tr><td style="padding:16px 20px;">
    <div style="font-size:12px;color:#3b82f6;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Order Number</div>
    <div style="font-size:20px;font-weight:bold;color:#1e40af;font-family:monospace;">{{ $order->order_no }}</div>
    <div style="font-size:13px;color:#6b7280;margin-top:4px;">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</div>
</td></tr>
</table>

{{-- Details --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border-collapse:collapse;">
@php $rows = [
    ['Company',        $order->company_name],
    ['Payment Method', $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery'],
    ['Delivery To',    trim($order->shipping_address . ($order->shipping_city ? ', '.$order->shipping_city : ''))],
]; @endphp
@foreach($rows as [$label, $value])
<tr>
    <td width="160" style="padding:7px 0;font-size:13px;color:#9ca3af;vertical-align:top;">{{ $label }}</td>
    <td style="padding:7px 0;font-size:13px;color:#111827;font-weight:600;">{{ $value ?: '—' }}</td>
</tr>
@endforeach
</table>

{{-- Items --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:20px;border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;">
<thead>
<tr style="background:#f9fafb;">
    <th style="text-align:left;padding:10px 14px;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Product</th>
    <th style="text-align:center;padding:10px 14px;font-size:12px;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Qty</th>
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
    <td style="padding:10px 14px;font-size:13px;font-weight:600;color:#111827;text-align:right;border-bottom:1px solid #f3f4f6;">
        Tk {{ number_format($item->line_total, 2) }}
    </td>
</tr>
@endforeach
<tr style="background:#f9fafb;">
    <td colspan="2" style="padding:12px 14px;font-size:14px;font-weight:700;color:#111827;">Grand Total</td>
    <td style="padding:12px 14px;font-size:16px;font-weight:700;color:#1e40af;text-align:right;">Tk {{ number_format($order->total, 2) }}</td>
</tr>
</tbody>
</table>

@if($order->payment_method === 'bank_transfer')
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;margin-bottom:24px;">
<tr><td style="padding:14px 18px;font-size:13px;color:#92400e;">
    <strong>Bank Transfer Order:</strong> Our team will review and approve your order, then send you payment instructions with our bank details.
</td></tr>
</table>
@endif

<p style="margin:24px 0 0;font-size:13px;color:#6b7280;">
    You can track your order status anytime from your
    <a href="{{ route('buyer.orders.show', $order) }}" style="color:#1e40af;font-weight:600;">buyer dashboard</a>.
</p>

@endsection
