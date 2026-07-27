<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $req_subdatas = [];
        $query = Purchase::select('purchases.*')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->leftJoin('branches', 'branches.id', '=', 'purchases.branch_id')
            ->orderBy('purchases.id', 'asc');

        if ($request->has('created_by') && !empty($request->created_by)) {
            $query->where('purchases.created_by', $request->created_by);
        }

        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('purchases.supplier_id', $request->supplier_id);
        }

        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('purchases.branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchases.purchase_no', 'like', "%{$search}%");
            });
        }


        $datas = $query->paginate(20);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $suppliers = Supplier::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();

        return view('purchases.index', compact(
            'datas',
            'users',
            'branches',
            'suppliers'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $lastInvoice = Purchase::latest('id')->value('purchase_no');

        if ($lastInvoice) {
            $lastNumber = (int) str_replace('PUR-', '', $lastInvoice);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $nextPurNo = 'PUR-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $suppliers = Supplier::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();
        $products = Product::orderBy('name')->where('is_active', 1)->get();
        $banks = Bank::orderBy('name')->where('status', 1)->get();

        return view('purchases.create', compact(
            'users',
            'products',
            'branches',
            'suppliers',
            'banks',
            'nextPurNo'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {                
        $validator = Validator::make($request->all(), [
            'purchase_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);            
        }
        
        try {
            DB::transaction(function () use($request) {

                $lastInvoice = Purchase::latest('id')->value('purchase_no');

                if ($lastInvoice) {                    
                    $lastNumber = (int) str_replace('PUR-', '', $lastInvoice);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                $invoiceNo = 'PUR-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

                $data = Purchase::create([
                    'supplier_id' => $request->supplier_id,
                    'branch_id' => $request->branch_id,
                    'bank_id' => $request->bank_id,
                    'purchase_no' => $invoiceNo,
                    'purchase_date' => $request->purchase_date ?? now(),
                    'total_amount' => $request->total_amount ?? 0,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'due_amount' => $request->due_amount ?? 0,
                    'payment_status' => $request->payment_status,
                    'payment_method' => $request->payment_method,
                    'note' => $request->note,
                    'created_by' => auth()->id()
                ]);

                if ($request->product_ids) {
                    foreach ($request->product_ids as $key => $product_id) {
                        $quantity = (int) ($request->quantity[$key] ?? 0);
                        $unit_price = (float) ($request->unit_price[$key] ?? 0);
                        $subtotal = (float) ($request->subtotal[$key] ?? 0);

                        PurchaseItem::updateOrCreate(
                            ['product_id' => $product_id, 'purchase_id' => $data->id],
                            ['quantity' => $quantity, 'unit_price' => $unit_price, 'subtotal' => $subtotal]
                        );

                        $product = Product::with('stocks')->find($product_id);

                        $product->update([
                            'stock_qty' => $product->stock_qty + $quantity
                        ]);

                        if ($request->branch_id) {
                            $branchStock = $product->stocks()->where('branch_id', $request->branch_id)->first();

                            if ($branchStock) {
                                $branchStock->update([
                                    'available_qty' => $branchStock->available_qty + $quantity
                                ]);
                            } else {
                                $product->stocks()->create([
                                    'branch_id' => $request->branch_id,
                                    'available_qty' => $quantity
                                ]);
                            }
                        }
                    }
                }
    
            });            
        } catch (\Throwable $th) {            
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully.'
        ]);
    
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($role,$id)
    {
        $purchase = Purchase::with('items', 'items.product')->find($id);        
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $suppliers = Supplier::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();
        $products = Product::orderBy('name')->where('is_active', 1)->get();
        $banks = Bank::orderBy('name')->where('status', 1)->get();

        return view('purchases.edit', compact(
            'users',
            'products',
            'branches',
            'suppliers',
            'banks',
            'purchase'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id=null)
    {        
        $data = Purchase::findOrFail($request->purchase_id);
        if (!$data) {
            return request()->ajax()
                ? response()->json(['success' => false, 'message' => 'Data Info Not Found!'])
                : redirect()->back()->with('error', 'Data Info Not Found!');
        }

        $validated = $request->validate([
            'purchase_date' => 'required',
        ]);

        try {
            DB::transaction(function () use ($data, $request) {                
                $data->update([
                    'supplier_id' => $request->supplier_id,
                    'bank_id' => $request->bank_id,
                    'branch_id' => $request->branch_id,
                    'purchase_date' => $request->purchase_date,
                    'total_amount' => $request->total_amount ?? 0,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'due_amount' => $request->due_amount ?? 0,
                    'payment_status' => $request->payment_status,
                    'payment_method' => $request->payment_method,
                    'note' => $request->note
                ]);

                $existingItems = $data->purchase_items->keyBy('product_id');
                $incomingItemIds = $request->product_ids ?? [];

                // Handle removed items & restore stock
                $removedIds = array_diff($existingItems->keys()->toArray(), $incomingItemIds);                
                foreach ($removedIds as $productId) {
                    $item = $existingItems[$productId];

                    $product = Product::with('stocks')->find($productId);

                    // Decrease total stock since we’re removing a purchase
                    $product->decrement('stock_qty', $item->quantity);

                    // Decrease branch stock
                    if ($data->branch_id) {
                        $branchStock = $product->stocks()->where('branch_id', $data->branch_id)->first();
                        if ($branchStock) {
                            $branchStock->decrement('available_qty', $item->quantity);
                        }
                    }

                    $item->delete();
                }


                // Update or create incoming items and adjust stock                
                foreach ($incomingItemIds as $key => $productId) {
                    $quantity = (int) ($request->quantity[$key] ?? 0);
                    $unitPrice = (float) ($request->unit_price[$key] ?? 0);
                    $subtotal = (float) ($request->subtotal[$key] ?? 0);

                    $oldQuantity = $existingItems[$productId]->quantity ?? 0;
                    $qtyDiff = $quantity - $oldQuantity; // positive = added, negative = reduced

                    PurchaseItem::updateOrCreate(
                        ['purchase_id' => $data->id, 'product_id' => $productId],
                        ['quantity' => $quantity, 'unit_price' => $unitPrice, 'subtotal' => $subtotal]
                    );

                    // Adjust stock properly
                    if ($qtyDiff != 0) {
                        $product = Product::with('stocks')->find($productId);

                        // Increase or decrease main stock depending on diff
                        $newQty = $product->stock_qty + $qtyDiff;
                        if ($newQty < 0) {
                            throw new \Exception("Invalid stock adjustment for product ID: {$product->sku}");
                        }
                        $product->update(['stock_qty' => $newQty]);

                        // Adjust branch stock accordingly
                        if ($request->branch_id) {
                            $branchStock = $product->stocks()->where('branch_id', $request->branch_id)->first();

                            if ($branchStock) {
                                $newAvailable = $branchStock->available_qty + $qtyDiff;
                                if ($newAvailable < 0) {
                                    throw new \Exception("Invalid branch stock for product ID: {$product->sku}");
                                }
                                $branchStock->update(['available_qty' => $newAvailable]);
                            } else {
                                // Create branch stock if adding positive quantity
                                if ($qtyDiff > 0) {
                                    $product->stocks()->create([
                                        'branch_id' => $request->branch_id,
                                        'available_qty' => $qtyDiff
                                    ]);
                                }
                            }
                        }
                    }
                }
            });
        } catch (\Throwable $th) {
            return request()->ajax()
                ? response()->json(['success' => false, 'message' => $th->getMessage()])
                : redirect()->back()->with('error', $th->getMessage());
        }

        return request()->ajax()
            ? response()->json(['success' => true, 'message' => 'Data updated successfully.'])
            : redirect()->back()->with('success', 'Data updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $purchase = Purchase::with('items.product.stocks')->find($request->item_id);

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Info Not Found!'
                ]);
            }

            // Loop through purchase items to reverse stock
            foreach ($purchase->items as $purchaseItem) {
                $product = $purchaseItem->product;

                // Decrease total product stock
                $newQty = $product->stock_qty - $purchaseItem->quantity;
                if ($newQty < 0) {
                    throw new \Exception("Invalid stock after deleting purchase for product ID: {$product->sku}");
                }
                $product->update(['stock_qty' => $newQty]);

                // Decrease branch stock
                if ($purchaseItem->branch_id) {
                    $branchStock = $product->stocks()->where('branch_id', $purchaseItem->branch_id)->first();
                    if ($branchStock) {
                        $newAvailable = $branchStock->available_qty - $purchaseItem->quantity;
                        if ($newAvailable < 0) {
                            throw new \Exception("Invalid branch stock for product ID: {$product->id}");
                        }
                        $branchStock->update(['available_qty' => $newAvailable]);
                    }
                }
            }

            // Delete purchase items first, then main record
            $purchase->items()->delete();
            $purchase->delete();

            return response()->json([
                'success' => true,
                'message' => 'Purchase deleted successfully.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully.'
        ]);
    }

}
