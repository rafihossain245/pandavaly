<div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded shadow-lg z-50 overflow-y-auto" style="max-height: 90vh;">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Edit Homepage Section</h3>
                <button class="modal-close-edit z-50"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST"
                    action="{{ route('role.homepage-sections.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'homepage_section' => 1]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editItemId" name="id">
                    <div class="mb-4">
                        <label for="edit_type" class="block text-gray-700 text-sm font-bold mb-2">Section Type</label>
                        <select id="edit_type" name="type" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="hero_slider">Hero Banner Slider</option>
                            <option value="category_strip">Featured Categories Strip</option>
                            <option value="product_row">Product Row (carousel)</option>
                            <option value="split_banner">Promotional Banner(s)</option>
                            <option value="brand_strip">Our Brands Strip</option>
                            <option value="combo_deals">Combo Deals</option>
                            <option value="feature_strip">Trust / Feature Strip</option>
                            <option value="testimonials">Customer Testimonials</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_title" class="block text-gray-700 text-sm font-bold mb-2">Internal Title (admin label)</label>
                        <input type="text" id="edit_title" name="title" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="edit_heading" class="block text-gray-700 text-sm font-bold mb-2">Frontend Heading</label>
                        <input type="text" id="edit_heading" name="heading" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="edit_subheading" class="block text-gray-700 text-sm font-bold mb-2">Subheading (optional)</label>
                        <input type="text" id="edit_subheading" name="subheading" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>

                    <div id="edit_product_row_fields" class="type-fields border-t pt-4 mt-2">
                        <div class="mb-4">
                            <label for="edit_source" class="block text-gray-700 text-sm font-bold mb-2">Product Source</label>
                            <select id="edit_source" name="source" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="trending">Trending Products</option>
                                <option value="popular">Popular Products</option>
                                <option value="recommended">Recommended Products</option>
                                <option value="category">Specific Category</option>
                                <option value="manual">Manually Picked Products</option>
                            </select>
                        </div>
                        <div id="edit_category_field" class="type-fields mb-4">
                            <label for="edit_category_id" class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                            <select id="edit_category_id" name="category_id" class="form-select select2 w-full">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="edit_manual_field" class="type-fields mb-4">
                            <label for="edit_product_ids" class="block text-gray-700 text-sm font-bold mb-2">Products</label>
                            <select id="edit_product_ids" name="product_ids[]" class="form-select select2 w-full" multiple>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="edit_layout" class="block text-gray-700 text-sm font-bold mb-2">Layout</label>
                            <select id="edit_layout" name="layout" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="carousel">Carousel (sliding row)</option>
                                <option value="grid">Grid (static gallery)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="edit_limit" class="block text-gray-700 text-sm font-bold mb-2">Max Products to Show</label>
                            <input type="number" id="edit_limit" name="limit" min="1" max="50" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>

                    {{-- Trust badges. Rows are built by JS (see index.blade.php)
                         so the same repeater serves create and edit. --}}
                    <div id="edit_feature_strip_fields" class="type-fields border-t pt-4 mt-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Trust Badges</label>
                        <p class="text-xs text-gray-500 mb-3">Leave every row blank to show the default badges.</p>
                        <div id="edit_feature_rows"></div>
                        <button type="button" class="add-feature-row text-sm text-blue-600" data-prefix="edit">
                            <i class="fas fa-plus"></i> Add badge
                        </button>
                    </div>

                    <div id="edit_testimonials_fields" class="type-fields border-t pt-4 mt-2">
                        <div class="mb-4">
                            <label for="edit_testimonial_source" class="block text-gray-700 text-sm font-bold mb-2">Testimonial Source</label>
                            <select id="edit_testimonial_source" name="source" class="testimonial-source form-select w-full px-3 py-2 border border-gray-300 rounded-md" data-prefix="edit">
                                <option value="manual">Curated (written below)</option>
                                <option value="reviews">Real approved product reviews</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="edit_testimonial_limit" class="block text-gray-700 text-sm font-bold mb-2">How many to show</label>
                            <input type="number" id="edit_testimonial_limit" name="limit" min="1" max="50" value="3"
                                class="testimonial-limit form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div id="edit_testimonial_manual_wrap">
                            <div id="edit_testimonial_rows"></div>
                            <button type="button" class="add-testimonial-row text-sm text-blue-600" data-prefix="edit">
                                <i class="fas fa-plus"></i> Add testimonial
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="edit_starts_at" class="block text-gray-700 text-sm font-bold mb-2">Show From (optional)</label>
                            <input type="datetime-local" id="edit_starts_at" name="starts_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="edit_ends_at" class="block text-gray-700 text-sm font-bold mb-2">Show Until (optional)</label>
                            <input type="datetime-local" id="edit_ends_at" name="ends_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="edit_is_active" class="form-checkbox h-5 w-5 text-blue-600" checked>
                            <span class="ml-2 text-gray-700">Active</span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md mr-2 modal-close-edit">Cancel</button>
                <button data-action="{{ route('role.homepage-sections.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'homepage_section' => 1]) }}"
                    id="editSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md">Update</button>
            </div>
        </div>
    </div>
</div>
