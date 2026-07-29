<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded shadow-lg z-50 overflow-y-auto" style="max-height: 90vh;">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Add New Homepage Section</h3>
                <button class="modal-close-create z-50"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="createForm" action="{{ route('role.homepage-sections.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="create_type" class="block text-gray-700 text-sm font-bold mb-2">Section Type</label>
                        <select id="create_type" name="type" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="hero_slider">Hero Banner Slider</option>
                            <option value="category_strip">Featured Categories Strip</option>
                            <option value="product_row">Product Row (carousel)</option>
                            <option value="split_banner">Promotional Banner(s)</option>
                            <option value="brand_strip">Our Brands Strip</option>
                            <option value="combo_deals">Combo Deals</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="create_title" class="block text-gray-700 text-sm font-bold mb-2">Internal Title (admin label)</label>
                        <input type="text" id="create_title" name="title" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g. Top Selling Products">
                    </div>
                    <div class="mb-4">
                        <label for="create_heading" class="block text-gray-700 text-sm font-bold mb-2">Frontend Heading</label>
                        <input type="text" id="create_heading" name="heading" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Shown to customers, e.g. Top Selling Products">
                    </div>
                    <div class="mb-4">
                        <label for="create_subheading" class="block text-gray-700 text-sm font-bold mb-2">Subheading (optional)</label>
                        <input type="text" id="create_subheading" name="subheading" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>

                    <div id="create_product_row_fields" class="type-fields border-t pt-4 mt-2">
                        <div class="mb-4">
                            <label for="create_source" class="block text-gray-700 text-sm font-bold mb-2">Product Source</label>
                            <select id="create_source" name="source" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="trending">Trending Products</option>
                                <option value="popular">Popular Products</option>
                                <option value="recommended">Recommended Products</option>
                                <option value="category">Specific Category</option>
                                <option value="manual">Manually Picked Products</option>
                            </select>
                        </div>
                        <div id="create_category_field" class="type-fields mb-4">
                            <label for="create_category_id" class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                            <select id="create_category_id" name="category_id" class="form-select select2 w-full">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="create_manual_field" class="type-fields mb-4">
                            <label for="create_product_ids" class="block text-gray-700 text-sm font-bold mb-2">Products</label>
                            <select id="create_product_ids" name="product_ids[]" class="form-select select2 w-full" multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="create_limit" class="block text-gray-700 text-sm font-bold mb-2">Max Products to Show</label>
                            <input type="number" id="create_limit" name="limit" min="1" max="50" value="8" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="create_starts_at" class="block text-gray-700 text-sm font-bold mb-2">Show From (optional)</label>
                            <input type="datetime-local" id="create_starts_at" name="starts_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="create_ends_at" class="block text-gray-700 text-sm font-bold mb-2">Show Until (optional)</label>
                            <input type="datetime-local" id="create_ends_at" name="ends_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="create_is_active" class="form-checkbox h-5 w-5 text-blue-600" checked>
                            <span class="ml-2 text-gray-700">Active</span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md mr-2 modal-close-create">Cancel</button>
                <button id="createSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md">Submit</button>
            </div>
        </div>
    </div>
</div>
