<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['productSku.product', 'fromWarehouse', 'toWarehouse'])
            ->orderBy('id', 'desc');

        if ($request->filled('product_id')) {
            $query->whereHas('productSku', fn($q) => $q->where('product_id', $request->product_id));
        }

        if ($request->filled('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id)
                  ->orWhere('to_warehouse_id', $request->warehouse_id);
            });
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('search')) {
            $query->where('ref_id', 'like', '%' . $request->search . '%');
        }

        $datas      = $query->paginate(20)->withQueryString();
        $warehouses = Warehouse::orderBy('name')->get();
        $products   = Product::orderBy('name')->where('is_active', 1)->get();
        $reasons    = ['purchase_receipt', 'sale_dispatch', 'transfer', 'adjustment', 'return_in', 'return_out'];

        return view('stock-movements.index', compact('datas', 'warehouses', 'products', 'reasons'));
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $products   = Product::orderBy('name')->where('is_active', 1)->get();
        $reasons    = ['purchase_receipt', 'sale_dispatch', 'adjustment', 'return_in', 'return_out'];

        return view('stock-movements.create', compact('warehouses', 'products', 'reasons'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id'         => 'required|exists:products,id',
            'from_warehouse_id'  => 'nullable|exists:ware_houses,id',
            'to_warehouse_id'    => 'nullable|exists:ware_houses,id',
            'qty'                => 'required|numeric|min:0.01',
            'reason'             => 'required|in:purchase_receipt,sale_dispatch,adjustment,return_in,return_out',
            'ref_type'           => 'nullable|string|max:10',
            'ref_id'             => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            DB::transaction(function () use ($request) {
                // Resolve the first active SKU for this product
                $sku = ProductSku::where('product_id', $request->product_id)
                    ->where('is_active', 1)
                    ->orderBy('id')
                    ->firstOrFail();

                StockMovement::create([
                    'product_sku_id'    => $sku->id,
                    'from_warehouse_id' => $request->from_warehouse_id,
                    'to_warehouse_id'   => $request->to_warehouse_id,
                    'qty'               => $request->qty,
                    'reason'            => $request->reason,
                    'ref_type'          => $request->ref_type,
                    'ref_id'            => $request->ref_id,
                ]);

                // Update stock on_hand for the relevant warehouse
                $inReasons  = ['purchase_receipt', 'return_in'];
                $outReasons = ['sale_dispatch', 'return_out'];

                if (in_array($request->reason, $inReasons) && $request->to_warehouse_id) {
                    $this->adjustQtyOnHand($request->product_id, $request->to_warehouse_id, $request->qty);
                } elseif (in_array($request->reason, $outReasons) && $request->from_warehouse_id) {
                    $this->adjustQtyOnHand($request->product_id, $request->from_warehouse_id, -$request->qty);
                } elseif ($request->reason === 'adjustment') {
                    // adjustment can be positive (in) or negative (out) — positive qty = stock in
                    if ($request->to_warehouse_id) {
                        $this->adjustQtyOnHand($request->product_id, $request->to_warehouse_id, $request->qty);
                    } elseif ($request->from_warehouse_id) {
                        $this->adjustQtyOnHand($request->product_id, $request->from_warehouse_id, -$request->qty);
                    }
                }

                // Keep product.stock_qty in sync (sum across all warehouses)
                $this->syncProductStockQty($request->product_id);
            });
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Stock movement recorded.']);
    }

    public function edit(string $_role, int $id)
    {
        $movement   = StockMovement::with('productSku.product')->findOrFail($id);
        $warehouses = Warehouse::orderBy('name')->get();
        $products   = Product::orderBy('name')->where('is_active', 1)->get();
        $reasons    = ['purchase_receipt', 'sale_dispatch', 'adjustment', 'return_in', 'return_out'];

        return view('stock-movements.edit', compact('movement', 'warehouses', 'products', 'reasons'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id'         => 'required|exists:products,id',
            'from_warehouse_id'  => 'nullable|exists:ware_houses,id',
            'to_warehouse_id'    => 'nullable|exists:ware_houses,id',
            'qty'                => 'required|numeric|min:0.01',
            'reason'             => 'required|in:purchase_receipt,sale_dispatch,adjustment,return_in,return_out',
            'ref_id'             => 'nullable|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $movement = StockMovement::findOrFail($id);

                $inReasons  = ['purchase_receipt', 'return_in'];
                $outReasons = ['sale_dispatch', 'return_out'];

                // Reverse old effect
                if (in_array($movement->reason, $inReasons) && $movement->to_warehouse_id) {
                    $this->adjustQtyOnHand($movement->productSku->product_id, $movement->to_warehouse_id, -$movement->qty);
                } elseif (in_array($movement->reason, $outReasons) && $movement->from_warehouse_id) {
                    $this->adjustQtyOnHand($movement->productSku->product_id, $movement->from_warehouse_id, $movement->qty);
                }

                // Resolve new SKU
                $sku = ProductSku::where('product_id', $request->product_id)
                    ->where('is_active', 1)->orderBy('id')->firstOrFail();

                // Apply new effect
                if (in_array($request->reason, $inReasons) && $request->to_warehouse_id) {
                    $this->adjustQtyOnHand($request->product_id, $request->to_warehouse_id, $request->qty);
                } elseif (in_array($request->reason, $outReasons) && $request->from_warehouse_id) {
                    $this->adjustQtyOnHand($request->product_id, $request->from_warehouse_id, -$request->qty);
                } elseif ($request->reason === 'adjustment') {
                    if ($request->to_warehouse_id) {
                        $this->adjustQtyOnHand($request->product_id, $request->to_warehouse_id, $request->qty);
                    } elseif ($request->from_warehouse_id) {
                        $this->adjustQtyOnHand($request->product_id, $request->from_warehouse_id, -$request->qty);
                    }
                }

                $movement->update([
                    'product_sku_id'    => $sku->id,
                    'from_warehouse_id' => $request->from_warehouse_id,
                    'to_warehouse_id'   => $request->to_warehouse_id,
                    'qty'               => $request->qty,
                    'reason'            => $request->reason,
                    'ref_id'            => $request->ref_id,
                ]);

                $this->syncProductStockQty($request->product_id);
            });
        } catch (\Throwable $th) {
            return request()->ajax()
                ? response()->json(['success' => false, 'message' => $th->getMessage()])
                : redirect()->back()->with('error', $th->getMessage());
        }

        return request()->ajax()
            ? response()->json(['success' => true, 'message' => 'Movement updated successfully.'])
            : redirect()->back()->with('success', 'Movement updated successfully.');
    }

    public function destroy(Request $request, $_id = null)
    {
        DB::transaction(function () use ($request) {
            $movement = StockMovement::findOrFail($request->item_id);

            $inReasons  = ['purchase_receipt', 'return_in'];
            $outReasons = ['sale_dispatch', 'return_out'];

            // Reverse the effect
            if (in_array($movement->reason, $inReasons) && $movement->to_warehouse_id) {
                $this->adjustQtyOnHand($movement->productSku->product_id, $movement->to_warehouse_id, -$movement->qty);
            } elseif (in_array($movement->reason, $outReasons) && $movement->from_warehouse_id) {
                $this->adjustQtyOnHand($movement->productSku->product_id, $movement->from_warehouse_id, $movement->qty);
            }

            $productId = $movement->productSku->product_id;
            $movement->delete();

            $this->syncProductStockQty($productId);
        });

        return response()->json(['success' => true, 'message' => 'Movement deleted and stock reversed.']);
    }

    private function adjustQtyOnHand(int $productId, int $warehouseId, float $delta): void
    {
        $stock = Stock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['qty_on_hand' => 0, 'qty_reserved' => 0]
        );

        $stock->qty_on_hand = max(0, $stock->qty_on_hand + $delta);
        $stock->save();
    }

    private function syncProductStockQty(int $productId): void
    {
        $total = Stock::where('product_id', $productId)->sum('qty_on_hand');
        Product::where('id', $productId)->update(['stock_qty' => $total]);
    }
}
