<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Sale;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\SubCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $req_subdatas = [];
        $query = Sale::select('sales.*')
            ->leftJoin('users', 'users.id', '=', 'sales.created_by')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
            ->orderBy('sales.id', 'asc');

        if ($request->has('created_by') && !empty($request->created_by)) {
            $query->where('sales.created_by', $request->created_by);
        }

        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $query->where('sales.customer_id', $request->customer_id);
        }

        if ($request->has('branch_id') && !empty($request->branch_id)) {
            $query->where('sales.branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sales.invoice_no', 'like', "%{$search}%");
            });
        }


        $datas = $query->paginate(20);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $customers = Customer::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();

        return view('sales.index', compact(
            'datas',
            'users',
            'branches',
            'customers'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $lastInvoice = Sale::latest('id')->value('invoice_no');

        if ($lastInvoice) {
            $lastNumber = (int) str_replace('EPAL-', '', $lastInvoice);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        $nextInvoice = 'EPAL-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $customers = Customer::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();
        $products = Product::orderBy('name')->where('is_active', 1)->get();
        $banks = Bank::orderBy('name')->where('status', 1)->get();

        return view('sales.create', compact(
            'users',
            'products',
            'branches',
            'customers',
            'banks',
            'nextInvoice'
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
            'sale_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);            
        }
        
        try {
            DB::transaction(function () use($request) {

                // $paymentStatus = 'due';
                // if (!empty($request->paid_amount)) {                    
                //     $paymentStatus = ($request->total_amount == $request->paid_amount) ? 'paid' : 'partial';
                // }
                
                $lastInvoice = Sale::latest('id')->value('invoice_no');

                if ($lastInvoice) {                    
                    $lastNumber = (int) str_replace('EPAL-', '', $lastInvoice);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }

                $invoiceNo = 'EPAL-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

                $data = Sale::create([
                    'customer_id' => $request->customer_id,
                    'branch_id' => $request->branch_id,
                    'bank_id' => $request->bank_id,
                    'invoice_no' => $invoiceNo,
                    'sale_date' => $request->sale_date ?? now(),
                    'total_amount' => $request->total_amount ?? 0,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'due_amount' => $request->due_amount ?? 0,
                    'commission_amount' => $request->commission_amount ?? 0,
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
                    
                        SaleItem::updateOrCreate(
                            ['product_id' => $product_id, 'sale_id' => $data->id],
                            ['quantity' => $quantity, 'unit_price' => $unit_price, 'subtotal' => $subtotal]
                        );
                    
                        $product = Product::with('stocks')->find($product_id);
                    
                        $product->update([
                            'stock_qty' => $product->stock_qty - $quantity
                        ]);
                    
                        $remainingQty = $quantity;                    
                        if ($request->branch_id) {
                            $branchStock = $product->stocks()->where('branch_id', $request->branch_id)->first();
                            if ($branchStock && $branchStock->available_qty > 0) {
                                $deduct = min($branchStock->available_qty, $remainingQty);
                                $branchStock->update(['available_qty' => $branchStock->available_qty - $deduct]);
                                $remainingQty -= $deduct;
                            }
                        }
                    
                        if ($remainingQty > 0) {
                            $otherStocks = $product->stocks()
                                ->where('branch_id', '!=', $request->branch_id)
                                ->where('available_qty', '>', 0)
                                ->orderBy('id')
                                ->get();
                    
                            foreach ($otherStocks as $stock) {
                                if ($remainingQty <= 0) break;
                    
                                $deduct = min($stock->available_qty, $remainingQty);
                                $stock->update(['available_qty' => $stock->available_qty - $deduct]);
                                $remainingQty -= $deduct;
                            }
                        }
                    
                        if ($remainingQty > 0) {
                            throw new \Exception("Insufficient stock for product SKU => {$product->sku}");
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
        $sale = Sale::with('items', 'items.product')->find($id);        
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $customers = Customer::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();
        $products = Product::orderBy('name')->where('is_active', 1)->get();
        $banks = Bank::orderBy('name')->where('status', 1)->get();

        return view('sales.edit', compact(
            'users',
            'products',
            'branches',
            'customers',
            'banks',
            'sale'
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
        $data = Sale::findOrFail($request->sale_id);
        if (!$data) {
            return request()->ajax()
                ? response()->json(['success' => false, 'message' => 'Data Info Not Found!'])
                : redirect()->back()->with('error', 'Data Info Not Found!');
        }

        $validated = $request->validate([
            'sale_date' => 'required',
        ]);

        try {
            DB::transaction(function () use ($data, $request) {

                // Update payment status
                // $paymentStatus = !empty($request->paid_amount)
                //     ? ($request->total_amount == $request->paid_amount ? 'paid' : 'partial')
                //     : 'due';

                $data->update([
                    'customer_id' => $request->customer_id,
                    'branch_id' => $request->branch_id,
                    'bank_id' => $request->bank_id,
                    'sale_date' => $request->sale_date,
                    'total_amount' => $request->total_amount ?? 0,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'due_amount' => $request->due_amount ?? 0,
                    'commission_amount' => $request->commission_amount ?? 0,
                    'payment_status' => $request->payment_status,
                    'payment_method' => $request->payment_method,
                    'note' => $request->note
                ]);

                $existingItems = $data->sale_items->keyBy('product_id');
                $incomingItemIds = $request->product_ids ?? [];

                // Handle removed items & restore stock
                $removedIds = array_diff($existingItems->keys()->toArray(), $incomingItemIds);
                foreach ($removedIds as $productId) {
                    $item = $existingItems[$productId];

                    // restore stock for product
                    $product = Product::with('stocks')->find($productId);
                    $product->increment('stock_qty', $item->quantity);

                    if ($data->branch_id) {
                        $branchStock = $product->stocks()->where('branch_id', $data->branch_id)->first();
                        if ($branchStock) {
                            $branchStock->increment('available_qty', $item->quantity);
                        }
                    }

                    // delete sale item
                    $item->delete();
                }

                // Update or create incoming items and adjust stock
                foreach ($incomingItemIds as $key => $productId) {
                    $quantity = (int) ($request->quantity[$key] ?? 0);
                    $unitPrice = (float) ($request->unit_price[$key] ?? 0);
                    $subtotal = (float) ($request->subtotal[$key] ?? 0);

                    $saleItem = SaleItem::updateOrCreate(
                        ['sale_id' => $data->id, 'product_id' => $productId],
                        ['quantity' => $quantity, 'unit_price' => $unitPrice, 'subtotal' => $subtotal]
                    );

                    $product = Product::with('stocks')->find($productId);

                    // Calculate old quantity for stock adjustment
                    $oldQuantity = $existingItems[$productId]->quantity ?? 0;
                    $qtyDiff = $quantity - $oldQuantity;

                    if ($qtyDiff != 0) {
                        // update main stock
                        $product->stock_qty -= $qtyDiff;
                        if ($product->stock_qty < 0) {
                            throw new \Exception("Insufficient stock for product SKU: {$product->sku}");
                        }
                        $product->save();

                        $remainingQty = $qtyDiff;

                        // adjust branch stock
                        if ($request->branch_id) {
                            $branchStock = $product->stocks()->where('branch_id', $request->branch_id)->first();
                            if ($branchStock) {
                                $deduct = min($branchStock->available_qty, $remainingQty);
                                $branchStock->available_qty -= $deduct;
                                $branchStock->save();
                                $remainingQty -= $deduct;
                            }
                            // adjust other branches if needed
                            if ($remainingQty > 0) {
                                $otherStocks = $product->stocks()
                                    ->where('branch_id', '!=', $request->branch_id)
                                    ->where('available_qty', '>', 0)
                                    ->orderBy('id')
                                    ->get();
    
                                foreach ($otherStocks as $stock) {
                                    if ($remainingQty <= 0) break;
                                    $deduct = min($stock->available_qty, $remainingQty);
                                    $stock->available_qty -= $deduct;
                                    $stock->save();
                                    $remainingQty -= $deduct;
                                }
                            }
                        }


                        if ($remainingQty <= 0) {
                            throw new \Exception("Insufficient stock for product SKU => {$product->sku}");
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
            $item = Sale::with('items.product.stocks')->find($request->item_id);

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Info Not Found!'
                ]);
            }

            // Loop through sale items to restore stock
            foreach ($item->items as $saleItem) {
                $product = $saleItem->product;

                // Restore total product stock
                $product->update([
                    'stock_qty' => $product->stock_qty + $saleItem->quantity
                ]);

                $remainingQty = $saleItem->quantity;

                // Try to restore stock to the original branch first (if branch_id is stored in SaleItem)
                if ($saleItem->branch_id) {
                    $branchStock = $product->stocks()->where('branch_id', $saleItem->branch_id)->first();
                    if ($branchStock) {
                        $restock = min($remainingQty, $saleItem->quantity); // can also check max capacity if needed
                        $branchStock->update([
                            'available_qty' => $branchStock->available_qty + $restock
                        ]);
                        $remainingQty -= $restock;
                    }
                }

                // If any remaining, restore sequentially to other branches
                if ($remainingQty > 0) {
                    $otherStocks = $product->stocks()
                        ->where('branch_id', '!=', $saleItem->branch_id)
                        ->orderBy('id')
                        ->get();

                    foreach ($otherStocks as $stock) {
                        if ($remainingQty <= 0) break;

                        $restock = $remainingQty;
                        $stock->update([
                            'available_qty' => $stock->available_qty + $restock
                        ]);
                        $remainingQty -= $restock;
                    }
                }
            }

            // Finally, delete the sale
            $item->delete();

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

    
    public function getSaleEditModal(Request $request)
    {
        try {
            $data = $request->item_id ? Sale::with('stocks')->find($request->item_id) : null;
            $customers = Customer::orderBy('name')->where('is_active', 1)->get();
            $branches = Branch::orderBy('name')->where('is_active', 1)->get();
            $users = User::orderBy('name')->where('is_super_admin', 0)->get();
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data info found!'
                ]);
            }
            $data['modal_view'] = view('sales.edit-modal', [
                'data' => $data,
                'users' => $users,
                'customers' => $customers,
                'branches' => $branches
            ])->render();

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'Data Found Successfully.'
        ]);
    }
}
