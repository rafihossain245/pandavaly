@extends('emails.layout')
@section('subject', 'Order Delivered')
@section('content')

<h2 style="margin:0 0 6px;font-size:22px;color:#111827;">Your Order Has Been Delivered!</h2>
<p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
    Hi <strong>{{ $order->contact_person }}</strong>, great news! Your order has been delivered successfully.
    We hope everything arrived in perfect condition.
</p>

{{-- Order badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:24px;">
<tr><td style="padding:16px 20px;">
    <div style="font-size:12px;color:#16a34a;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Delivered</div>
    <div style="font-size:20px;font-weight:bold;color:#15803d;font-family:monospace;">{{ $order->order_no }}</div>
    <div style="font-size:13px;color:#6b7280;margin-top:4px;">Delivered on {{ now()->format('d M Y') }}</div>
</td></tr>
</table>

{{-- Summary --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border-collapse:collapse;">
@php $rows = [
    ['Delivered To', $order->shipping_address_line],
    ['Order Total', 'Tk ' . number_format($order->total, 2)],
    ['Payment',    $order->payment_method === 'bank_transfer' ? 'Bank Transfer (Verified)' : 'Cash on Delivery'],
]; @endphp
@foreach($rows as [$label, $value])
<tr>
    <td width="160" style="padding:7px 0;font-size:13px;color:#9ca3af;vertical-align:top;">{{ $label }}</td>
    <td style="padding:7px 0;font-size:13px;color:#111827;font-weight:600;">{{ $value ?: '—' }}</td>
</tr>
@endforeach
</table>

@if($order->payment_method === 'cod')
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fffbeb;border:1px solid #fcd34d;border-radius:6px;margin-bottom:24px;">
<tr><td style="padding:14px 18px;font-size:13px;color:#92400e;">
    <strong>Reminder:</strong> Please arrange payment of <strong>Tk {{ number_format($order->total, 2) }}</strong> to complete your order.
</td></tr>
</table>
@endif

<p style="margin:0;font-size:13px;color:#6b7280;">
    If you have any concerns about your delivery, please contact us.
    You can view your full order history in your
    <a href="{{ route('buyer.orders.show', $order) }}" style="color:#1e40af;font-weight:600;">buyer dashboard</a>.
</p>

@endsection
