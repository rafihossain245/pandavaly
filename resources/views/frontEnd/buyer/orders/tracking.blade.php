@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')

    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Order tracking</h3>
            <a href="{{ route('buyer.orders.show', $order) }}" class="ba-panel-action">&larr; Back to details</a>
        </div>
        <div class="ba-panel-body">

            <div class="ba-track-cards">
                <div class="ba-track-card">
                    <div class="ba-stat-icon ba-ic-blue"><i class="fas fa-box"></i></div>
                    <div class="ba-track-card-value">#{{ $order->order_no }}</div>
                    <div class="ba-track-card-label">Order number</div>
                </div>
                <div class="ba-track-card">
                    <div class="ba-stat-icon ba-ic-orange"><i class="fas fa-calendar-check"></i></div>
                    <div class="ba-track-card-value">
                        {{ $cancelled ? '—' : $estimated?->format('F d, Y') }}
                    </div>
                    <div class="ba-track-card-label">Estimated delivery date</div>
                </div>
                <div class="ba-track-card">
                    <div class="ba-stat-icon ba-ic-green"><i class="fas fa-basket-shopping"></i></div>
                    <div class="ba-track-card-value">{{ $order->items_count }} item{{ $order->items_count === 1 ? '' : 's' }}</div>
                    <div class="ba-track-card-label">Total products</div>
                </div>
            </div>

            <h4 class="text-center fw-bold mb-4" style="font-size:20px">Order status</h4>

            @if($cancelled)
                <div class="ba-timeline-item" style="background:#fef2f2; border-color:#fecaca">
                    <i style="background:#dc2626"><i class="fas fa-xmark"></i></i>
                    <div>
                        <div class="ba-timeline-title" style="color:#991b1b">Order cancelled</div>
                        <div class="ba-timeline-sub" style="color:#b91c1c">This order is no longer being processed.</div>
                        <div class="ba-timeline-time" style="color:#c86a6a">{{ $order->updated_at->format('d M, Y &nbsp; h:i a') }}</div>
                    </div>
                </div>
            @else
                <div class="ba-steps">
                    @foreach($stages as $index => $label)
                    @php
                        $number  = $index + 1;
                        $isDone  = $number < $stage;
                        $current = $number === $stage;
                    @endphp
                    <div class="ba-step {{ $isDone ? 'is-done' : ($current ? 'is-current' : '') }}">
                        <div class="ba-step-dot">
                            @if($isDone)<i class="fas fa-check"></i>@else{{ $number }}@endif
                        </div>
                        <div class="ba-step-label">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="ba-timeline-item">
                    <i><i class="fas fa-check"></i></i>
                    <div>
                        <div class="ba-timeline-title">{{ $stages[$stage - 1] }}</div>
                        <div class="ba-timeline-sub">
                            @switch($stage)
                                @case(1) We have received your order @break
                                @case(2) Your order is being prepared @break
                                @case(3) Your order is on the way @break
                                @default Your order has been delivered
                            @endswitch
                        </div>
                        <div class="ba-timeline-time">{{ $order->updated_at->format('d M, Y') }} &nbsp; {{ $order->updated_at->format('h:i a') }}</div>
                    </div>
                </div>
            @endif

            <div class="text-center mt-4">
                <a href="{{ route('buyer.orders.show', $order) }}" class="ba-btn-sm ba-btn-dark">
                    <i class="fas fa-eye me-1"></i> View Order
                </a>
            </div>

        </div>
    </div>

@include('frontEnd.buyer.partials.layout-end')
@endsection
