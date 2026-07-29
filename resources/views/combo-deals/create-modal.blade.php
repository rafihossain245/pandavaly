<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded shadow-lg z-50 overflow-y-auto" style="max-height: 90vh;">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Add New Combo Deal</h3>
                <button class="modal-close-create z-50"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="createForm" enctype="multipart/form-data" action="{{ route('role.combo-deals.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="create_name" class="block text-gray-700 text-sm font-bold mb-2">Combo Name</label>
                        <input type="text" id="create_name" name="name" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g. Honey Combo">
                    </div>
                    <div class="mb-4">
                        <label for="create_description" class="block text-gray-700 text-sm font-bold mb-2">Description (optional)</label>
                        <textarea id="create_description" name="description" rows="2" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="create_image" class="block text-gray-700 text-sm font-medium mb-2">Image</label>
                        <input type="file" id="create_image" name="image" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="create_product_ids" class="block text-gray-700 text-sm font-bold mb-2">Products in this combo (pick at least 2)</label>
                        <select id="create_product_ids" name="product_ids[]" class="form-select select2 w-full" multiple>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="create_price" class="block text-gray-700 text-sm font-bold mb-2">Combo Price (৳)</label>
                        <input type="number" step="0.01" min="0" id="create_price" name="price" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
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
