@extends('layout.app')
@section('meta-information')
    <title>Invoice {{ $invoice->invoice_no }}</title>
@endsection
@section('css')
<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; }
        .print-wrap { box-shadow: none !important; border: none !important; }
    }
    .inv-label { width: 140px; flex-shrink: 0; color: #777; font-size: 13px; font-weight: 500; }
    .inv-value { color: #111; font-size: 13px; font-weight: 600; }
</style>
@endsection
@section('main-content')

@php $role = Str::slug(Auth::user()->getRoleNames()->first()); @endphp

<div class="no-print flex items-center justify-between mb-5">
    <a href="{{ route('role.invoices.index', ['role' => $role]) }}"
       class="text-blue-600 text-sm hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Invoices
    </a>
    <button onclick="window.print()"
            class="flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-gray-700">
        <i class="fas fa-print"></i> Print / Save PDF
    </button>
</div>

<div class="print-wrap bg-white rounded-xl shadow p-8 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex justify-between items-start mb-8">
        <div>
            <div class="text-2xl font-extrabold text-gray-900">{{ config('app.name') }}</div>
            @php $setting = \App\Models\Setting::first(); @endphp
            @if($setting?->address)
            <div class="text-gray-500 text-sm mt-1">{{ $setting->address }}</div>
            @endif
            @if($setting?->contact_phone)
            <div class="text-gray-500 text-sm">{{ $setting->contact_phone }}</div>
            @endif
        </div>
        <div class="text-right">
            <div class="text-3xl font-black text-blue-600 tracking-wide">INVOICE</div>
            <div class="mt-2 font-mono font-bold text-lg text-gray-900">{{ $invoice->invoice_no }}</div>
            <span class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-bold {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst($invoice->status) }}
            </span>
        </div>
    </div>

    {{-- Meta row --}}
    <div class="grid grid-cols-2 gap-6 mb-8">
        <div>
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bill To</div>
            <div class="font-bold text-gray-900">{{ $invoice->buyer?->business_name ?? '—' }}</div>
            @if($invoice->buyer?->phone)
            <div class="text-gray-600 text-sm">{{ $invoice->buyer->phone }}</div>
            @endif
            @if($invoice->buyer?->email)
            <div class="text-gray-600 text-sm">{{ $invoice->buyer->email }}</div>
            @endif
            @if($invoice->buyer?->address)
            <div class="text-gray-500 text-sm mt-1">{{ $invoice->buyer->address }}{{ $invoice->buyer->city ? ', '.$invoice->buyer->city : '' }}</div>
            @endif
        </div>
        <div class="text-right">
            <div class="flex justify-end gap-8">
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Invoice Date</div>
                    <div class="font-semibold text-gray-900">{{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Due Date</div>
                    <div class="font-semibold {{ $invoice->status === 'unpaid' && $invoice->due_date?->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                    </div>
                </div>
            </div>
            @if($invoice->salesOrder)
            <div class="mt-3 text-sm text-gray-500">
                Order: <span class="font-mono font-semibold text-gray-700">{{ $invoice->salesOrder->order_no }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Items table --}}
    <table class="w-full text-sm mb-6" style="border-collapse:collapse">
        <thead>
            <tr style="background:#f8f9fa; border-bottom:2px solid #e9ecef">
                <th class="text-left px-4 py-3 font-semibold text-gray-600">Product</th>
                <th class="text-center px-4 py-3 font-semibold text-gray-600">Qty</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Unit Price</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-600">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr style="border-bottom:1px solid #f0f0f0">
                <td class="px-4 py-3 text-gray-800">
                    {{ $item->productSku?->product?->name ?? '—' }}
                    @if($item->productSku?->variant_label)
                        <div class="text-muted small">{{ $item->productSku->variant_label }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-center text-gray-700">{{ $item->qty }}</td>
                <td class="px-4 py-3 text-right text-gray-700">Tk {{ number_format($item->price, 2) }}</td>
                <td class="px-4 py-3 text-right font-semibold text-gray-900">Tk {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="flex justify-end">
        <div class="w-64">
            <div class="flex justify-between py-1.5 text-sm text-gray-600">
                <span>Subtotal</span><span>Tk {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between py-1.5 text-sm text-gray-600">
                <span>Discount</span><span>Tk {{ number_format($invoice->discount, 2) }}</span>
            </div>
            <div class="flex justify-between py-1.5 text-sm text-gray-600">
                <span>Tax / VAT</span><span>Tk {{ number_format($invoice->tax, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 text-base font-bold text-gray-900 border-t-2 border-gray-900 mt-1 pt-2">
                <span>Grand Total</span><span>Tk {{ number_format($invoice->total, 2) }}</span>
            </div>
            @if($invoice->balance > 0)
            <div class="flex justify-between py-1.5 text-sm font-semibold text-red-600 border-t border-red-200 mt-1">
                <span>Balance Due</span><span>Tk {{ number_format($invoice->balance, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment info (for unpaid) --}}
    @if(in_array($invoice->status, ['unpaid', 'partial']))
    <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
        <div class="font-bold text-blue-800 mb-2"><i class="fas fa-university mr-1"></i> Payment Instructions</div>
        <div class="text-blue-700">Please transfer the balance due to our bank account:</div>
        <div class="mt-2 space-y-1 text-blue-800">
            <div><span class="font-semibold">Bank:</span> {{ \App\Http\Controllers\OrderController::BANK_NAME }}</div>
            <div><span class="font-semibold">Account:</span> {{ \App\Http\Controllers\OrderController::BANK_ACCOUNT }}</div>
            <div><span class="font-semibold">Account Name:</span> {{ \App\Http\Controllers\OrderController::BANK_HOLDER }}</div>
            <div><span class="font-semibold">Reference:</span> {{ $invoice->invoice_no }}</div>
        </div>
    </div>
    @endif

    {{-- Footer --}}
    <div class="mt-8 pt-4 border-t border-gray-200 text-center text-xs text-gray-400">
        Thank you for your business. For queries contact {{ $setting?->contact_phone ?? config('app.name') }}.
    </div>

</div>

@endsection
