@extends('frontEnd.layouts.master')

@section('content')
    <section class="shop-section py-4">
        <div class="container">
            <div class="breadcrumb-container mb-3">
                <div class="custom-breadcrumb">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="breadcrumb-separator">/</span>
                    <p>Shop</p>
                </div>
            </div>

            <div class="theme-inline-two-block mb-3 rounded">
                <div class="theme-block-left">
                    <div class="theme-open-fixed-sidebar" onclick="openSidebar()">
                        <i class="fas fa-sliders-h"></i>
                        Filters
                    </div>
                </div>
                <div class="theme-block-right ms-auto">
                    <p class="theme-woo-result-count m-0">
                        Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                    </p>

                    <form action="{{ route('shop') }}" method="GET" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="category" value="{{ $activeCategory }}">
                        <input type="hidden" name="sub_category" value="{{ $activeSubCategory }}">
                        <input type="hidden" name="brand" value="{{ $activeBrand }}">
                        <input type="hidden" name="min_price" value="{{ $minPrice }}">
                        <input type="hidden" name="max_price" value="{{ $maxPrice }}">

                        <input type="search" name="q" class="form-control" placeholder="Search products..."
                            value="{{ $searchQuery }}">
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="newest" {{ $activeSort === 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="name_asc" {{ $activeSort === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                            <option value="price_asc" {{ $activeSort === 'price_asc' ? 'selected' : '' }}>Price Low-High</option>
                            <option value="price_desc" {{ $activeSort === 'price_desc' ? 'selected' : '' }}>Price High-Low</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </form>
                </div>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-lg-3">
                    <aside class="shop-sidebar-inner rounded" id="shopSidebar">
                        <button type="button" class="close-shop-sidebar d-none" onclick="closeSidebar()">
                            <i class="fas fa-times"></i>
                        </button>

                        <form action="{{ route('shop') }}" method="GET" class="shop-sidebar-inner-widget">
                            <h5 class="shop-sidebar-inner-widget-title">Search</h5>
                            <input type="search" name="q" class="search-field w-100" placeholder="Search by name"
                                value="{{ $searchQuery }}">

                            <input type="hidden" name="sort" value="{{ $activeSort }}">
                            <button type="submit" class="btn btn-primary mt-3 w-100">Search</button>
                        </form>

                        <div class="shop-sidebar-inner-widget">
                            <h5 class="shop-sidebar-inner-widget-title">Categories</h5>
                            <div class="widget-body">
                                <ul class="ninetheme-product-categories-primary list-unstyled mb-0">
                                    <li class="cat-item mb-2">
                                        <a href="{{ route('shop', request()->except(['category', 'sub_category', 'page'])) }}"
                                            class="{{ empty($activeCategory) ? 'fw-bold text-primary' : '' }}">
                                            All Categories
                                        </a>
                                    </li>
                                    @foreach ($categories as $category)
                                        <li class="cat-item mb-2">
                                            <a href="{{ route('shop', array_merge(request()->except(['category', 'sub_category', 'page']), ['category' => $category->slug])) }}"
                                                class="{{ $activeCategory === $category->slug ? 'fw-bold text-primary' : '' }}">
                                                {{ $category->name }}
                                            </a>

                                            @if ($category->sub_categories->count())
                                                <ul class="children list-unstyled ps-3 d-block">
                                                    @foreach ($category->sub_categories as $sub)
                                                        <li class="cat-item mb-1">
                                                            <a href="{{ route('shop', array_merge(request()->except(['page']), ['category' => $category->slug, 'sub_category' => $sub->slug])) }}"
                                                                class="{{ $activeSubCategory === $sub->slug ? 'fw-bold text-primary' : '' }}">
                                                                {{ $sub->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <form action="{{ route('shop') }}" method="GET" class="shop-sidebar-inner-widget">
                            <h5 class="shop-sidebar-inner-widget-title">Filter by Brand</h5>
                            <input type="hidden" name="q" value="{{ $searchQuery }}">
                            <input type="hidden" name="category" value="{{ $activeCategory }}">
                            <input type="hidden" name="sub_category" value="{{ $activeSubCategory }}">
                            <input type="hidden" name="sort" value="{{ $activeSort }}">
                            <input type="hidden" name="min_price" value="{{ $minPrice }}">
                            <input type="hidden" name="max_price" value="{{ $maxPrice }}">

                            <select name="brand" class="form-select">
                                <option value="">All Brands</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->slug }}" {{ $activeBrand === $brand->slug ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-primary mt-3 w-100">Apply Brand</button>
                        </form>

                        <form action="{{ route('shop') }}" method="GET" class="shop-sidebar-inner-widget">
                            <h5 class="shop-sidebar-inner-widget-title">Price Range</h5>
                            <input type="hidden" name="q" value="{{ $searchQuery }}">
                            <input type="hidden" name="category" value="{{ $activeCategory }}">
                            <input type="hidden" name="sub_category" value="{{ $activeSubCategory }}">
                            <input type="hidden" name="brand" value="{{ $activeBrand }}">
                            <input type="hidden" name="sort" value="{{ $activeSort }}">

                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control" placeholder="Min"
                                        value="{{ $minPrice }}" min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control" placeholder="Max"
                                        value="{{ $maxPrice }}" min="0">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-3 w-100">Apply Price</button>
                        </form>

                        <a href="{{ route('shop') }}" class="btn btn-outline-secondary w-100">Reset All Filters</a>
                    </aside>
                </div>

                <div class="col-lg-9">
                    <div class="shop-products d-flex flex-wrap gap-20px">
                        @forelse ($products as $item)
                            @php
                                $price = $item->product_prices->first();
                            @endphp
                            <div class="product-card item">
                                <div class="discount-badge">0%</div>
                                <div class="product-image">
                                    <img src="{{ asset($item->thumbnail) }}" alt="{{ $item->name }}">
                                </div>
                                <a href="{{ route('product.details', $item->slug) }}" class="product-name line-2">
                                    {{ $item->name }}
                                </a>
                                <div class="price-container">
                                    <span class="original-price">৳ {{ $price->previous_price ?? '0' }}</span>
                                    <span class="current-price">৳ {{ $price->selling_price ?? '0' }}</span>
                                </div>
                                <div class="stock-info">
                                    IN STOCK: <span class="stock-count">{{ $item->stock_qty ?? 0 }}</span>
                                </div>
                                <div class="action-buttons mt-2">
                                    <button class="btn btn-sm btn-primary text-white add-to-cart-btn" data-product="{{ $item->id }}">Add to Cart</button>
                                    <a href="{{ route('product.details', $item->slug) }}" class="btn btn-sm btn-outline-secondary ms-2">View</a>
                                </div>
                            </div>
                        @empty
                            <div class="w-100 text-center p-5 bg-light rounded">
                                <h5 class="mb-2">No products found</h5>
                                <p class="text-muted mb-3">Try changing search text or filter options.</p>
                                <a href="{{ route('shop') }}" class="btn btn-primary">View all products</a>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
