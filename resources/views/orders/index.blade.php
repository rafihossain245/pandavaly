@extends('layout.app')
@section('meta-information')
    <title>Online Orders</title>
@endsection
@section('css')
<style>
    .states-table .states-table-container { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,.1); overflow: hidden; }
    .states-table-header { background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%); color: white; }
    .states-table .states-table-content .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 0.75rem 1rem; font-weight: 600; color: #495057; }
    .states-table .states-table-content .table tbody td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid #e9ecef; }
    .states-table .states-table-content .table tbody tr:hover { background-color: #f8f9fa; }
    .status-tab { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: all .2s; }
    .status-tab:hover { opacity: .8; }
    .status-tab.all { background: #111; color: #fff; }
    .status-tab.pending { background: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .status-tab.confirmed { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
    .status-tab.packed { background: #ede9fe; color: #5b21b6; border-color: #c4b5fd; }
    .status-tab.shipped { background: #e0e7ff; color: #3730a3; border-color: #a5b4fc; }
    .status-tab.delivered { background: #ccfbf1; color: #065f46; border-color: #6ee7b7; }
    .status-tab.completed { background: #dcfce7; color: #166534; border-color: #86efac; }
    .status-tab.cancelled { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }
    .status-tab.active { box-shadow: 0 0 0 2px #1e40af; }
    .badge-status { display: inline-block; padding: .3rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
    .payment-badge { display: inline-block; padding: .2rem .55rem; border-radius: 4px; font-size: .72rem; font-weight: 600; }
    .pm-cod { background: #dcfce7; color: #166534; }
    .pm-bank { background: #dbeafe; color: #1e40af; }
</style>
@endsection
@section('main-content')

<div class="states-table bg-white rounded-lg shadow-md overflow-hidden" style="margin-top:0">
    <div class="states-table-container">

        {{-- Header --}}
        <div class="states-table-header px-6 py-4 flex justify-between items-center">
            <h2 class="text-white text-xl font-semibold">
                <i class="fas fa-shopping-bag mr-2"></i> Online Orders
            </h2>
            <span class="text-blue-200 text-sm">B2B Ecommerce Orders</span>
        </div>

        {{-- Filters --}}
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap gap-2 items-center">
            <a href="{{ route('role.orders.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
               class="status-tab all {{ !request('status') ? 'active' : '' }}">
                All ({{ $counts->sum() }})
            </a>
            @foreach(['pending','confirmed','packed','shipped','delivered','completed','cancelled'] as $s)
            <a href="{{ route('role.orders.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'status' => $s]) }}"
               class="status-tab {{ $s }} {{ request('status') === $s ? 'active' : '' }}">
                {{ ucfirst($s) }} ({{ $counts[$s] ?? 0 }})
            </a>
            @endforeach

            {{-- Search --}}
            <form method="GET" action="{{ route('role.orders.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                  class="ml-auto flex gap-2">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Order no, company, phone…"
                       class="border border-gray-300 rounded px-3 py-1 text-sm outline-none focus:border-blue-400" style="min-width:220px">
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm">Search</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="states-table-content" style="padding:15px">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-2 rounded mb-3 text-sm">{{ session('success') }}</div>
            @endif

            <div class="table-responsive" style="overflow-x:auto">
                <table class="table w-full border-collapse" style="min-width:800px">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order No</th>
                            <th>Company</th>
                            <th>Contact / Phone</th>
                            <th>Payment</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $orders->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $order->order_no }}</strong></td>
                            <td>
                                <div class="font-semibold text-sm">{{ $order->company_name }}</div>
                                <div class="text-xs text-gray-500">{{ $order->buyer?->email }}</div>
                            </td>
                            <td>
                                <div class="text-sm">{{ $order->contact_person }}</div>
                                <div class="text-xs text-gray-500">{{ $order->shipping_phone }}</div>
                            </td>
                            <td>
                                @if($order->payment_method === 'bank_transfer')
                                    <span class="payment-badge pm-bank"><i class="fas fa-university me-1"></i>Bank Transfer</span>
                                @else
                                    <span class="payment-badge pm-cod"><i class="fas fa-money-bill-wave me-1"></i>COD</span>
                                @endif
                            </td>
                            <td><strong>Tk {{ number_format($order->total, 2) }}</strong></td>
                            <td>
                                <span class="badge-status {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="text-xs text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('role.orders.show', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'order' => $order->id]) }}"
                                   class="border border-blue-500 text-blue-600 hover:bg-blue-500 hover:text-white px-3 py-1 rounded text-sm transition">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-400">
                                <i class="fas fa-shopping-bag fa-2x mb-2" style="opacity:.4; display:block; margin-bottom:8px"></i>
                                No orders found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $orders->links() }}</div>
        </div>

    </div>
</div>
@endsection
