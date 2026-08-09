@php
    $footerCategories = \App\Models\Category::where('is_active', 1)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->take(7)
        ->get();

    // Footer link columns are admin-managed: each active page category is a
    // column, its active pages are the links. Manage under Content > Pages.
    $footerColumns = \App\Models\PageCategory::active()
        ->ordered()
        ->with('activePages')
        ->get()
        ->filter(fn ($column) => $column->activePages->isNotEmpty());
@endphp
<footer>
    {{-- Tells the grid how many link columns it has to lay out: "Shop By" plus
         however many page categories are being rendered. Set as a custom property
         rather than grid-template-columns so the mobile media queries still win. --}}
    <div class="container footer-top" style="--footer-link-cols: {{ 1 + $footerColumns->count() }}">
        <div class="footer-brand">
            <a class="footer-logo" href="{{ route('home') }}">
                <img src="{{ asset($setting->logo_path ?? 'frontEnd/assets/image/logo.png') }}"
                    alt="{{ $setting->title ?? 'GoeBazar' }}" style="height:42px">
            </a>

            <p class="footer-tagline">
                {{ $setting->title ?? 'GoeBazar' }} is an e-commerce platform dedicated to providing
                safe and reliable food to every home.
            </p>

            <ul class="footer-contact-list">
                @if($setting->address ?? null)
                    <li><i class="fas fa-map-marker-alt"></i> <span>{{ $setting->address }}</span></li>
                @endif
                @if($setting->contact_phone ?? null)
                    <li><i class="fas fa-phone"></i> <a href="tel:{{ $setting->contact_phone }}">{{ $setting->contact_phone }}</a></li>
                @endif
                @if($setting->contact_email ?? null)
                    <li><i class="far fa-envelope"></i> <a href="mailto:{{ $setting->contact_email }}">{{ $setting->contact_email }}</a></li>
                @endif
                @if(!($setting->address ?? null) && !($setting->contact_phone ?? null) && !($setting->contact_email ?? null))
                    <li class="footer-contact-empty">Add your address, phone and email in Website Settings.</li>
                @endif
            </ul>

            {{-- Managed in Website Settings; the block disappears entirely when no
                 profile has been filled in, rather than showing dead links. --}}
            @if ($setting?->hasSocialLinks())
                <div class="social-icons">
                    @foreach ($setting->socialLinks() as $social)
                        <a href="{{ $social['url'] }}" class="social-icon" target="_blank" rel="noopener"
                           aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}">
                            <i class="{{ $social['icon'] }}"></i>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>

        <div class="footer-section">
            <h3>Shop By</h3>
            <ul>
                @forelse($footerCategories as $cat)
                    <li><a href="{{ route('shop', ['category' => $cat->slug]) }}">{{ $cat->name }}</a></li>
                @empty
                    <li><a href="{{ route('shop') }}">All Products</a></li>
                @endforelse
            </ul>
        </div>

        @foreach($footerColumns as $column)
            <div class="footer-section">
                <h3>{{ $column->name }}</h3>
                <ul>
                    @foreach($column->activePages as $page)
                        <li><a href="{{ $page->url() }}">{{ $page->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    <div class="container footer-bottom">
        <p class="copyright">Copyright &copy; {{ date('Y') }} {{ $setting->title ?? 'GoeBazar' }}</p>
    </div>
</footer>
