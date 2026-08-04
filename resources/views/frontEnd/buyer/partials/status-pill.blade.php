@php
    $tone = match ($status) {
        'pending', 'payment_requested' => 'pending',
        'cancelled' => 'cancel',
        'delivered', 'completed' => 'done',
        default => 'progress',
    };
@endphp
<span class="ba-pill ba-pill-{{ $tone }}">{{ str_replace('_', ' ', $status) }}</span>
