<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded shadow-lg z-50 overflow-y-auto" style="max-height: 90vh;">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Add New Coupon</h3>
                <button class="modal-close-create z-50"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="createForm" action="{{ route('role.coupons.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="create_code" class="block text-gray-700 text-sm font-bold mb-2">Coupon Code <span class="text-red-500">*</span></label>
                        <input type="text" id="create_code" name="code" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g. EIDSALE100" style="text-transform: uppercase">
                        <p class="text-xs text-gray-500 mt-1">Buyers type this at checkout. Saved in uppercase; matching is case-insensitive.</p>
                    </div>
                    <div class="mb-4">
                        <label for="create_description" class="block text-gray-700 text-sm font-bold mb-2">Description (internal)</label>
                        <input type="text" id="create_description" name="description" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="e.g. Eid campaign, Tk 100 off">
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="create_type" class="block text-gray-700 text-sm font-bold mb-2">Discount Type</label>
                            <select id="create_type" name="type" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="fixed">Fixed amount (Tk)</option>
                                <option value="percent">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label for="create_value" class="block text-gray-700 text-sm font-bold mb-2">
                                Value <span class="text-gray-400" id="create_value_suffix">Tk</span> <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" id="create_value" name="value" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="create_min_order_amount" class="block text-gray-700 text-sm font-bold mb-2">Minimum Order (Tk)</label>
                            <input type="number" step="0.01" min="0" id="create_min_order_amount" name="min_order_amount" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Optional">
                        </div>
                        <div id="create_cap_field" class="cap-field">
                            <label for="create_max_discount" class="block text-gray-700 text-sm font-bold mb-2">Max Discount (Tk)</label>
                            <input type="number" step="0.01" min="0" id="create_max_discount" name="max_discount" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Cap for % coupons">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="create_starts_at" class="block text-gray-700 text-sm font-bold mb-2">Starts At</label>
                            <input type="datetime-local" id="create_starts_at" name="starts_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="create_ends_at" class="block text-gray-700 text-sm font-bold mb-2">Ends At</label>
                            <input type="datetime-local" id="create_ends_at" name="ends_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="create_usage_limit" class="block text-gray-700 text-sm font-bold mb-2">Usage Limit</label>
                        <input type="number" min="1" id="create_usage_limit" name="usage_limit" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Leave empty for unlimited">
                    </div>
                    <div class="mb-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="create_is_active" name="is_active" value="1" checked class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
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
