@extends('frontEnd.layouts.master')

@section('content')
@include('frontEnd.buyer.partials.layout-start')

    <div class="ba-panel">
        <div class="ba-panel-head">
            <h3 class="ba-panel-title">Promo / Coupon</h3>
            <a href="{{ route('shop') }}" class="ba-panel-action">Start shopping</a>
        </div>
        <div class="ba-panel-body">
            <p style="font-size:13px; color:#6b7280; margin-bottom:18px">
                Copy a code and paste it into the <strong>&ldquo;Have any coupon or gift voucher?&rdquo;</strong> box at checkout.
            </p>

            @forelse($coupons as $coupon)
            <div class="d-flex align-items-center gap-3 flex-wrap" style="border:1px dashed #dfe2e7; border-radius:10px; padding:15px 18px; margin-bottom:12px">
                <div class="ba-stat-icon ba-ic-orange"><i class="fas fa-ticket"></i></div>
                <div style="flex:1; min-width:170px">
                    <div style="font-family:ui-monospace,Menlo,monospace; font-size:15px; font-weight:800; letter-spacing:.6px; color:#111827">
                        {{ $coupon->code }}
                    </div>
                    <div style="font-size:12.5px; color:#6b7280; margin-top:3px">
                        {{ $coupon->label }}
                        @if($coupon->type === 'percent' && $coupon->max_discount)
                            (up to {{ number_format($coupon->max_discount, 2) }} BDT)
                        @endif
                        @if($coupon->min_order_amount)
                            &middot; min order {{ number_format($coupon->min_order_amount, 2) }} BDT
                        @endif
                    </div>
                    @if($coupon->description)
                        <div style="font-size:11.5px; color:#9ca3af; margin-top:2px">{{ $coupon->description }}</div>
                    @endif
                </div>
                <div class="text-end">
                    @if($coupon->ends_at)
                        <div style="font-size:11.5px; color:#9ca3af; margin-bottom:6px">
                            Expires {{ $coupon->ends_at->format('d M Y') }}
                        </div>
                    @endif
                    <button type="button" class="ba-btn-sm ba-btn-primary ba-copy" data-code="{{ $coupon->code }}">
                        <i class="fas fa-copy me-1"></i> Copy code
                    </button>
                </div>
            </div>
            @empty
            <div class="ba-empty">
                <i class="fas fa-ticket"></i>
                <p>No promo codes are running right now. Check back soon.</p>
            </div>
            @endforelse
        </div>
    </div>

@include('frontEnd.buyer.partials.layout-end')
@endsection

@section('scripts')
<script>
jQuery(function ($) {
    $('.ba-copy').on('click', function () {
        var $btn = $(this), code = $btn.data('code');

        function done() {
            var original = $btn.html();
            $btn.html('<i class="fas fa-check me-1"></i> Copied');
            setTimeout(function () { $btn.html(original); }, 1600);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(done);
            return;
        }

        // Fallback for plain http, where the async clipboard API is unavailable.
        var $tmp = $('<input>').val(code).appendTo('body').select();
        document.execCommand('copy');
        $tmp.remove();
        done();
    });
});
</script>
@endsection
