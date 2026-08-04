<div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded shadow-lg z-50 overflow-y-auto" style="max-height: 90vh;">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Edit Coupon</h3>
                <button class="modal-close-edit z-50"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" id="editItemId" name="item_id">
                    <div class="mb-4">
                        <label for="edit_code" class="block text-gray-700 text-sm font-bold mb-2">Coupon Code <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_code" name="code" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" style="text-transform: uppercase">
                    </div>
                    <div class="mb-4">
                        <label for="edit_description" class="block text-gray-700 text-sm font-bold mb-2">Description (internal)</label>
                        <input type="text" id="edit_description" name="description" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="edit_type" class="block text-gray-700 text-sm font-bold mb-2">Discount Type</label>
                            <select id="edit_type" name="type" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="fixed">Fixed amount (Tk)</option>
                                <option value="percent">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_value" class="block text-gray-700 text-sm font-bold mb-2">
                                Value <span class="text-gray-400" id="edit_value_suffix">Tk</span> <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="0.01" id="edit_value" name="value" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="edit_min_order_amount" class="block text-gray-700 text-sm font-bold mb-2">Minimum Order (Tk)</label>
                            <input type="number" step="0.01" min="0" id="edit_min_order_amount" name="min_order_amount" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Optional">
                        </div>
                        <div id="edit_cap_field" class="cap-field">
                            <label for="edit_max_discount" class="block text-gray-700 text-sm font-bold mb-2">Max Discount (Tk)</label>
                            <input type="number" step="0.01" min="0" id="edit_max_discount" name="max_discount" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Cap for % coupons">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="edit_starts_at" class="block text-gray-700 text-sm font-bold mb-2">Starts At</label>
                            <input type="datetime-local" id="edit_starts_at" name="starts_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="edit_ends_at" class="block text-gray-700 text-sm font-bold mb-2">Ends At</label>
                            <input type="datetime-local" id="edit_ends_at" name="ends_at" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="edit_usage_limit" class="block text-gray-700 text-sm font-bold mb-2">Usage Limit</label>
                        <input type="number" min="1" id="edit_usage_limit" name="usage_limit" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Leave empty for unlimited">
                    </div>
                    <div class="mb-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="form-checkbox">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md mr-2 modal-close-edit">Cancel</button>
                <button id="editSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md">Update</button>
            </div>
        </div>
    </div>
</div>
