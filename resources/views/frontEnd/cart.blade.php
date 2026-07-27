@extends('frontEnd.layouts.master')

@section('content')
<section class="cart-page">
    <div class="container">

        {{-- Breadcrumb --}}
        <div class="cart-breadcrumb">
            <a href="{{ route('home') }}"><i class="fas fa-home"></i></a>
            <span>&rarr;</span>
            <span>Cart</span>
        </div>

        <div class="row g-3">

            {{-- ===== LEFT: Cart Items ===== --}}
            <div class="col-lg-8">

                {{-- Heading row --}}
                <div class="cart-heading-row">
                    <span class="cart-count-text">
                        You have (<span id="cp-item-count">{{ count($cart['items']) }}</span>) item(s) in your cart
                    </span>
                    <a href="#" class="need-help">
                        <i class="fas fa-circle-info me-1"></i> Need Help?
                    </a>
                </div>

                {{-- Check all / Delete row --}}
                <div class="cart-action-row">
                    <label class="cart-check-all">
                        <input type="checkbox" id="checkAllItems"> Check All
                    </label>
                    <button class="cart-delete-selected" id="deleteSelectedBtn">Delete</button>
                </div>

                {{-- Items --}}
                <div id="cp-items-wrap">
                    @if(count($cart['items']))
                        @foreach($cart['items'] as $key => $it)
                        <div class="cart-item-card" data-product-id="{{ $it['id'] }}" data-cart-key="{{ $it['key'] ?? $key }}">
                            {{-- Checkbox --}}
                            <div class="item-checkbox">
                                <input type="checkbox" class="item-select" value="{{ $it['key'] ?? $key }}">
                            </div>
                            {{-- Image --}}
                            <img src="{{ asset($it['thumbnail'] ?? 'frontEnd/assets/image/product.jpg') }}"
                                 alt="{{ $it['name'] }}" class="item-img">
                            {{-- Details --}}
                            <div class="item-details">
                                <div class="item-name">{{ $it['name'] }}</div>
                                @if(!empty($it['variant_label']))
                                    <div class="item-variant text-muted small">{{ $it['variant_label'] }}</div>
                                @endif
                                <div class="item-price-line">Tk {{ number_format($it['price']) }}</div>
                                <div class="item-discount">Ecom Discount Tk 0</div>
                            </div>
                            {{-- Actions --}}
                            <div class="item-actions">
                                <button class="change-emi-btn">Change to EMI</button>
                                <div class="item-qty-row">
                                    <button class="qty-btn-cp cp-decrease" data-cart-key="{{ $it['key'] ?? $key }}" data-qty="{{ max(1, $it['qty'] - 1) }}">&#8722;</button>
                                    <input type="text" class="qty-num" value="{{ $it['qty'] }}" readonly>
                                    <button class="qty-btn-cp cp-increase" data-cart-key="{{ $it['key'] ?? $key }}" data-qty="{{ $it['qty'] + 1 }}">&#43;</button>
                                    <span class="item-line-total">Tk {{ number_format($it['price'] * $it['qty']) }}</span>
                                </div>
                                <button class="item-remove-cp cp-remove" data-cart-key="{{ $it['key'] ?? $key }}" title="Remove">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-5 bg-white border rounded">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-3">Your cart is empty.</p>
                            <a href="{{ route('shop') }}" class="btn btn-dark px-4">Continue Shopping</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===== RIGHT: Price Summary ===== --}}
            <div class="col-lg-4">
                <div class="price-summary-card">
                    <div class="ps-header">Price Details</div>
                    <div class="ps-body">
                        <div class="ps-row">
                            <span>Sub Total</span>
                            <span>Tk <span id="cp-subtotal">{{ number_format($cart['total']) }}</span></span>
                        </div>
                        <div class="ps-row">
                            <span>Ecom Discount</span>
                            <span class="discount-val">Tk <span id="cp-discount">0</span></span>
                        </div>
                        <div class="ps-row ps-total">
                            <span>Total</span>
                            <span>Tk <span id="cp-total">{{ number_format($cart['total']) }}</span></span>
                        </div>

                        <a href="{{ route('shop') }}" class="ps-continue-btn">Continue Shopping</a>
                        <a href="{{ route('checkout.index') }}" class="ps-checkout-btn">Checkout</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ---- Check All ----
    $('#checkAllItems').on('change', function () {
        $('.item-select').prop('checked', $(this).is(':checked'));
    });

    // ---- Delete Selected ----
    $('#deleteSelectedBtn').on('click', function () {
        var selected = [];
        $('.item-select:checked').each(function () { selected.push($(this).val()); });
        if (!selected.length) {
            toastr.warning('Please select items to delete.');
            return;
        }
        if (!confirm('Remove selected items from cart?')) return;
        // Remove each selected item via AJAX
        var requests = selected.map(function (key) {
            return $.ajax({ url: '{{ route("cart.remove") }}', method: 'POST', data: { _token: csrfToken, cart_key: key } });
        });
        $.when.apply($, requests).then(function () {
            var lastArg = arguments[arguments.length - 1];
            // last response has cart data
            var resp = Array.isArray(arguments[0]) ? arguments[0][0] : (lastArg ? lastArg[0] : null);
            // Just reload to reflect correctly after bulk delete
            location.reload();
        }).fail(function () { toastr.error('Failed to remove items.'); });
    });

    // ---- Qty Decrease ----
    $(document).on('click', '.cp-decrease', function () {
        var key = $(this).data('cart-key');
        var qty = parseInt($(this).data('qty'));
        if (qty < 1) qty = 1;
        updateCartItem(key, qty, $(this));
    });

    // ---- Qty Increase ----
    $(document).on('click', '.cp-increase', function () {
        var key = $(this).data('cart-key');
        var qty = parseInt($(this).data('qty'));
        updateCartItem(key, qty, $(this));
    });

    // ---- Remove Item ----
    $(document).on('click', '.cp-remove', function () {
        var key = $(this).data('cart-key');
        var card = $(this).closest('.cart-item-card');
        $.ajax({
            url: '{{ route("cart.remove") }}',
            method: 'POST',
            data: { _token: csrfToken, cart_key: key },
            success: function (res) {
                card.fadeOut(200, function () { card.remove(); });
                updateCartPageSummary(res.cart);
            },
            error: function () { toastr.error('Failed to remove item.'); }
        });
    });

    function updateCartItem(key, qty, btnEl) {
        $.ajax({
            url: '{{ route("cart.update") }}',
            method: 'POST',
            data: { _token: csrfToken, cart_key: key, qty: qty },
            success: function (res) {
                var card = btnEl.closest('.cart-item-card');
                var item = res.cart.items[key];
                if (item) {
                    card.find('.qty-num').val(item.qty);
                    card.find('.cp-decrease').data('qty', Math.max(1, item.qty - 1));
                    card.find('.cp-increase').data('qty', item.qty + 1);
                    card.find('.item-line-total').text('Tk ' + numberFormat(item.price * item.qty));
                }
                updateCartPageSummary(res.cart);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.error : 'Failed to update.';
                toastr.error(msg);
            }
        });
    }

    function updateCartPageSummary(cart) {
        var total = cart.total || 0;
        $('#cp-subtotal').text(numberFormat(total));
        $('#cp-total').text(numberFormat(total));
        $('#cp-item-count').text(Object.keys(cart.items).length);
        // Update sidebar too
        $('#cs-items-wrap').html(cart.cart_items_html || '');
        $('.total-cart, .cs-count').text(cart.count);
        $('.cs-total-val').text(numberFormat(total));
    }

    function numberFormat(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
});
</script>
@endsection
