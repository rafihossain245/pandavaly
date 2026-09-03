@extends('emails.layout')
@section('subject', 'Order Cancelled')
@section('content')
<h2 style="margin:0 0 8px;font-size:22px;color:#991b1b;">Order Cancelled</h2>
<p style="margin:0 0 22px;font-size:14px;color:#4b5563;">
    Hi <strong>{{ $order->contact_person }}</strong>, your order has been cancelled.
</p>
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;margin-bottom:22px;">
<tr><td style="padding:16px 20px;">
    <div style="font-size:12px;color:#991b1b;font-weight:bold;text-transform:uppercase;">Order Number</div>
    <div style="font-size:20px;font-weight:bold;color:#7f1d1d;font-family:monospace;">{{ $order->order_no }}</div>
</td></tr>
</table>
<p style="margin:0;font-size:14px;color:#4b5563;">
    Please contact our support team if you believe this was unexpected or if you need help with a refund.
</p>
@endsection
