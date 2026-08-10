@extends('layout.app')

@section('meta-information')
    <title>Delivery</title>
@endsection

@section('main-content')
    @php
        $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());

        $filters = [
            'all' => 'All',
            'failed' => 'Not accepted',
            'moving' => 'In transit',
            'delivered' => 'Delivered',
        ];

        // Courier states worth colouring differently; anything else stays neutral.
        $statusStyles = [
            'delivered' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'partial_delivered' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
            'in_review' => 'bg-blue-50 text-blue-700 ring-blue-200',
        ];
    @endphp

    <div class="mx-auto max-w-[1440px]">
        <div class="mb-5 overflow-hidden rounded-xl shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">
                    <i class="fas fa-truck-fast mr-2"></i> Delivery
                </h2>
                <span class="rounded-full px-3 py-1 text-xs font-medium
                    {{ $courier['live'] ? 'bg-emerald-400/20 text-emerald-100' : 'bg-amber-400/20 text-amber-100' }}">
                    Steadfast · {{ $courier['live'] ? 'Live' : 'Test mode (' . $courier['driver'] . ')' }}
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @unless ($courier['live'])
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                <strong>No real parcels are being created.</strong>
                The courier driver is <code class="rounded bg-amber-100 px-1">{{ $courier['driver'] }}</code>, which only
                writes the payload to the log. Set <code class="rounded bg-amber-100 px-1">STEADFAST_DRIVER=api</code>
                with real keys in <code class="rounded bg-amber-100 px-1">.env</code> to ship for real.
                @unless ($courier['configured'])
                    The API key and secret are also still empty.
                @endunless
            </div>
        @endunless

        {{-- Orders waiting to be handed over --}}
        @if ($waiting->isNotEmpty())
            <div class="mb-5 overflow-hidden rounded-xl border border-blue-200 bg-white">
                <div class="border-b border-blue-100 bg-blue-50 px-5 py-4">
                    <h3 class="text-base font-semibold text-blue-900">
                        {{ $waiting->count() }} order(s) ready for the courier
                    </h3>
                    <p class="mt-1 text-xs text-blue-800">
                        These are at status "{{ $courier['pushStatus'] }}" with no accepted consignment.
                        {{ $courier['autoPush']
                            ? 'Automatic push is on, so these should clear on their own shortly.'
                            : 'Automatic push is off — send each one from its order page.' }}
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3 font-semibold">Order</th>
                                <th class="px-5 py-3 font-semibold">Recipient</th>
                                <th class="px-5 py-3 font-semibold">Phone</th>
                                <th class="px-5 py-3 text-right font-semibold">Total</th>
                                <th class="px-5 py-3 text-right font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($waiting as $order)
                                <tr class="border-b border-gray-100 last:border-b-0">
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $order->order_no }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $order->shipping_name ?: '—' }}</td>
                                    <td class="px-5 py-3 text-gray-600">{{ $order->shipping_phone ?: '—' }}</td>
                                    <td class="px-5 py-3 text-right text-gray-900">৳{{ number_format($order->total, 2) }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('role.orders.show', ['role' => $roleSlug, 'order' => $order->id]) }}"
                                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-600 transition hover:bg-gray-50">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Filters --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                @foreach ($filters as $key => $label)
                    <a href="{{ route('role.delivery.index', array_filter(['role' => $roleSlug, 'filter' => $key, 'q' => $search ?: null])) }}"
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition
                            {{ $filter === $key ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                        <span class="ml-1 opacity-70">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" class="flex items-center gap-2">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="search" name="q" value="{{ $search }}" placeholder="Invoice, consignment or phone"
                    class="w-64 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                <button type="submit"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 transition hover:bg-gray-50">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        {{-- Consignments --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3 font-semibold">Order</th>
                            <th class="px-5 py-3 font-semibold">Consignment</th>
                            <th class="px-5 py-3 font-semibold">Phone</th>
                            <th class="px-5 py-3 text-right font-semibold">COD</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Sent</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($consignments as $consignment)
                            <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    @if ($consignment->salesOrder)
                                        <a href="{{ route('role.orders.show', ['role' => $roleSlug, 'order' => $consignment->sales_order_id]) }}"
                                            class="font-medium text-blue-600 hover:underline">
                                            {{ $consignment->salesOrder->order_no }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">Order deleted</span>
                                    @endif
                                    <div class="text-xs text-gray-400">{{ $consignment->invoice }}</div>
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    @if ($consignment->consignment_id)
                                        <div>{{ $consignment->consignment_id }}</div>
                                        @if ($consignment->tracking_code)
                                            <div class="text-xs text-gray-400">{{ $consignment->tracking_code }}</div>
                                        @endif
                                    @else
                                        <span class="text-xs text-red-600">Not accepted</span>
                                        @if ($consignment->last_error)
                                            <div class="mt-0.5 max-w-xs truncate text-xs text-gray-400"
                                                title="{{ $consignment->last_error }}">
                                                {{ $consignment->last_error }}
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $consignment->recipient_phone }}</td>
                                <td class="px-5 py-3 text-right text-gray-900">৳{{ number_format($consignment->cod_amount, 2) }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset
                                        {{ $statusStyles[$consignment->delivery_status] ?? 'bg-gray-50 text-gray-600 ring-gray-200' }}">
                                        {{ $consignment->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    {{ $consignment->pushed_at?->format('d M, H:i') ?? '—' }}
                                    @if ($consignment->attempts > 1)
                                        <div class="text-gray-400">{{ $consignment->attempts }} attempts</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if ($consignment->isAccepted())
                                        @unless ($consignment->isFinal())
                                            <form method="POST"
                                                action="{{ route('role.delivery.sync', ['role' => $roleSlug, 'consignment' => $consignment->id]) }}"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                                    title="Ask the courier for an update">
                                                    <i class="fas fa-rotate"></i>
                                                </button>
                                            </form>
                                        @endunless
                                    @else
                                        <form method="POST"
                                            action="{{ route('role.delivery.retry', ['role' => $roleSlug, 'consignment' => $consignment->id]) }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs text-gray-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                                                Retry
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <i class="fas fa-truck-fast mb-3 text-3xl text-gray-300"></i>
                                    <p class="text-gray-500">No parcels here yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($consignments->hasPages())
                <div class="border-t border-gray-200 p-3">
                    {{ $consignments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
