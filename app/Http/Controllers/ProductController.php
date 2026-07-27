<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ExpenseCategory;
use App\Models\Stock;
use App\Models\SubCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductSku;
use App\Models\ProductAttribute;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $req_subdatas = [];
        $query = Product::select('products.*')
            ->leftJoin('users', 'users.id', '=', 'products.user_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('sub_categories', 'sub_categories.id', '=', 'products.sub_category_id')
            ->orderBy('categories.name', 'asc')
            ->orderBy('products.name', 'asc');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('products.user_id', $request->user_id);
        }

        if ($request->has('brand_id') && !empty($request->brand_id)) {
            $query->where('products.brand_id', $request->brand_id);
        }

        if ($request->has('unit_id') && !empty($request->unit_id)) {
            $query->where('products.unit_id', $request->unit_id);
        }

        if ($request->has('supplier_id') && !empty($request->supplier_id)) {
            $query->where('products.supplier_id', $request->supplier_id);
        }

        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('products.category_id', $request->category_id);
            $req_subdatas = SubCategory::where('category_id', $request->category_id)->get();
        }

        if ($request->has('sub_category_id') && !empty($request->sub_category_id)) {
            $query->where('products.sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->whereDate('products.is_active', $request->is_active);
        }

        $datas = $query->paginate(20);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $brands = Brand::orderBy('name')->where('is_active', 1)->get();
        $units = Unit::orderBy('name')->where('is_active', 1)->get();
        $categories = Category::orderBy('name')->where('is_active', 1)->get();

        return view('products.index', compact(
            'datas',
            'users',
            'req_subdatas',
            'brands',
            'units',
            'categories'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name')->where('is_active', 1)->get();

        $brands = Brand::orderBy('name')->where('is_active', 1)->get();
        $units = Unit::orderBy('name')->where('is_active', 1)->get();
        $suppliers = Supplier::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();
        $attributes = Attribute::with('values')->where('type', 'select')->whereHas('values')->orderBy('name')->get();
        return view('products.create', compact('categories', 'brands', 'branches', 'suppliers', 'units', 'attributes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'nullable|unique:products,slug',
            'category_id' => 'required',
            'brand_id' => 'required',
            'unit_id' => 'required',
            'supplier_id' => 'required',
            'selling_price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            DB::transaction(function () use ($request) {
                $simage = $request->file('thumbnail');
                if ($simage) {
                    $image_name = uniqid();
                    $ext = strtolower($simage->getClientOriginalExtension());
                    $image_full_name = $image_name . '.' . $ext;
                    $upload_path = 'images/product/';
                    $image_url = $upload_path . $image_full_name;
                    $success = $simage->move($upload_path, $image_full_name);
                    if ($success) {
                        $thumbnail = $image_url;
                    }
                }
                $data = Product::create([
                    'user_id' => auth()->id(),
                    'name' => $request->name,
                    'slug' => Str::slug($request->name),
                    'sku' => 'PRD-' . strtoupper(Str::random(8)),
                    'category_id' => $request->category_id,
                    'sub_category_id' => $request->sub_category_id,
                    'brand_id' => $request->brand_id,
                    'unit_id' => $request->unit_id,
                    'supplier_id' => $request->supplier_id,
                    'thumbnail' => $thumbnail ?? null,
                    'description' => $request->content,
                    'moq' => max(1, (int) ($request->moq ?? 1)),
                    'is_trending' => $request->is_trending ? 1 : 0,
                    'is_popular' => $request->is_popular ? 1 : 0,
                    'is_recommended' => $request->is_recommended ? 1 : 0,
                    'is_active' => $request->is_active ? 1 : 0
                ]);
                if (!$data) {
                    throw new \Exception('Product creation failed');
                } else {
                    $product_price = DB::table('product_prices')->insert([
                        'product_id' => $data->id,
                        'pricing_tier_id' => null,
                        'purchase_price' => $request->purchase_price ?? $request->selling_price,
                        'selling_price' => $request->selling_price,
                        'valid_from' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    if (!$product_price) {
                        throw new \Exception('Product price creation failed');
                    }
                }
                $specifications = $request->specification_name;
                for ($i = 0; $i < count($specifications); $i++) {
                    if (!empty($specifications[$i])) {
                        DB::table('product_specifications')->insert([
                            'product_id' => $data->id,
                            'specification_name' => $specifications[$i],
                            'specification_value' => $request->specification_value[$i] ?? '',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $simage) {
                        if ($simage) {
                            $image_name = uniqid();
                            $ext = strtolower($simage->getClientOriginalExtension());
                            $image_full_name = $image_name . '.' . $ext;
                            $upload_path = 'images/multi-pro/';
                            $image_url = $upload_path . $image_full_name;

                            $simage->move($upload_path, $image_full_name);

                            DB::table('productwise_images')->insert([
                                'product_id' => $data->id,
                                'image_path' => $image_url,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }

                $this->syncProductVariants($data->id, $request->input('variants_json'));


                //-------- update/create stock        			
                // if ($request->branch_ids) {
                //     foreach ($request->branch_ids as $key => $branch_id) {
                //         $available = (int) ($request->available_qty[$key] ?? 0);
                //         $reserved = (int) ($request->reserved_qty[$key] ?? 0);
                //         $damaged = (int) ($request->damaged_qty[$key] ?? 0);

                //         Stock::updateOrCreate([
                //             'branch_id' => $branch_id,
                //             'product_id' => $data->id
                //         ], [
                //             'available_qty' => $available,
                //             'reserved_qty' => $reserved,
                //             'damaged_qty' => $damaged
                //         ]);
                //     }
                // }

                //--------- then update product current quantity
                // $name = strtoupper($data->name);
                // $prefix = collect(explode(' ', $name))->map(fn($word) => Str::substr($word, 0, 1))->implode('');
                // $productTotal = Stock::where('product_id', $data->id)->sum(DB::raw('available_qty + reserved_qty + damaged_qty'));
                // $newSku = $prefix ?: 'PRD';

                // if (Product::where('sku', $newSku)->where('id', '!=', $data->id)->exists()) {
                //     $newSku .= $data->id;
                // }

                // $data->update([
                //     'sku' => $newSku,
                //     'stock_qty' => $productTotal
                // ]);
                return redirect()->back()->with('success', 'Product created successfully.');
            });
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['error' => 'Failed to create product: ' . $th->getMessage()]);
        }

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($request, $id)
    {
        $categories = Category::orderBy('name')->where('is_active', 1)->get();

        $brands = Brand::orderBy('name')->where('is_active', 1)->get();
        $units = Unit::orderBy('name')->where('is_active', 1)->get();
        $suppliers = Supplier::orderBy('name')->where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->where('is_active', 1)->get();
        $data = Product::with(['product_prices'])->findOrFail($id);
        $product_spec = DB::table('product_specifications')->where('product_id', $id)->get();
        $product_images = DB::table('productwise_images')->where('product_id', $id)->get();
        $attributes = Attribute::with('values')->where('type', 'select')->whereHas('values')->orderBy('name')->get();
        $skus = ProductSku::with('productAttributes')->where('product_id', $id)->get();
        return view('products.edit-modal', compact('brands', 'units', 'categories', 'data', 'suppliers', 'branches', 'product_spec', 'product_images', 'attributes', 'skus'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $role, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category_id' => 'required',
            'brand_id' => 'required',
            'unit_id' => 'required',
            'supplier_id' => 'required',
            'selling_price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors()->first());
        }

        try {
            $oldProduct = Product::findOrFail($id);
            $oldSlug = $oldProduct->slug;

            DB::transaction(function () use ($request, $id) {

                $data = Product::findOrFail($id);

                /**
                 * ---------------------------
                 * 1️⃣ UPDATE THUMBNAIL IMAGE
                 * ---------------------------
                 */
                if ($request->hasFile('thumbnail')) {

                    // Delete old thumbnail
                    if ($data->thumbnail && file_exists(public_path($data->thumbnail))) {
                        unlink(public_path($data->thumbnail));
                    }

                    $file = $request->file('thumbnail');
                    $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
                    $uploadPath = 'images/product/';
                    $file->move($uploadPath, $fileName);

                    $thumbnail = $uploadPath . $fileName;
                } else {
                    $thumbnail = $data->thumbnail;
                }

                /**
                 * ---------------------------
                 * 2️⃣ UPDATE PRODUCT DATA
                 * ---------------------------
                 */
                $data->update([
                    'name' => $request->name,
                    'slug' => Str::slug($request->name),
                    'category_id' => $request->category_id,
                    'sub_category_id' => $request->sub_category_id,
                    'brand_id' => $request->brand_id,
                    'unit_id' => $request->unit_id,
                    'supplier_id' => $request->supplier_id,
                    'thumbnail' => $thumbnail,
                    'description' => $request->content,
                    'moq' => max(1, (int) ($request->moq ?? 1)),
                    'is_trending' => $request->is_trending ? 1 : 0,
                    'is_popular' => $request->is_popular ? 1 : 0,
                    'is_recommended' => $request->is_recommended ? 1 : 0,
                    'is_active' => $request->is_active ? 1 : 0,
                ]);

                /**
                 * -------------------------------------
                 * 3️⃣ UPDATE PRODUCT PRICE (SINGLE ROW)
                 * -------------------------------------
                 */
                DB::table('product_prices')
                    ->where('product_id', $data->id)
                    ->update([
                        'purchase_price' => $request->purchase_price ?? $request->selling_price,
                        'previous_price' => $request->previous_price,
                        'selling_price'   => $request->selling_price,
                        'updated_at'      => now(),
                    ]);

                /**
                 * -------------------------------------
                 * 4️⃣ UPDATE SPECIFICATIONS
                 * -------------------------------------
                 */
                DB::table('product_specifications')
                    ->where('product_id', $data->id)
                    ->delete();

                if (!empty($request->specification_name)) {
                    foreach ($request->specification_name as $i => $specName) {
                        if ($specName) {
                            DB::table('product_specifications')->insert([
                                'product_id'          => $data->id,
                                'specification_name'  => $specName,
                                'specification_value' => $request->specification_value[$i] ?? '',
                                'created_at'          => now(),
                                'updated_at'          => now(),
                            ]);
                        }
                    }
                }

                /**
                 * -------------------------------------
                 * 5️⃣ ADD NEW MULTIPLE IMAGES
                 * -------------------------------------
                 */
                if ($request->hasFile('images')) {

                    foreach ($request->file('images') as $img) {

                        $imageName = uniqid() . '.' . $img->getClientOriginalExtension();
                        $uploadPath = 'images/multi-pro/';
                        $img->move($uploadPath, $imageName);
                        DB::table('productwise_images')->insert([
                            'product_id' => $data->id,
                            'image_path' => $uploadPath . $imageName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                /**
                 * -------------------------------------
                 * 6️⃣ SYNC ATTRIBUTE VARIANTS
                 * -------------------------------------
                 */
                $this->syncProductVariants($data->id, $request->input('variants_json'));
            });

            Cache::forget("product_details_{$oldSlug}");
            Cache::forget("product_details_" . Str::slug($request->name));

            return redirect()
                ->back()
                ->with('success', 'Product updated successfully.');
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'Update failed: ' . $th->getMessage()]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $id = $request->item_id;
            DB::transaction(function () use ($id) {

                // Delete all child records
                DB::table('product_specifications')->where('product_id', $id)->delete();
                DB::table('product_prices')->where('product_id', $id)->delete();
                DB::table('productwise_images')->where('product_id', $id)->delete();
                DB::table('stocks')->where('product_id', $id)->delete(); // if exists

                // Delete the product
                Product::where('id', $id)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getProductEditModal(Request $request)
    {
        try {
            $product = $request->item_id ? Product::with('stocks')->find($request->item_id) : null;
            $users = User::orderBy('name')->where('is_super_admin', 0)->get();
            $categories = Category::orderBy('name')->where('is_active', 1)->get();
            $brands = Brand::orderBy('name')->where('is_active', 1)->get();
            $units = Unit::orderBy('name')->where('is_active', 1)->get();
            $suppliers = Supplier::orderBy('name')->where('is_active', 1)->get();
            $branches = Branch::orderBy('name')->where('is_active', 1)->get();
            $sub_categories = $product && $product->category_id ? SubCategory::where('category_id', $product->category_id)->get() : [];
            if (empty($product)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No product info found!'
                ]);
            }
            $product_spec = DB::table('product_specifications')->where('product_id', $product->id)->get();
            $product_images = DB::table('productwise_images')->where('product_id', $product->id)->get();
            $attributes = Attribute::with('values')->where('type', 'select')->whereHas('values')->orderBy('name')->get();
            $skus = ProductSku::with('productAttributes')->where('product_id', $product->id)->get();
            $data['modal_view'] = view('products.edit-modal', [
                'data' => $product,
                'product' => $product,
                'users' => $users,
                'categories' => $categories,
                'brands' => $brands,
                'units' => $units,
                'suppliers' => $suppliers,
                'branches' => $branches,
                'sub_categories' => $sub_categories,
                'product_spec' => $product_spec,
                'product_images' => $product_images,
                'attributes' => $attributes,
                'skus' => $skus,
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
    public function getSubCategory(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id'
        ]);

        $subCategories = SubCategory::where('category_id', $request->category_id)
            ->select('id', 'name')
            ->get();

        return response()->json($subCategories);
    }
    public function deleteImage($request, $id)
    {
        $image = DB::table('productwise_images')->where('id', $id)->first();

        if (!$image) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        // Delete file
        $path = asset($image->image_path);
        if (file_exists($path)) {
            unlink($path);
        }

        // Delete record
        DB::table('productwise_images')->where('id', $id)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Create/update product SKUs and their attribute assignments based on
     * the variant matrix submitted from the create/edit forms.
     *
     * @param  int  $productId
     * @param  string|null  $variantsJson
     * @return void
     */
    private function syncProductVariants($productId, $variantsJson)
    {
        $variants = json_decode($variantsJson ?? '', true);
        if (!is_array($variants)) {
            $variants = [];
        }

        $keptSkuIds = [];

        foreach ($variants as $variant) {
            $skuData = [
                'product_id' => $productId,
                'barcode' => $variant['barcode'] ?? null,
                'mrp' => $variant['mrp'] !== '' ? ($variant['mrp'] ?? null) : null,
                'cost' => $variant['cost'] !== '' ? ($variant['cost'] ?? null) : null,
                'weight' => $variant['weight'] !== '' ? ($variant['weight'] ?? null) : null,
                'is_active' => !empty($variant['is_active']) ? 1 : 0,
            ];

            $sku = null;
            if (!empty($variant['id'])) {
                $sku = ProductSku::where('product_id', $productId)->find($variant['id']);
            }

            if ($sku) {
                $sku->update($skuData);
            } else {
                $sku = ProductSku::create($skuData);
            }

            $keptSkuIds[] = $sku->id;

            ProductAttribute::where('product_sku_id', $sku->id)->delete();

            foreach (($variant['attribute_value_ids'] ?? []) as $valueId) {
                $attributeValue = AttributeValue::find($valueId);
                if (!$attributeValue) {
                    continue;
                }

                ProductAttribute::create([
                    'product_sku_id' => $sku->id,
                    'attribute_id' => $attributeValue->attribute_id,
                    'attribute_value_id' => $attributeValue->id,
                ]);
            }
        }

        ProductSku::where('product_id', $productId)
            ->whereNotIn('id', $keptSkuIds ?: [0])
            ->update(['is_active' => 0]);
    }
}
