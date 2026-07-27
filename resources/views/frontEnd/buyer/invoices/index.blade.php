@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')
    <div class="buyer-panel">
        <h2 class="mb-1">Invoices</h2>
        <p class="text-muted mb-4">View billing status and print invoice copies.</p>
        <div class="table-responsive">
            <table class="table buyer-table align-middle">
                <thead><tr><th>Invoice</th><th>Order</th><th>Date</th><th>Status</th><th class="text-end">Balance</th><th class="text-end">Total</th><th></th></tr></thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="fw-semibold">{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->salesOrder?->order_no ?? '-' }}</td>
                            <td>{{ $invoice->invoice_date?->format('d M Y') ?? $invoice->created_at->format('d M Y') }}</td>
                            <td><span class="buyer-badge {{ $invoice->status }}">{{ $invoice->status }}</span></td>
                            <td class="text-end">৳{{ number_format($invoice->balance, 2) }}</td>
                            <td class="text-end">৳{{ number_format($invoice->total, 2) }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('buyer.invoices.show', $invoice) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
@include('frontEnd.buyer.partials.layout-end')
@endsection
