@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Buyer Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, {{ $buyer->business_name }}.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('shop') }}">Continue Shopping</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="buyer-stat"><small class="text-muted">Total Orders</small><div class="value">{{ $stats['orders'] }}</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="buyer-stat"><small class="text-muted">In Progress</small><div class="value">{{ $stats['pending'] }}</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="buyer-stat"><small class="text-muted">Invoices</small><div class="value">{{ $stats['invoices'] }}</div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="buyer-stat"><small class="text-muted">Outstanding</small><div class="value">৳{{ number_format($stats['outstanding'], 2) }}</div></div></div>
    </div>

    <div class="buyer-panel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Recent Orders</h4>
            <a href="{{ route('buyer.orders') }}">View all</a>
        </div>
        @include('frontEnd.buyer.orders.table', ['orders' => $recentOrders])
    </div>
@include('frontEnd.buyer.partials.layout-end')
@endsection
