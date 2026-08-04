@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')

    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Product reviews</h3>
            <a href="{{ route('buyer.orders') }}" class="ba-panel-action">My orders</a>
        </div>
        <div class="ba-panel-body is-flush">
            @forelse($reviews as $review)
            <div style="display:flex; gap:14px; padding:16px 20px; border-bottom:1px solid #f2f3f5">
                <img src="{{ asset($review->product->thumbnail ?? 'frontEnd/assets/image/product.jpg') }}"
                     alt="{{ $review->product->name ?? 'Product' }}"
                     style="width:56px; height:56px; object-fit:contain; border:1px solid #f0f0f0; border-radius:8px; background:#fafafa; padding:4px; flex-shrink:0">
                <div style="flex:1; min-width:0">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <div>
                            @if($review->product)
                                <a href="{{ route('product.details', $review->product->slug) }}"
                                   style="font-size:13.5px; font-weight:700; color:#111827; text-decoration:none">
                                    {{ $review->product->name }}
                                </a>
                            @else
                                <span style="font-size:13.5px; font-weight:700; color:#111827">Product removed</span>
                            @endif
                            <div style="margin-top:3px">
                                @for($star = 1; $star <= 5; $star++)
                                    <i class="fas fa-star" style="font-size:11.5px; color:{{ $star <= $review->rating ? '#f59e0b' : '#e5e7eb' }}"></i>
                                @endfor
                                <span style="font-size:11.5px; color:#9ca3af; margin-left:4px">{{ $review->created_at?->format('d M Y') }}</span>
                            </div>
                        </div>
                        <span class="ba-pill {{ $review->is_approved ? 'ba-pill-done' : 'ba-pill-pending' }}">
                            {{ $review->is_approved ? 'Published' : 'Awaiting approval' }}
                        </span>
                    </div>
                    @if($review->title)
                        <div style="font-size:13px; font-weight:600; color:#374151; margin-top:8px">{{ $review->title }}</div>
                    @endif
                    @if($review->comment)
                        <div style="font-size:12.5px; color:#6b7280; line-height:1.6; margin-top:3px">{{ $review->comment }}</div>
                    @endif
                </div>
            </div>
            @empty
            <div class="ba-empty">
                <i class="fas fa-star"></i>
                <p>You have not reviewed any products yet.</p>
                <a href="{{ route('buyer.orders') }}" class="ba-btn-sm ba-btn-primary">Review a purchase</a>
            </div>
            @endforelse
        </div>
        @if($reviews->hasPages())
        <div class="ba-panel-body" style="border-top:1px solid #f1f2f4">{{ $reviews->links() }}</div>
        @endif
    </div>

@include('frontEnd.buyer.partials.layout-end')
@endsection
