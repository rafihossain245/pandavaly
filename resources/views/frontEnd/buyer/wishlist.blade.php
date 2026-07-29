@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')
    <div class="buyer-panel">
        <h2 class="mb-1">My Wishlist</h2>
        <p class="text-muted mb-4">Products you've saved for later.</p>

        <div class="shop-products d-flex flex-wrap gap-20px">
            @forelse ($products as $item)
                @include('frontEnd.partials.product-card', ['item' => $item, 'wrapperClass' => 'item'])
            @empty
                <div class="w-100 text-center p-5 bg-light rounded">
                    <h5 class="mb-2">Your wishlist is empty</h5>
                    <p class="text-muted mb-3">Tap the heart icon on any product to save it here.</p>
                    <a href="{{ route('shop') }}" class="btn btn-primary">Browse Products</a>
                </div>
            @endforelse
        </div>
    </div>
@include('frontEnd.buyer.partials.layout-end')
@endsection
