@php
    // Items are admin-managed and live in the section's `config` JSON (one row
    // per trust badge). Nothing is rendered from a hardcoded list at runtime —
    // the defaults below only stand in until the section is configured, so a
    // freshly seeded site still looks complete.
    $items = collect($section->config['items'] ?? [])
        ->filter(fn ($item) => filled($item['title'] ?? null))
        ->values();

    if ($items->isEmpty()) {
        $items = collect([
            ['icon' => 'fas fa-leaf',        'title' => '100% Pure & Organic', 'subtitle' => 'Lab tested quality'],
            ['icon' => 'fas fa-truck-fast',  'title' => 'Fast Home Delivery',  'subtitle' => 'Inside Dhaka in 24 hrs'],
            ['icon' => 'fas fa-hand-holding-dollar', 'title' => 'Cash On Delivery', 'subtitle' => 'Pay after verification'],
            ['icon' => 'fas fa-rotate-left', 'title' => 'Hassle-free Returns', 'subtitle' => '7 day money back'],
        ]);
    }
@endphp
@if($items->count())
<div class="feature-strip">
    <div class="container">
        {{-- The column count follows the number of configured items so three or
             five badges lay out evenly instead of leaving a hole in a fixed grid. --}}
        <div class="feature-strip-inner" style="--feature-cols: {{ $items->count() }}">
            @foreach ($items as $item)
                <div class="feature-item">
                    <span class="feature-icon"><i class="{{ $item['icon'] ?? 'fas fa-circle-check' }}"></i></span>
                    <span class="feature-text">
                        <strong>{{ $item['title'] }}</strong>
                        @if(filled($item['subtitle'] ?? null))
                            <span>{{ $item['subtitle'] }}</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
