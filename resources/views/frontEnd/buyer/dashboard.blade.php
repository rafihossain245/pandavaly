@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        @foreach([
            ['value' => number_format($stats['orders']),        'label' => 'Total order placed', 'icon' => 'fa-bag-shopping',   'tone' => 'blue'],
            ['value' => number_format($stats['running']),       'label' => 'Running orders',     'icon' => 'fa-truck-fast',     'tone' => 'orange'],
            ['value' => number_format($stats['cart_items']),    'label' => 'Items in cart',      'icon' => 'fa-cart-shopping',  'tone' => 'green'],
            ['value' => number_format($stats['wishlist']),      'label' => 'Product in wishlist','icon' => 'fa-heart',          'tone' => 'pink'],
            ['value' => number_format($stats['spent']),         'label' => 'Amount spent',       'icon' => 'fa-bangladeshi-taka-sign', 'tone' => 'cyan'],
            ['value' => number_format($stats['tickets']),       'label' => 'Opened Tickets',     'icon' => 'fa-comments',       'tone' => 'purple'],
        ] as $card)
        <div class="col-sm-6 col-xl-4">
            <div class="ba-stat">
                <div>
                    <div class="ba-stat-value">{{ $card['value'] }}</div>
                    <div class="ba-stat-label">{{ $card['label'] }}</div>
                </div>
                <div class="ba-stat-icon ba-ic-{{ $card['tone'] }}"><i class="fas {{ $card['icon'] }}"></i></div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Recent orders --}}
    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Recent orders</h3>
            <a href="{{ route('buyer.orders') }}" class="ba-panel-action">All orders</a>
        </div>
        <div class="ba-panel-body is-flush">
            @forelse($recentOrders as $order)
            <div class="ba-order-row">
                <div style="flex:1; min-width:190px">
                    <div class="ba-order-no">
                        <i class="fas fa-circle-check me-1" style="color:#3b82f6"></i>
                        #{{ $order->order_no }}
                    </div>
                    <div class="ba-order-date">{{ $order->created_at?->format('jS M, Y h:i A') }}</div>
                </div>
                <div>@include('frontEnd.buyer.partials.status-pill', ['status' => $order->status])</div>
                <div class="ba-order-cell">Qty: <strong>{{ $order->items_count }}</strong></div>
                <div class="ba-order-cell">Amount: <strong>{{ number_format($order->total, 2) }} BDT</strong></div>
                <a href="{{ route('buyer.orders.show', $order) }}" class="ba-btn-sm">Order details</a>
            </div>
            @empty
            <div class="ba-empty">
                <i class="fas fa-bag-shopping"></i>
                <p>You have not placed any orders yet.</p>
                <a href="{{ route('shop') }}" class="ba-btn-sm ba-btn-primary">Start shopping</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Wishlist items --}}
    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Wishlist items</h3>
            <a href="{{ route('buyer.wishlist') }}" class="ba-panel-action">View more</a>
        </div>
        <div class="ba-panel-body">
            @if($wishlistProducts->isEmpty())
                <div class="ba-empty" style="padding:18px 0">
                    <p class="mb-0">No Product in Wishlist</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($wishlistProducts as $product)
                    <div class="col-6 col-md-3">
                        <a href="{{ route('product.details', $product->slug) }}" class="d-block text-decoration-none">
                            <img src="{{ asset($product->thumbnail ?: 'frontEnd/assets/image/product.jpg') }}"
                                 alt="{{ $product->name }}"
                                 style="width:100%; height:120px; object-fit:contain; background:#fafafa; border:1px solid #f0f0f0; border-radius:8px; padding:6px">
                            <div style="font-size:12.5px; font-weight:600; color:#111827; margin-top:8px; line-height:1.4">{{ $product->name }}</div>
                            <div style="font-size:12.5px; color:var(--primary); font-weight:700; margin-top:2px">
                                {{ number_format($product->product_prices->first()->selling_price ?? 0, 2) }} BDT
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@include('frontEnd.buyer.partials.layout-end')
@endsection
