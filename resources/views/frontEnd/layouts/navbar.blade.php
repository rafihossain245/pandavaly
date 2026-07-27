@php
    $categories = App\Models\Category::with('sub_categories')->where('is_active',1)->get();
@endphp
<nav class="navbar">
        <div class="container">
            <div class="category-sidebar">
                <div class="toggle-dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                        aria-expanded="false"><i class="ti ti-menu-2"></i> Categories</a>
                    <div class="dropdown-menu {{ request()->routeIs('home') ? 'd-block' : '' }} main-menu" style="min-height: 57vh;">
                        <ul class="main-ul">
                            @foreach ($categories as $item)
                                <li class="has_dropdown">
                                <a href="{{ route('shop', ['category' => $item->slug]) }}" class="menu-item">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-file-info"></i>
                                        <span class="text initial">{{ $item->name }}</span>
                                    </div>
                                    @if($item->sub_categories->count())
                                    <i class="fas fa-angle-right"></i>
                                    @endif
                                </a>
                                @if($item->sub_categories->count())
                                <ul class="sub_menu">
                                    @foreach ($item->sub_categories as $sub)
                                    <li>
                                        <a href="{{ route('shop', ['category' => $item->slug, 'sub_category' => $sub->slug]) }}" class="menu-item">
                                            <div class="d-flex align-items-center">
                                                <i class="ti ti-file-info"></i>
                                                <span class="text initial">{{ $sub->name }}</span>
                                            </div>
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
            </div>
            <div class="right-side">
                <div class="main-menu">
                    <ul class="ps-2">
                        <li>
                            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="ti ti-home"></i>
                                <span class="text">Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'active' : '' }}">
                                <i class="ti ti-shopping-bag"></i>
                                <span class="text">Shop</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="right-menu">
                    <a href="{{ route('shop', ['sort' => 'newest']) }}" class="arrival-menu">
                        <span class="text">New Arrival</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>
