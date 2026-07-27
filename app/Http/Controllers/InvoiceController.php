<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public const STATUS_COLORS = [
        'unpaid'  => 'bg-red-100 text-red-800',
        'partial' => 'bg-yellow-100 text-yellow-800',
        'paid'    => 'bg-green-100 text-green-800',
        'void'    => 'bg-gray-100 text-gray-600',
    ];

    public function index(string $_role, Request $request)
    {
        $query = Invoice::with('buyer', 'salesOrder')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('invoice_no', 'like', "%{$q}%")
                    ->orWhereHas('buyer', fn($b) => $b->where('business_name', 'like', "%{$q}%"))
                    ->orWhereHas('salesOrder', fn($s) => $s->where('order_no', 'like', "%{$q}%"));
            });
        }

        $invoices     = $query->paginate(20)->withQueryString();
        $counts       = Invoice::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $statusColors = self::STATUS_COLORS;

        return view('invoices.index', compact('invoices', 'counts', 'statusColors'));
    }

    public function show(string $_role, Invoice $invoice)
    {
        $invoice->load(['items.productSku.product', 'buyer', 'salesOrder']);
        $statusColors = self::STATUS_COLORS;

        return view('invoices.show', compact('invoice', 'statusColors'));
    }
}
