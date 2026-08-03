@php
    $navCategories = App\Models\Category::with([
            'sub_categories' => fn ($q) => $q->where('is_active', 1)->orderBy('name'),
        ])
        ->where('is_active', 1)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    $activeCategorySlug = request('category');
@endphp
<nav class="navbar cat-navbar">
    <div class="container">
        <ul class="cat-nav-list">
            <li>
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="ti ti-home"></i> Home
                </a>
            </li>
            <li>
                <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') && !$activeCategorySlug ? 'active' : '' }}">
                    Offer Zone
                </a>
            </li>

            @foreach ($navCategories as $item)
                <li class="{{ $item->sub_categories->count() ? 'has-children' : '' }}">
                    <a href="{{ route('shop', ['category' => $item->slug]) }}"
                        class="{{ $activeCategorySlug === $item->slug ? 'active' : '' }}">
                        {{ $item->name }}
                        @if($item->sub_categories->count())
                            <i class="fas fa-angle-down"></i>
                        @endif
                    </a>
                    @if($item->sub_categories->count())
                        <ul class="cat-nav-dropdown">
                            @foreach ($item->sub_categories as $sub)
                                <li>
                                    <a href="{{ route('shop', ['category' => $item->slug, 'sub_category' => $sub->slug]) }}">
                                        {{ $sub->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach

            <li class="cat-nav-right">
                <a href="{{ route('shop', ['sort' => 'newest']) }}" class="arrival-menu">New Arrival</a>
            </li>
        </ul>
    </div>
</nav>
