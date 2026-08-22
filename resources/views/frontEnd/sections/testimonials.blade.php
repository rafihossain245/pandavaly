@php
    // Normalised in HomeController::resolveTestimonials() so this view renders
    // curated entries and real product reviews through the same shape:
    // ['name', 'role', 'rating', 'body', 'avatar', 'verified'].
    $testimonials = $section->resolvedData;
@endphp
@if($testimonials && $testimonials->count())
<div class="testimonial-section">
    <div class="container">
        <div class="section-head-center">
            <h3 class="section-title">{{ $section->heading ?: $section->title }}</h3>
            @if($section->subheading)
                <p class="section-subtitle">{{ $section->subheading }}</p>
            @endif
        </div>
        <div class="testimonial-grid">
            @foreach ($testimonials as $item)
                @php
                    $rating = (int) ($item['rating'] ?? 5);
                    $rating = max(1, min(5, $rating));
                @endphp
                <div class="testimonial-card">
                    <span class="testimonial-quote">&rdquo;</span>
                    <div class="testimonial-stars" aria-label="{{ $rating }} out of 5 stars">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="{{ $i <= $rating ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </div>
                    <p class="testimonial-body">{{ $item['body'] }}</p>
                    <div class="testimonial-person">
                        @if(filled($item['avatar'] ?? null))
                            <img class="testimonial-avatar" src="{{ asset($item['avatar']) }}" alt="{{ $item['name'] }}">
                        @else
                            <span class="testimonial-avatar-fallback">{{ Str::upper(Str::substr($item['name'], 0, 1)) }}</span>
                        @endif
                        <span>
                            <span class="testimonial-name">{{ $item['name'] }}</span>
                            <span class="testimonial-role">
                                @if(filled($item['role'] ?? null))
                                    {{ $item['role'] }}
                                @endif
                                @if($item['verified'] ?? false)
                                    <span class="testimonial-verified">
                                        <i class="fas fa-circle-check"></i> Verified
                                    </span>
                                @endif
                            </span>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
