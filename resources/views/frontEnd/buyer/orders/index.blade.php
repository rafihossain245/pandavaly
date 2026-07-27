@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')
    <div class="buyer-panel">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h2 class="mb-1">Order History</h2><p class="text-muted mb-0">Review and track your purchases.</p></div>
            <a class="btn btn-primary" href="{{ route('shop') }}">Shop Products</a>
        </div>
        @include('frontEnd.buyer.orders.table', ['orders' => $orders])
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
@include('frontEnd.buyer.partials.layout-end')
@endsection
