@extends('layout.app')

@section('meta-information')
    <title>Dashboard</title>
@endsection

@section('import-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('css')
<style>
    /*
      A responsive Chart.js canvas sizes itself from its parent. With
      maintainAspectRatio:false the parent's HEIGHT is what it reads, so that
      parent must have a definite height and hold nothing but the canvas —
      otherwise the parent grows with the canvas, the ResizeObserver fires,
      the canvas grows again, and the bitmap expands every frame until the
      browser runs out of memory. Do not put the card heading inside this box,
      and do not give the canvas a height attribute.
    */
    .dash-chart {
        position: relative;
        height: 260px;
        width: 100%;
    }

    @media (max-width: 640px) {
        .dash-chart { height: 210px; }
    }
</style>
@endsection


@section('main-content')
    @php
        // Every admin link needs the {role} route parameter, resolved the same way
        // the sidebar and the order list resolve it.
        $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());

        $cards = [
            [
                'label' => 'Total Sales',
                'value' => 'Tk ' . number_format($stats['sales']['total'], 2),
                'metric' => $stats['sales'],
                'icon' => 'fa-coins',
                'accent' => 'border-blue-500',
                'chip' => 'bg-blue-100 text-blue-600',
                'note' => 'Cancelled orders excluded',
            ],
            [
                'label' => 'Total Orders',
                'value' => number_format($stats['orders']['total']),
                'metric' => $stats['orders'],
                'icon' => 'fa-shopping-cart',
                'accent' => 'border-purple-500',
                'chip' => 'bg-purple-100 text-purple-600',
                'note' => null,
            ],
            [
                'label' => 'Customers',
                'value' => number_format($stats['customers']['total']),
                'metric' => $stats['customers'],
                'icon' => 'fa-users',
                'accent' => 'border-green-500',
                'chip' => 'bg-green-100 text-green-600',
                'note' => null,
            ],
            [
                'label' => 'Active Products',
                'value' => number_format($stats['products']['total']),
                'metric' => $stats['products'],
                'icon' => 'fa-box',
                'accent' => 'border-yellow-500',
                'chip' => 'bg-yellow-100 text-yellow-600',
                'note' => null,
                // Catalogue size is a standing total, not a monthly flow. A month
                // with no new products is normal, but as a percentage it reads
                // "-100%" and looks like an outage, so show the count instead.
                'compare' => false,
            ],
        ];
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Welcome {{ Auth::user()->name }} 👋</h1>
        <p class="text-gray-600">Look Out! Here's what's happening today.</p>
    </div>

    {{-- Only rendered when something is actually waiting, so it never becomes
         background noise the admin learns to scroll past. --}}
    @if ($pendingOrderCount || $pendingReviewCount)
        <div class="flex flex-wrap gap-3 mb-6">
            @if ($pendingOrderCount)
                <a href="{{ route('role.orders.index', ['role' => $roleSlug, 'status' => 'pending']) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-2 text-sm font-medium text-yellow-800 hover:bg-yellow-100">
                    <i class="fas fa-clock"></i>
                    {{ $pendingOrderCount }} {{ Str::plural('order', $pendingOrderCount) }} awaiting action
                </a>
            @endif
            @if ($pendingReviewCount)
                <a href="{{ route('role.product-reviews.index', ['role' => $roleSlug]) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-800 hover:bg-blue-100">
                    <i class="fas fa-star"></i>
                    {{ $pendingReviewCount }} {{ Str::plural('review', $pendingReviewCount) }} to moderate
                </a>
            @endif
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        @foreach ($cards as $card)
            <div class="bg-white rounded-xl shadow p-6 border-l-4 {{ $card['accent'] }}">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $card['value'] }}</p>
                    </div>
                    <div class="{{ $card['chip'] }} p-3 rounded-lg">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                </div>

                @php
                    $change = $card['metric']['change'];
                    $added = $card['metric']['current'];
                @endphp
                @if (($card['compare'] ?? true) === false)
                    <p class="text-xs text-gray-500 mt-2">
                        {{ $added > 0 ? '+' . number_format($added) . ' added this month' : 'None added this month' }}
                    </p>
                @elseif (is_null($change))
                    {{-- No baseline last month: quoting a percentage would invent one. --}}
                    <p class="text-xs text-gray-400 mt-2">
                        {{ $card['metric']['current'] > 0 ? 'New this month' : 'Nothing last month to compare' }}
                    </p>
                @else
                    <p class="text-xs mt-2 {{ $change >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        <i class="fas fa-arrow-{{ $change >= 0 ? 'up' : 'down' }} mr-1"></i>
                        {{ number_format(abs($change), 1) }}% from last month
                    </p>
                @endif

                @if ($card['note'])
                    <p class="text-[11px] text-gray-400 mt-1">{{ $card['note'] }}</p>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-4 rounded-xl shadow">
            <h2 class="text-lg font-semibold mb-2">Sales — last 7 days</h2>
            <div class="dash-chart"><canvas id="lineChart"></canvas></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow">
            <h2 class="text-lg font-semibold mb-2">New customers — last 6 months</h2>
            <div class="dash-chart"><canvas id="barChart"></canvas></div>
        </div>
    </div>

    <!-- Recent orders + restock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold">Recent Orders</h2>
                <a href="{{ route('role.orders.index', ['role' => $roleSlug]) }}"
                    class="text-sm text-blue-600 hover:underline">View all</a>
            </div>

            @if ($recentOrders->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-400">No orders yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3 font-semibold">Order</th>
                                <th class="px-5 py-3 font-semibold">Customer</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 font-semibold text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr class="border-t border-gray-100 hover:bg-gray-50">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('role.orders.show', ['role' => $roleSlug, 'order' => $order->id]) }}"
                                            class="font-medium text-blue-600 hover:underline">{{ $order->order_no }}</a>
                                        <div class="text-xs text-gray-400">{{ $order->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700">
                                        {{ $order->buyer->business_name ?? $order->shipping_name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ Str::headline($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-semibold text-gray-800">
                                        Tk {{ number_format($order->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold">Low Stock</h2>
                <a href="{{ route('role.products.index', ['role' => $roleSlug]) }}"
                    class="text-sm text-blue-600 hover:underline">All products</a>
            </div>

            @if ($lowStockProducts->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-400">
                    Nothing at or below {{ $lowStockThreshold }} in stock.
                </p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($lowStockProducts as $product)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                @if ($product->thumbnail)
                                    <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}"
                                        class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400">{{ $product->sku }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->stock_qty > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->stock_qty > 0 ? $product->stock_qty . ' left' : 'Out of stock' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection


@section('raw-script')
    <!-- Chart.js Scripts -->
    <script>
        const salesChart = @json($salesChart);
        const customerChart = @json($customerChart);

        // Line Chart — revenue per day
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: salesChart.labels,
                datasets: [{
                    label: 'Sales (Tk)',
                    data: salesChart.data,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                // Fills .dash-chart, which supplies the definite height this needs.
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: 'rgba(17, 24, 39, 0.06)' },
                        ticks: {
                            callback: value => 'Tk ' + value.toLocaleString()
                        }
                    }
                },
                plugins: {
                    // One series — the card heading already names it.
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'Tk ' + Number(ctx.parsed.y).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            })
                        }
                    }
                }
            }
        });

        // Bar Chart — new customers per month
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: customerChart.labels,
                datasets: [{
                    label: 'New customers',
                    data: customerChart.data,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: 'rgba(17, 24, 39, 0.06)' },
                        // Customer counts are whole numbers; the default float ticks
                        // read as nonsense at low volume.
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
@endsection
