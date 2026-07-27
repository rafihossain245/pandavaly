<div class="table-responsive">
    <table class="table buyer-table align-middle mb-0">
        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Items</th><th class="text-end">Total</th><th></th></tr></thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td class="fw-semibold">{{ $order->order_no }}</td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td><span class="buyer-badge {{ $order->status }}">{{ $order->status }}</span></td>
                    <td>{{ $order->items_count ?? $order->items()->count() }}</td>
                    <td class="text-end">৳{{ number_format($order->total, 2) }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('buyer.orders.show', $order) }}">Details</a>
                        @if(in_array($order->status, ['delivered', 'completed']))
                            <form action="{{ route('buyer.orders.reorder', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Reorder</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
