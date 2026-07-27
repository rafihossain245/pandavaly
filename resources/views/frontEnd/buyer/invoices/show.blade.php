@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')
    <div class="buyer-panel">
        <div class="d-flex justify-content-between align-items-start mb-5">
            <div>
                <div class="text-muted text-uppercase small">Invoice</div>
                <h2 class="mb-1">{{ $invoice->invoice_no }}</h2>
                <span class="buyer-badge {{ $invoice->status }}">{{ $invoice->status }}</span>
            </div>
            <div class="invoice-actions">
                <a href="{{ route('buyer.invoices') }}" class="btn btn-outline-secondary">Back</a>
                <button class="btn btn-primary" type="button" onclick="window.print()">Print Invoice</button>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <h6 class="text-muted">Billed To</h6>
                <strong>{{ $invoice->buyer->business_name }}</strong><br>
                {{ $invoice->buyer->email }}<br>{{ $invoice->buyer->phone }}
            </div>
            <div class="col-md-6 text-md-end">
                <div><span class="text-muted">Invoice date:</span> {{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</div>
                <div><span class="text-muted">Due date:</span> {{ $invoice->due_date?->format('d M Y') ?? '-' }}</div>
                <div><span class="text-muted">Order:</span> {{ $invoice->salesOrder?->order_no ?? '-' }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Product</th><th>Quantity</th><th class="text-end">Price</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>
                                {{ $item->productSku?->product?->name ?? 'Product' }}
                                @if($item->productSku?->variant_label)
                                    <div class="text-muted small">{{ $item->productSku->variant_label }}</div>
                                @endif
                            </td>
                            <td>{{ $item->qty }}</td>
                            <td class="text-end">৳{{ number_format($item->price, 2) }}</td>
                            <td class="text-end">৳{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="3" class="text-end">Subtotal</th><td class="text-end">৳{{ number_format($invoice->subtotal, 2) }}</td></tr>
                    <tr><th colspan="3" class="text-end">Discount</th><td class="text-end">-৳{{ number_format($invoice->discount, 2) }}</td></tr>
                    <tr><th colspan="3" class="text-end">Tax</th><td class="text-end">৳{{ number_format($invoice->tax, 2) }}</td></tr>
                    <tr><th colspan="3" class="text-end">Total</th><th class="text-end">৳{{ number_format($invoice->total, 2) }}</th></tr>
                    <tr><th colspan="3" class="text-end">Balance Due</th><th class="text-end">৳{{ number_format($invoice->balance, 2) }}</th></tr>
                </tfoot>
            </table>
        </div>
    </div>
@include('frontEnd.buyer.partials.layout-end')
@endsection
