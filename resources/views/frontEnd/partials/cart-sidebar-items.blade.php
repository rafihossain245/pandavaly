@if(count($cart['items']))
    @foreach($cart['items'] as $key => $item)
    <div class="cs-item" data-product-id="{{ $item['id'] }}" data-cart-key="{{ $item['key'] ?? $key }}">
        <img src="{{ asset($item['thumbnail'] ?? 'frontEnd/assets/image/product.jpg') }}"
             alt="{{ $item['name'] }}" class="cs-item-img">
        <div class="cs-item-info">
            <div class="cs-item-name">{{ $item['name'] }}</div>
            @if(!empty($item['variant_label']))
                <div class="cs-item-variant text-muted small">{{ $item['variant_label'] }}</div>
            @endif
            <div class="cs-item-price">Tk {{ number_format($item['price']) }} &times; {{ $item['qty'] }}</div>
        </div>
        <button class="cs-item-remove sidebar-remove-btn" data-cart-key="{{ $item['key'] ?? $key }}" title="Remove">
            <i class="fas fa-trash"></i>
        </button>
    </div>
    @endforeach
@else
    <div class="cs-empty">
        <i class="fas fa-shopping-cart"></i>
        <p>Your cart is empty</p>
    </div>
@endif
