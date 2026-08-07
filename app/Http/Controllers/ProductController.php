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
use App\Helpers\FileLimit;
use Illuminate\Support\Facades\Log;

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
        // The joins below exist for filtering and ordering only — the select is
        // products.*, so without this eager load the list issued a query per row
        // per relation (12 columns x 20 rows).
        $query = Product::select('products.*')
            ->with(['category', 'sub_category', 'brand', 'product_prices'])
            ->withCount('skus')
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

        // whereDate() here compared a boolean column as a date, so the Status
        // filter silently matched nothing whichever option was chosen.
        if ($request->filled('is_active')) {
            $query->where('products.is_active', (int) $request->is_active);
        }

        // The Stock dropdown has always been in the form but was never read.
        if ($request->filled('stock_qty')) {
            $request->stock_qty == '1'
                ? $query->where('products.stock_qty', '>', 0)
                : $query->where(fn ($q) => $q->whereNull('products.stock_qty')->orWhere('products.stock_qty', '<=', 0));
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
        $attributes = Attribute::with('values')->usableForVariants()->ordered()->get();
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
        $this->logUploadDiagnostics($request);

        $validator = Validator::make($request->all(), $this->productRules(), $this->productValidationMessages(), $this->productAttributeNames());

        if ($validator->fails()) {
            Log::warning('Product create rejected by validation', ['errors' => $validator->errors()->toArray()]);

            return $this->validationFailed($request, $validator);
        }

        try {
            DB::transaction(function () use ($request) {
                $thumbnail = null;
                if ($request->hasFile('thumbnail')) {
                    $thumbnail = $this->storeUploadedImage($request->file('thumbnail'), 'images/product');
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
                    // Checkout enforces and decrements this, so it has to be settable
                    // here; for a product with variants syncProductStockFromSkus()
                    // overwrites it with the SKU total further down.
                    'stock_qty' => max(0, (int) ($request->stock_qty ?? 0)),
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
                        // update() already stored this; leaving it out here meant a
                        // compare-at price typed on the create form was silently lost
                        // until the admin saved the product a second time.
                        'previous_price' => $request->previous_price,
                        'selling_price' => $request->selling_price,
                        'valid_from' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    if (!$product_price) {
                        throw new \Exception('Product price creation failed');
                    }
                }
                $specifications = (array) $request->input('specification_name', []);
                $specificationValues = (array) $request->input('specification_value', []);
                foreach ($specifications as $i => $specificationName) {
                    if (filled($specificationName)) {
                        DB::table('product_specifications')->insert([
                            'product_id' => $data->id,
                            'specification_name' => $specificationName,
                            'specification_value' => $specificationValues[$i] ?? '',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $simage) {
                        if ($simage) {
                            DB::table('productwise_images')->insert([
                                'product_id' => $data->id,
                                'image_path' => $this->storeUploadedImage($simage, 'images/multi-pro'),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }

                $this->syncProductVariants($data->id, $request);


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
            });
        } catch (\Throwable $th) {
            report($th);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create product: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Failed to create product: ' . $th->getMessage())
                ->withInput();
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Product created successfully.']);
        }

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    /**
     * Temporary instrumentation for the "image does not upload" report. Records what
     * actually reached PHP so the failure can be pinned down from the log.
     * Remove once the upload issue is confirmed fixed.
     */
    private function logUploadDiagnostics(Request $request): void
    {
        $describe = function ($file) {
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                return ['present' => false];
            }

            return [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'client_mime' => $file->getClientMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'is_valid' => $file->isValid(),
                'error_code' => $file->getError(),
                'error_message' => $file->isValid() ? null : $file->getErrorMessage(),
            ];
        };

        $productDir = public_path('images/product');
        $galleryDir = public_path('images/multi-pro');

        Log::info('Product create upload diagnostics', [
            'content_length' => $request->server('CONTENT_LENGTH'),
            'content_type' => $request->header('Content-Type'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'has_thumbnail' => $request->hasFile('thumbnail'),
            'thumbnail' => $describe($request->file('thumbnail')),
            'has_images' => $request->hasFile('images'),
            'images_count' => is_array($request->file('images')) ? count($request->file('images')) : 0,
            'images' => collect((array) $request->file('images'))->map($describe)->all(),
            'all_file_keys' => array_keys($request->allFiles()),
            'posted_field_keys' => array_keys($request->except(['_token'])),
            'php_files_keys' => array_keys($_FILES ?? []),
            'product_dir' => ['path' => $productDir, 'exists' => is_dir($productDir), 'writable' => is_writable($productDir)],
            'gallery_dir' => ['path' => $galleryDir, 'exists' => is_dir($galleryDir), 'writable' => is_writable($galleryDir)],
            'process_user' => function_exists('posix_getpwuid') && function_exists('posix_geteuid')
                ? (posix_getpwuid(posix_geteuid())['name'] ?? 'unknown')
                : 'unknown',
        ]);
    }

    /**
     * Validation rules shared by store() and update(). $ignoreId skips the row being edited
     * in the unique checks.
     */
    private function productRules(?int $ignoreId = null): array
    {
        $uniqueSlug = 'unique:products,slug' . ($ignoreId ? ',' . $ignoreId : '');

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|' . $uniqueSlug,
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'required|exists:units,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'previous_price' => 'nullable|numeric|min:0',
            'moq' => 'nullable|integer|min:1',
            'stock_qty' => 'nullable|integer|min:0',
            'content' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:' . FileLimit::uploadMaxKilobytes(),
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:' . FileLimit::uploadMaxKilobytes(),
            'specification_name' => 'nullable|array',
            'specification_name.*' => 'nullable|string|max:255',
            'specification_value' => 'nullable|array',
            'specification_value.*' => 'nullable|string|max:500',
            'variants_json' => 'nullable|json',
            'variant_images' => 'nullable|array',
            'variant_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:' . FileLimit::uploadMaxKilobytes(),
        ];
    }

    private function productValidationMessages(): array
    {
        return [
            'required' => 'Please provide the :attribute.',
            'exists' => 'The selected :attribute no longer exists. Pick another one.',
            'image' => 'The :attribute must be an image file (JPG, PNG, WEBP or GIF).',
            'mimes' => 'The :attribute must be a JPG, PNG, WEBP or GIF file.',
            'max.file' => 'The :attribute must be ' . FileLimit::humanUploadMax() . ' or smaller. Please compress the image and try again.',
            'images.max' => 'You can upload at most 10 gallery images at once.',
            'uploaded' => 'The :attribute could not be uploaded. It is most likely larger than the server upload limit — try an image under ' . FileLimit::humanUploadMax() . '.',
            'numeric' => 'The :attribute must be a number.',
            'min.numeric' => 'The :attribute cannot be negative.',
            'variants_json.json' => 'The variant data is corrupted. Please rebuild the variants and try again.',
            'variant_images.*.image' => 'Each variant photo must be an image file (JPG, PNG, WEBP or GIF).',
        ];
    }

    private function productAttributeNames(): array
    {
        return [
            'name' => 'product name',
            'category_id' => 'category',
            'sub_category_id' => 'sub category',
            'brand_id' => 'brand',
            'unit_id' => 'unit',
            'supplier_id' => 'supplier',
            'selling_price' => 'selling price',
            'purchase_price' => 'purchase price',
            'moq' => 'minimum order quantity',
            'thumbnail' => 'thumbnail image',
            'images' => 'gallery images',
            'images.*' => 'gallery image',
            'content' => 'description',
        ];
    }

    /**
     * Validation failures answer in the caller's language: JSON for the ajax modals,
     * redirect-back-with-input for the full-page forms.
     */
    private function validationFailed(Request $request, $validator)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        return redirect()->back()->withErrors($validator)->withInput();
    }

    /**
     * Moves an uploaded image under public/ and returns the stored relative path.
     * Throws with a message the admin can act on rather than failing silently.
     */
    private function storeUploadedImage(\Illuminate\Http\UploadedFile $file, string $folder): string
    {
        if (!$file->isValid()) {
            throw new \RuntimeException(
                'Image "' . $file->getClientOriginalName() . '" was not uploaded (' . $file->getErrorMessage() . ').'
            );
        }

        $folder = trim($folder, '/');
        $directory = public_path($folder);

        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException("Could not create the upload folder \"{$folder}\". Check the folder permissions on the server.");
        }

        if (!is_writable($directory)) {
            throw new \RuntimeException("The upload folder \"{$folder}\" is not writable. Set it to permission 755 on the server.");
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
        $filename = uniqid() . Str::random(6) . '.' . $extension;

        $file->move($directory, $filename);

        return $folder . '/' . $filename;
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
        $attributes = Attribute::with('values')->usableForVariants()->ordered()->get();
        $skus = ProductSku::with('productAttributes')->where('product_id', $id)->ordered()->get();
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
        $validator = Validator::make($request->all(), $this->productRules((int) $id), $this->productValidationMessages(), $this->productAttributeNames());

        if ($validator->fails()) {
            return $this->validationFailed($request, $validator);
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

                    $thumbnail = $this->storeUploadedImage($request->file('thumbnail'), 'images/product');
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
                    'stock_qty' => max(0, (int) ($request->stock_qty ?? 0)),
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
                        DB::table('productwise_images')->insert([
                            'product_id' => $data->id,
                            'image_path' => $this->storeUploadedImage($img, 'images/multi-pro'),
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
                $this->syncProductVariants($data->id, $request);
            });

            Cache::forget("product_details_{$oldSlug}");
            Cache::forget("product_details_" . Str::slug($request->name));

            return redirect()
                ->back()
                ->with('success', 'Product updated successfully.');
        } catch (\Throwable $th) {
            report($th);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Update failed: ' . $th->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Update failed: ' . $th->getMessage())
                ->withInput();
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

                // The SKU rows themselves go with the product via the foreign
                // key cascade; their uploaded photos would otherwise be orphaned.
                foreach (ProductSku::where('product_id', $id)->pluck('image') as $variantImage) {
                    $this->deleteImageFile($variantImage);
                }

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
            $attributes = Attribute::with('values')->usableForVariants()->ordered()->get();
            $skus = ProductSku::with('productAttributes')->where('product_id', $product->id)->ordered()->get();
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
     * Create/update product SKUs and their attribute assignments from the
     * variant grid submitted by the create/edit forms.
     *
     * Each variant carries a `key` (its sorted attribute-value ids) which is
     * also the array key of its optional image upload, so a row's photo can be
     * matched back to the row without threading indexes through the JSON.
     */
    private function syncProductVariants($productId, Request $request)
    {
        $variants = json_decode($request->input('variants_json') ?? '', true);
        if (!is_array($variants)) {
            $variants = [];
        }

        $uploads = (array) $request->file('variant_images', []);
        $keptSkuIds = [];

        foreach (array_values($variants) as $position => $variant) {
            $sku = null;
            if (!empty($variant['id'])) {
                $sku = ProductSku::where('product_id', $productId)->find($variant['id']);
            }

            $key = $variant['key'] ?? null;
            $image = $sku?->image;

            if ($key && isset($uploads[$key]) && $uploads[$key] instanceof \Illuminate\Http\UploadedFile) {
                $this->deleteImageFile($image);
                $image = $this->storeUploadedImage($uploads[$key], 'images/variant');
            } elseif (!empty($variant['remove_image'])) {
                $this->deleteImageFile($image);
                $image = null;
            }

            $skuData = [
                'product_id' => $productId,
                'sku' => $this->blankToNull($variant['sku'] ?? null),
                'barcode' => $this->blankToNull($variant['barcode'] ?? null),
                'price' => $this->blankToNull($variant['price'] ?? null),
                'compare_at_price' => $this->blankToNull($variant['compare_at_price'] ?? null),
                'cost' => $this->blankToNull($variant['cost'] ?? null),
                'stock_qty' => max(0, (int) ($variant['stock_qty'] ?? 0)),
                'weight' => $this->blankToNull($variant['weight'] ?? null),
                'image' => $image,
                'position' => $position + 1,
                'is_active' => !empty($variant['is_active']) ? 1 : 0,
            ];

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

        $this->discardRemovedVariants($productId, $keptSkuIds);
        $this->rollUpVariantStock($productId, count($keptSkuIds) > 0);
    }

    /**
     * Variants the admin took off the grid. Ones that were never ordered are
     * deleted outright; ones with order history are only deactivated so past
     * invoices can still resolve what was bought.
     */
    private function discardRemovedVariants($productId, array $keptSkuIds): void
    {
        $removed = ProductSku::where('product_id', $productId)
            ->whereNotIn('id', $keptSkuIds ?: [0])
            ->get();

        if ($removed->isEmpty()) {
            return;
        }

        $ordered = DB::table('sales_order_items')
            ->whereIn('product_sku_id', $removed->pluck('id'))
            ->distinct()
            ->pluck('product_sku_id')
            ->all();

        foreach ($removed as $sku) {
            if (in_array($sku->id, $ordered)) {
                $sku->update(['is_active' => 0]);
                continue;
            }

            $this->deleteImageFile($sku->image);
            ProductAttribute::where('product_sku_id', $sku->id)->delete();
            $sku->delete();
        }
    }

    /**
     * With variants the sellable quantity lives on the SKUs, so the product's
     * own stock_qty becomes their sum — that keeps product cards, the cart's
     * stock guard and the listing filters honest without special-casing them.
     */
    private function rollUpVariantStock($productId, bool $hasVariants): void
    {
        if (!$hasVariants) {
            return;
        }

        $total = ProductSku::where('product_id', $productId)
            ->where('is_active', 1)
            ->sum('stock_qty');

        Product::where('id', $productId)->update(['stock_qty' => $total]);
    }

    private function blankToNull($value)
    {
        return ($value === '' || $value === null) ? null : $value;
    }

    private function deleteImageFile(?string $path): void
    {
        if ($path && file_exists(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
