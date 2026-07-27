@extends('layout.app')
@section('meta-information')
    <title>Invoices</title>
@endsection
@section('main-content')

@php $role = Str::slug(Auth::user()->getRoleNames()->first()); @endphp

<div class="flex items-center justify-between mb-5">
    <h1 class="text-xl font-bold text-gray-900">Invoices</h1>
</div>

{{-- Status filter tabs --}}
<div class="flex flex-wrap gap-2 mb-4">
    @php
        $tabs = ['' => 'All', 'unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'void' => 'Void'];
    @endphp
    @foreach($tabs as $val => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $val, 'page' => 1]) }}"
       class="px-3 py-1.5 rounded-full text-sm font-semibold border transition
              {{ request('status', '') === $val
                 ? 'bg-blue-600 text-white border-blue-600'
                 : 'bg-white text-gray-600 border-gray-300 hover:border-blue-400' }}">
        {{ $label }}
        @if($val !== '' && isset($counts[$val]))
            <span class="ml-1 opacity-75">({{ $counts[$val] }})</span>
        @endif
    </a>
    @endforeach
</div>

{{-- Search --}}
<form method="GET" class="mb-4 flex gap-2">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <input type="text" name="q" value="{{ request('q') }}"
           placeholder="Invoice No, Buyer, Order No…"
           class="border border-gray-300 rounded px-3 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-blue-400">
    <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-semibold">Search</button>
    @if(request('q'))<a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => 1]) }}" class="px-4 py-2 rounded text-sm text-gray-500 border border-gray-300 hover:bg-gray-50">Clear</a>@endif
</form>

{{-- Table --}}
<div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">#</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Invoice No</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Buyer</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Order</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Due</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Total</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Balance</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Status</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">View</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($invoices as $inv)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400">{{ $invoices->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3 font-mono font-semibold text-gray-900">{{ $inv->invoice_no }}</td>
                <td class="px-4 py-3 text-gray-700">{{ $inv->buyer?->business_name ?? '—' }}</td>
                <td class="px-4 py-3">
                    @if($inv->salesOrder)
                        <a href="{{ route('role.orders.show', ['role' => $role, 'order' => $inv->salesOrder->id]) }}"
                           class="text-blue-600 hover:underline font-mono text-xs">
                            {{ $inv->salesOrder->order_no }}
                        </a>
                    @else —
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $inv->invoice_date?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-3 {{ $inv->status === 'unpaid' && $inv->due_date?->isPast() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                    {{ $inv->due_date?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-4 py-3 text-right font-semibold text-gray-900">Tk {{ number_format($inv->total, 2) }}</td>
                <td class="px-4 py-3 text-right font-semibold {{ $inv->balance > 0 ? 'text-red-600' : 'text-green-700' }}">
                    Tk {{ number_format($inv->balance, 2) }}
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold {{ $statusColors[$inv->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($inv->status) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <a href="{{ route('role.invoices.show', ['role' => $role, 'invoice' => $inv->id]) }}"
                       class="text-blue-600 hover:underline text-xs font-semibold">View</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-4 py-10 text-center text-gray-400">No invoices found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($invoices->hasPages())
<div class="mt-4">{{ $invoices->links() }}</div>
@endif

@endsection
