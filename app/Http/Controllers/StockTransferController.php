<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::with(['product', 'fromWarehouse', 'toWarehouse', 'createdBy'])
            ->orderBy('id', 'desc');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->from_warehouse_id);
        }

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }

        if ($request->filled('search')) {
            $query->where('transfer_no', 'like', '%' . $request->search . '%');
        }

        $datas      = $query->paginate(20)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();
        $products   = Product::orderBy('name')->where('is_active', 1)->get();

        return view('stock-transfers.index', compact('datas', 'warehouses', 'products'));
    }

    public function create()
    {
        $nextTransNo = $this->nextTransferNo();
        $warehouses  = Warehouse::orderBy('name')->get();
        $products    = Product::orderBy('name')->where('is_active', 1)->get();

        return view('stock-transfers.create', compact('nextTransNo', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_warehouse_id' => 'required|exists:ware_houses,id',
            'to_warehouse_id'   => 'required|exists:ware_houses,id|different:from_warehouse_id',
            'product_id'        => 'required|exists:products,id',
            'qty'               => 'required|numeric|min:0.01',
            'note'              => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            DB::transaction(function () use ($request) {
                // Lock and validate source stock
                $sourceStock = Stock::where('product_id', $request->product_id)
                    ->where('warehouse_id', $request->from_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$sourceStock || $sourceStock->qty_on_hand < $request->qty) {
                    $available = $sourceStock->qty_on_hand ?? 0;
                    throw new \Exception("Insufficient stock in source warehouse. Available: {$available}");
                }

                // Deduct from source
                $sourceStock->decrement('qty_on_hand', $request->qty);

                // Add to destination
                $destStock = Stock::firstOrCreate(
                    ['product_id' => $request->product_id, 'warehouse_id' => $request->to_warehouse_id],
                    ['qty_on_hand' => 0, 'qty_reserved' => 0]
                );
                $destStock->increment('qty_on_hand', $request->qty);

                // Resolve SKU for movement log
                $sku = ProductSku::where('product_id', $request->product_id)
                    ->where('is_active', 1)
                    ->orderBy('id')
                    ->first();

                // Create transfer record
                $transfer = StockTransfer::create([
                    'transfer_no'       => $this->nextTransferNo(),
                    'product_id'        => $request->product_id,
                    'product_sku_id'    => $sku?->id,
                    'from_warehouse_id' => $request->from_warehouse_id,
                    'to_warehouse_id'   => $request->to_warehouse_id,
                    'qty'               => $request->qty,
                    'note'              => $request->note,
                    'status'            => 'completed',
                    'created_by'        => auth()->id(),
                ]);

                // Log two stock movements: out from source, in to destination
                if ($sku) {
                    StockMovement::create([
                        'product_sku_id'    => $sku->id,
                        'from_warehouse_id' => $request->from_warehouse_id,
                        'to_warehouse_id'   => $request->to_warehouse_id,
                        'qty'               => $request->qty,
                        'reason'            => 'transfer',
                        'ref_type'          => 'TRNF',
                        'ref_id'            => $transfer->transfer_no,
                    ]);
                }

                // product.stock_qty stays the same (stock moved, not gained/lost)
            });
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Stock transferred successfully.']);
    }

    public function edit($_role, $id)
    {
        $transfer   = StockTransfer::findOrFail($id);
        $warehouses = Warehouse::orderBy('name')->get();
        $products   = Product::orderBy('name')->where('is_active', 1)->get();

        return view('stock-transfers.edit', compact('transfer', 'warehouses', 'products'));
    }

    public function update(Request $request, $_id = null)
    {
        $request->validate([
            'product_id'        => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:ware_houses,id',
            'to_warehouse_id'   => 'required|exists:ware_houses,id|different:from_warehouse_id',
            'qty'               => 'required|numeric|min:0.01',
            'note'              => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $transfer = StockTransfer::findOrFail($request->transfer_id);

                // Reverse the old transfer
                Stock::where('product_id', $transfer->product_id)
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->increment('qty_on_hand', $transfer->qty);

                $destStock = Stock::where('product_id', $transfer->product_id)
                    ->where('warehouse_id', $transfer->to_warehouse_id)
                    ->first();

                if ($destStock) {
                    $destStock->qty_on_hand = max(0, $destStock->qty_on_hand - $transfer->qty);
                    $destStock->save();
                }

                // Apply new transfer
                $newSource = Stock::where('product_id', $request->product_id)
                    ->where('warehouse_id', $request->from_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if (!$newSource || $newSource->qty_on_hand < $request->qty) {
                    $available = $newSource->qty_on_hand ?? 0;
                    throw new \Exception("Insufficient stock in source warehouse. Available: {$available}");
                }

                $newSource->decrement('qty_on_hand', $request->qty);

                $newDest = Stock::firstOrCreate(
                    ['product_id' => $request->product_id, 'warehouse_id' => $request->to_warehouse_id],
                    ['qty_on_hand' => 0, 'qty_reserved' => 0]
                );
                $newDest->increment('qty_on_hand', $request->qty);

                $transfer->update([
                    'product_id'        => $request->product_id,
                    'from_warehouse_id' => $request->from_warehouse_id,
                    'to_warehouse_id'   => $request->to_warehouse_id,
                    'qty'               => $request->qty,
                    'note'              => $request->note,
                ]);
            });
        } catch (\Throwable $th) {
            return request()->ajax()
                ? response()->json(['success' => false, 'message' => $th->getMessage()])
                : redirect()->back()->with('error', $th->getMessage());
        }

        return request()->ajax()
            ? response()->json(['success' => true, 'message' => 'Transfer updated successfully.'])
            : redirect()->back()->with('success', 'Transfer updated successfully.');
    }

    public function destroy(Request $request, $_id = null)
    {
        try {
            DB::transaction(function () use ($request) {
                $transfer = StockTransfer::findOrFail($request->item_id);

                // Reverse: restore source stock
                Stock::where('product_id', $transfer->product_id)
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->increment('qty_on_hand', $transfer->qty);

                // Reverse: reduce destination stock
                $destStock = Stock::where('product_id', $transfer->product_id)
                    ->where('warehouse_id', $transfer->to_warehouse_id)
                    ->lockForUpdate()
                    ->first();

                if ($destStock) {
                    $destStock->qty_on_hand = max(0, $destStock->qty_on_hand - $transfer->qty);
                    $destStock->save();
                }

                $transfer->delete();
            });
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Transfer deleted and stock reversed.']);
    }

    private function nextTransferNo(): string
    {
        $last = StockTransfer::latest('id')->value('transfer_no');
        $next = $last ? ((int) str_replace('TRNF-', '', $last) + 1) : 1;

        return 'TRNF-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
