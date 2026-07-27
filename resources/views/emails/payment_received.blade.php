@extends('emails.layout')
@section('subject', 'Payment Received')
@section('content')

<h2 style="margin:0 0 6px;font-size:22px;color:#111827;">Payment Confirmed!</h2>
<p style="margin:0 0 24px;font-size:14px;color:#6b7280;">
    Hi <strong>{{ $order->contact_person }}</strong>, we have successfully received and confirmed your payment
    for order <strong>{{ $order->order_no }}</strong>. Thank you!
</p>

{{-- Payment confirmation badge --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:24px;">
<tr><td style="padding:20px 24px;">
    <div style="display:flex;align-items:center;">
        <div>
            <div style="font-size:12px;color:#16a34a;font-weight:bold;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
                Payment Confirmed
            </div>
            <div style="font-size:26px;font-weight:bold;color:#15803d;">
                Tk {{ number_format($order->total, 2) }}
            </div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px;">
                Order: <strong style="font-family:monospace;">{{ $order->order_no }}</strong>
                &nbsp;·&nbsp;
                {{ now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>
</td></tr>
</table>

{{-- Payment details --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;border-collapse:collapse;">
@php $rows = [
    ['Company',        $order->company_name],
    ['Payment Method', $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'Cash on Delivery'],
    ['Invoice Status', 'Paid'],
]; @endphp
@foreach($rows as [$label, $value])
<tr>
    <td width="160" style="padding:7px 0;font-size:13px;color:#9ca3af;vertical-align:top;">{{ $label }}</td>
    <td style="padding:7px 0;font-size:13px;color:#111827;font-weight:600;">{{ $value }}</td>
</tr>
@endforeach
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;margin-bottom:24px;">
<tr><td style="padding:14px 18px;font-size:13px;color:#1e40af;">
    Your order is now <strong>complete</strong>. A paid invoice has been generated for your records.
    You can download it from your
    <a href="{{ route('buyer.invoices', []) }}" style="color:#1e40af;font-weight:bold;">invoice dashboard</a>.
</td></tr>
</table>

<p style="margin:0;font-size:13px;color:#6b7280;">
    Thank you for your business. We look forward to serving you again.
</p>

@endsection
