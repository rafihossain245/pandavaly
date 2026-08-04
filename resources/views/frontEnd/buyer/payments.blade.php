@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')

    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="ba-stat">
                <div>
                    <div class="ba-stat-value">{{ number_format($totals['paid'], 2) }}</div>
                    <div class="ba-stat-label">Total paid (BDT)</div>
                </div>
                <div class="ba-stat-icon ba-ic-green"><i class="fas fa-circle-check"></i></div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="ba-stat">
                <div>
                    <div class="ba-stat-value">{{ number_format($totals['due'], 2) }}</div>
                    <div class="ba-stat-label">Outstanding (BDT)</div>
                </div>
                <div class="ba-stat-icon ba-ic-pink"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
    </div>

    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Payments</h3>
            <a href="{{ route('buyer.invoices') }}" class="ba-panel-action">Invoices</a>
        </div>
        <div class="ba-panel-body is-flush">
            @forelse($orders as $order)
            @php $due = max(0, $order->total - $order->advance_paid); @endphp
            <div class="ba-order-row">
                <div style="flex:1; min-width:180px">
                    <div class="ba-order-no">#{{ $order->order_no }}</div>
                    <div class="ba-order-date">{{ $order->created_at?->format('jS M, Y h:i A') }}</div>
                </div>
                <div class="ba-order-cell">
                    Method
                    <strong>{{ $order->payment_method === 'bank_transfer' ? 'Bank Transfer' : 'COD' }}</strong>
                </div>
                <div class="ba-order-cell">Total <strong>{{ number_format($order->total, 2) }} BDT</strong></div>
                <div class="ba-order-cell">Paid <strong style="color:#16a34a">{{ number_format($order->advance_paid, 2) }} BDT</strong></div>
                <div class="ba-order-cell">Due <strong style="color:{{ $due > 0 ? '#dc2626' : '#16a34a' }}">{{ number_format($due, 2) }} BDT</strong></div>
                <div>
                    <span class="ba-pill {{ $due > 0 ? 'ba-pill-unpaid' : 'ba-pill-paid' }}">{{ $due > 0 ? 'Unpaid' : 'Paid' }}</span>
                </div>
                <a href="{{ route('buyer.orders.show', $order) }}" class="ba-btn-sm">
                    {{ $due > 0 && $order->payment_method === 'bank_transfer' ? 'Pay now' : 'Details' }}
                </a>
            </div>
            @empty
            <div class="ba-empty">
                <i class="fas fa-credit-card"></i>
                <p>No payments yet.</p>
            </div>
            @endforelse
        </div>
        @if($orders->hasPages())
        <div class="ba-panel-body" style="border-top:1px solid #f1f2f4">{{ $orders->links() }}</div>
        @endif
    </div>

@include('frontEnd.buyer.partials.layout-end')
@endsection
