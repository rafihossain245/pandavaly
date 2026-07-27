@extends('frontEnd.layouts.master')

@section('content')
    
    <section class="slider-section p-0 mb-4">
        <div class="container">
            <div class="swiper-container slideshow">
                <div class="swiper-wrapper">
                    @foreach ($sliders as $item)
                        <div class="swiper-slide slide">
                        <div class="slide-image"
                            style="background-image: url('{{ asset($item->image_path) }}')">
                        </div>
                        <div class="slide-content">
                            <h2 class="slide-title">

                            </h2>
                            <p class="slide-desc">

                            </p>
                            <div class="slide-buttons">

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="slideshow-pagination"></div>

                <div class="slideshow-navigation">
                    <div class="slideshow-navigation-button prev"><span class="fas fa-chevron-left"></span></div>
                    <div class="slideshow-navigation-button next"><span class="fas fa-chevron-right"></span></div>
                </div>

            </div>
        </div>
    </section>

    <div class="trending-product">
        <div class="container d-flex align-items-start gap-20px">
            <div class="banner-part">
                <img src="{{ asset('frontEnd/assets/') }}/image/banner.png" alt="">
            </div>
            <div class="products-part">
                <h3 class="section-title">Trending Product</h3>
                <div class="swiper trending-slider overflow-hidden">
                    <div class="swiper-wrapper">
                        @foreach ($trending_products as $item)
                            <div class="product-card swiper-slide">
                            <div class="discount-badge">0%</div>
                            <div class="product-image">
                                <img src="{{ asset($item->thumbnail) }}"
                                    alt="{{ $item->name }}">
                            </div>
                            <a href="{{ route('product.details', $item->slug) }}" class="product-name line-2">{{ $item->name }}</a>
                            <div class="price-container">
                                <span class="original-price">৳ {{ $item->product_prices[0]->previous_price ?? '0' }}</span>
                                <span class="current-price">৳ {{ $item->product_prices[0]->selling_price ?? '0' }}</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            {{-- <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" onclick="openCartModal(this)"
                                    data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div> --}}
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="banner-section py-4 my-4">
        <div class="container">
            <a href="#" class="w-100 img">
                <img src="{{ asset('frontEnd/assets/') }}/image/big-banner.png" alt="">
            </a>
        </div>
    </div>

    <div class="category-section pb-4 mb-4">
        <div class="container">
            <div class="section-flex align-items-center justify-content-between">
                <h3 class="section-title">Trending Category</h3>
                <a href="#" class="view-all">Check All Categories <i class="ti ti-arrow-narrow-right"></i>
                </a>
            </div>
            <div class="swiper category-slider">
                <div class="swiper-wrapper">
                    @foreach ($categories as $item)
                        <div class="swiper-slide">
                            <a href="#" class="category-item">
                                <div class="category-badge">7</div>
                                <div class="img"><img src="{{ asset($item->image_path) }}"
                                        alt="{{ $item->name }}"></div>
                                <div class="text">{{ $item->name }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>

    <div class="highlight-section py-4 my-4">
        <div class="container d-flex gap-50px">
            <div class="swiper highlight-slider overflow-hidden" style="width: 50%;">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="product-card d-flex gap-20px align-items-center">
                            <div class="item-img">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                            </div>
                            <div class="item-info">
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                    emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <ul class="product-desc checklist p-0 m-0">
                                    <li>Lorem ipsum dolor sit amet.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                </ul>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="product-card d-flex gap-20px align-items-center">
                            <div class="item-img">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                            </div>
                            <div class="item-info">
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                    emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <ul class="product-desc checklist p-0 m-0">
                                    <li>Lorem ipsum dolor sit amet.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                </ul>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="product-card d-flex gap-20px align-items-center">
                            <div class="item-img">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                            </div>
                            <div class="item-info">
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                    emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <ul class="product-desc checklist p-0 m-0">
                                    <li>Lorem ipsum dolor sit amet.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                </ul>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
            <div class="swiper highlight-slider2 overflow-hidden" style="width: 50%;">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="product-card d-flex gap-20px align-items-center">
                            <div class="item-img">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                            </div>
                            <div class="item-info">
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                    emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <ul class="product-desc checklist p-0 m-0">
                                    <li>Lorem ipsum dolor sit amet.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                </ul>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="product-card d-flex gap-20px align-items-center">
                            <div class="item-img">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                            </div>
                            <div class="item-info">
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                    emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <ul class="product-desc checklist p-0 m-0">
                                    <li>Lorem ipsum dolor sit amet.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                </ul>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="product-card d-flex gap-20px align-items-center">
                            <div class="item-img">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                            </div>
                            <div class="item-info">
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                    emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <ul class="product-desc checklist p-0 m-0">
                                    <li>Lorem ipsum dolor sit amet.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                </ul>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="product-card d-flex gap-20px align-items-center">
                            <div class="item-img">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                            </div>
                            <div class="item-info">
                                <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                    emmet</a>
                                <div class="price-container">
                                    <span class="original-price">$87,99</span>
                                    <span class="current-price">$81,99</span>
                                </div>
                                <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                                <ul class="product-desc checklist p-0 m-0">
                                    <li>Lorem ipsum dolor sit amet.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                    <li>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Nemo, earum.</li>
                                </ul>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>

    <div class="popular-section pt-4 mt-4">
        <div class="container">
            <div class="section-flex align-items-center justify-content-between">
                <h3 class="section-title">Popular Products</h3>
                {{-- <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-all-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-all" type="button" role="tab" aria-controls="pills-all"
                            aria-selected="true"><i class="ti ti-list"></i> All Products</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-best-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-best" type="button" role="tab" aria-controls="pills-best"
                            aria-selected="false"><i class="ti ti-trending-up"></i>
                            Best Selling</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-top-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-top" type="button" role="tab" aria-controls="pills-top"
                            aria-selected="false"><i class="ti ti-star"></i> Top Rated</button>
                    </li>
                </ul> --}}
                <a href="#" class="view-all">Check All Categories <i class="ti ti-arrow-narrow-right"></i>
                </a>
            </div>
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-all" role="tabpanel"
                    aria-labelledby="pills-all-tab">
                    <div class="owl-carousel all-carousel">
                        @foreach ($popular_products as $item)
                            <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset($item->thumbnail) }}"
                                    alt="{{ $item->name }}">
                            </div>
                            <a href="product-view.html" class="product-name line-2">{{ $item->name }}</a>
                            <div class="price-container">
                                <span class="original-price">৳ {{ $item->product_prices[0]->previous_price ?? '0' }}</span>
                                <span class="current-price">৳ {{ $item->product_prices[0]->selling_price ?? '0' }}</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            {{-- <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div> --}}
                        </div>
                        @endforeach
                    </div>
                </div>
                {{-- <div class="tab-pane fade" id="pills-best" role="tabpanel" aria-labelledby="pills-best-tab">
                    <div class="owl-carousel tabpanel-carousel">
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-top" role="tabpanel" aria-labelledby="pills-top-tab">
                    <div class="owl-carousel tabpanel-carousel">
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-card item">
                            <div class="discount-badge">7%</div>
                            <div class="product-image">
                                <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg"
                                    alt="Lorem ipsum dolor sit amet">
                            </div>
                            <a href="product-view.html" class="product-name line-2">Lorem ipsum dolor sit et
                                emmet</a>
                            <div class="price-container">
                                <span class="original-price">$87,99</span>
                                <span class="current-price">$81,99</span>
                            </div>
                            <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                            <div class="action-buttons">
                                <div class="icon-buttons">
                                    <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                        <i class="icon-button pointer ti ti-heart"></i>
                                    </span>
                                    <span class="hover-tooltip" data-tooltip="View Product">
                                        <i class="icon-button pointer ti ti-eye"></i>
                                    </span>
                                </div>
                                <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                                    <i class="fa-solid fa-bag-shopping"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>

    <div class="category-product-section py-4 my-4">
        <div class="container">
            <h2 class="section-title recom-title">Recommended For You</h2>
            <div class="owl-carousel category1-carousel">
                @foreach ($recommended_products as $item)
                    <div class="product-card item">
                    <div class="discount-badge">7%</div>
                    <div class="product-image">
                        <img src="{{asset($item->image)}}" alt="{{ $item->name }}">
                    </div>
                    <a href="product-view.html" class="product-name line-2">{{ $item->name }}</a>
                    <div class="price-container">
                        <span class="original-price">৳ {{ $item->product_prices[0]->previous_price ?? '0' }}</span>
                        <span class="current-price">৳ {{ $item->product_prices[0]->selling_price ?? '0' }}</span>
                    </div>
                    <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                    {{-- <div class="action-buttons">
                        <div class="icon-buttons">
                            <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                <i class="icon-button pointer ti ti-heart"></i>
                            </span>
                            <span class="hover-tooltip" data-tooltip="View Product">
                                <i class="icon-button pointer ti ti-eye"></i>
                            </span>
                        </div>
                        <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </button>
                    </div> --}}
                </div>
                @endforeach

            </div>
        </div>
    </div>

    <div class="category-product-section py-4 my-4">
        @foreach ($product_categories as $item)
            <div class="container">
            <h2 class="section-title recom-title">{{ $item->name }}</h2>
            <div class="owl-carousel category2-carousel">
                @foreach ($item->products as $product)
                    <div class="product-card item">
                    <div class="discount-badge">7%</div>
                    <div class="product-image">
                        <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}">
                    </div>
                    <a href="#" class="product-name line-2">{{ $product->name }}</a>
                    <div class="price-container">
                        <span class="original-price">৳ {{ $product->product_prices[0]->previous_price ?? '0' }}</span>
                        <span class="current-price">৳ {{ $product->product_prices[0]->selling_price ?? '0' }}</span>
                    </div>
                    <div class="stock-info">IN STOCK: <span class="stock-count">7</span></div>
                    {{-- <div class="action-buttons">
                        <div class="icon-buttons">
                            <span class="hover-tooltip" data-tooltip="Add to Wishlist">
                                <i class="icon-button pointer ti ti-heart"></i>
                            </span>
                            <span class="hover-tooltip" data-tooltip="View Product">
                                <i class="icon-button pointer ti ti-eye"></i>
                            </span>
                        </div>
                        <button class="add-to-cart hover-tooltip" data-tooltip="Add to Cart">
                            <i class="fa-solid fa-bag-shopping"></i>
                        </button>
                    </div> --}}
                </div>
                @endforeach

            </div>
        </div>
        @endforeach
        
    </div>

    {{-- <div class="category-product-section py-4 my-4">
        <div class="container">
            <div class="section-flex align-items-center justify-content-between">
                <h3 class="section-title">Product Reviews</h3>
                <a href="#" class="view-all">Check Products <i class="ti ti-arrow-narrow-right"></i> </a>
            </div>
            <div class="owl-carousel reviews-carousel">
                <div class="product-review-card">
                    <div class="reviewer-section">
                        <div class="user-avatar">
                            <img src="{{ asset('frontEnd/assets/') }}/image/profile.png" alt="">
                        </div>
                        <div class="reviewer-info">
                            <div class="reviewer-name">John Doe</div>
                            <div class="reviewer-role">Reviewer</div>
                        </div>
                    </div>

                    <div class="rating-section">
                        <div class="star-rating">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <div class="rating-value">5/5</div>
                    </div>

                    <div class="review-content-section">
                        <div class="review-text">
                            Electronic products are developing a little more every day to make our lives easier. They
                            adapt to the
                            developing digital world.
                        </div>
                        <div class="review-date pb-0">2 years ago</div>
                    </div>

                    <div class="product-section">
                        <div class="product-image">
                            <img src="{{ asset('frontEnd/assets/') }}/image/product.jpg" alt="">
                        </div>
                        <div class="product-name">Samsung Powerbank</div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="banner-section pb-4 mb-4">
        <div class="container">
            <a href="#" class="w-100 img">
                <img src="{{ asset('frontEnd/assets/') }}/image/big-banner.png" alt="">
            </a>
        </div>
    </div>

    <div class="category-section pb-4 mb-0" style="background-color: #fff;">
        <div class="container">
            <div class="section-flex align-items-center justify-content-between">
                <h3 class="section-title">Search By Brand</h3>
                <a href="#" class="view-all">Check All Brands <i class="ti ti-arrow-narrow-right"></i> </a>
            </div>
            <div class="swiper brand-slider overflow-hidden">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                    <div class="swiper-slide">
                        <a href="product-brand.html" class="category-item">
                            <div class="img"><img src="{{ asset('frontEnd/assets/') }}/image/logo.png"
                                    alt=""></div>
                        </a>
                    </div>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>

    <section class="service-section">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="service-item">
                <div class="service-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="service-content">
                    <h5 class="service-title">
                        <span>24/7 Support</span>
                    </h5>
                    <p class="service-description">
                        Lorem ipsum dolor sit consectetur.
                    </p>
                </div>
            </div>
            <div class="service-item">
                <div class="service-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="service-content">
                    <h5 class="service-title">
                        <span>Fast Delivery</span>
                    </h5>
                    <p class="service-description">
                        Lorem ipsum dolor sit consectetur.
                    </p>
                </div>
            </div>
            <div class="service-item">
                <div class="service-icon">
                    <i class="fa-solid fa-cart-flatbed"></i>
                </div>
                <div class="service-content">
                    <h5 class="service-title">
                        <span>Free Shipping</span>
                    </h5>
                    <p class="service-description">
                        Lorem ipsum dolor sit consectetur.
                    </p>
                </div>
            </div>
            <div class="service-item">
                <div class="service-icon">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <div class="service-content">
                    <h5 class="service-title">
                        <span>Flexible Payment</span>
                    </h5>
                    <p class="service-description">
                        Lorem ipsum dolor sit consectetur.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
