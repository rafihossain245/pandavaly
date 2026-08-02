@php
    $footerCategories = \App\Models\Category::where('is_active', 1)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->take(7)
        ->get();
@endphp
<footer>
    <div class="container footer-top">
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

            <div class="social-icons">
                <a href="#" class="social-icon" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="social-icon" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" class="social-icon" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            </div>

            <div class="download-app">
                <h3 class="footer-heading">Download App on Mobile :</h3>
                <div class="app-badges">
                    {{-- Only the square store logos ship with this theme, not the
                         official "Get it on Google Play" / "Download on the App
                         Store" badge artwork, so these are rendered as labelled
                         buttons. Swap in the official badges when available. --}}
                    <a href="#" class="app-badge">
                        <img src="{{ asset('frontEnd/assets/') }}/image/google-play.png" alt="">
                        <span><small>GET IT ON</small>Google Play</span>
                    </a>
                    <a href="#" class="app-badge">
                        <img src="{{ asset('frontEnd/assets/') }}/image/app-store.png" alt="">
                        <span><small>Download on the</small>App Store</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-section">
            <h3>Information</h3>
            <ul>
                <li><a href="#">About us</a></li>
                <li><a href="#">Contact us</a></li>
                <li><a href="#">Company Information</a></li>
                <li><a href="#">Our Stories</a></li>
                <li><a href="#">Terms &amp; Conditions</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Careers</a></li>
            </ul>
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

        <div class="footer-section">
            <h3>Support</h3>
            <ul>
                <li><a href="#">Support Center</a></li>
                <li><a href="#">How to Order</a></li>
                <li><a href="{{ route('track-order') }}">Order Tracking</a></li>
                <li><a href="#">Payment</a></li>
                <li><a href="#">Shipping</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Consumer Policy</h3>
            <ul>
                <li><a href="#">Happy Return</a></li>
                <li><a href="#">Refund Policy</a></li>
                <li><a href="#">Exchange</a></li>
                <li><a href="#">Cancellation</a></li>
                <li><a href="#">Pre-Order</a></li>
                <li><a href="#">Extra Discount</a></li>
            </ul>
        </div>
    </div>

    <div class="container footer-bottom">
        <p class="copyright">Copyright &copy; {{ date('Y') }} {{ $setting->title ?? 'GoeBazar' }}</p>
    </div>
</footer>
