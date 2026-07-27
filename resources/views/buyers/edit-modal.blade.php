<div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded shadow-lg z-50">
        <div class="modal-content flex flex-col py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-2 border-b-2 border-gray-200 w-full">
                <h3 class="text-xl font-semibold">Edit Buyer</h3>
                <button class="modal-close-edit z-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body overflow-y-auto mt-2" style="max-height: calc(90vh - 120px); scrollbar-width: thin;">
                <form id="editForm" method="POST" action="{{ route('role.buyers.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'buyer' => 1]) }}">

                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                        <div class="mb-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Business Name</label>
                            <input type="text" id="edit_business_name" name="business_name"
                                   class="form-input w-full border rounded-md px-3 py-2"
                                   placeholder="Enter Business Name">
                        </div>
                        <div class="mb-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                            <input type="text" id="edit_category" name="category"
                                   class="form-input w-full border rounded-md px-3 py-2"
                                   placeholder="Enter Category">
                        </div>
                        <div class="mb-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" id="edit_email" name="email"
                                   class="form-input w-full border rounded-md px-3 py-2"
                                   placeholder="Enter Email">
                        </div>
                        <div class="mb-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                            <input type="text" id="edit_phone" name="phone"
                                   class="form-input w-full border rounded-md px-3 py-2"
                                   placeholder="Enter Phone">
                        </div>
                        <div class="mb-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tin</label>
                            <input type="text" id="edit_tin" name="tin"
                                   class="form-input w-full border rounded-md px-3 py-2"
                                   placeholder="Enter Tin No">
                        </div>
                        <div class="mb-2">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Trade License No</label>
                            <input type="text" id="edit_trade_license_no" name="trade_license_no"
                                   class="form-input w-full border rounded-md px-3 py-2"
                                   placeholder="Enter Trade License No">
                        </div>
                        <div class="mb-2 md:col-span-3">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                                <select name="status" id="edit_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                    <option disabled selected>Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="blacklisted">Blacklisted</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    {{-- Contact Persons Section --}}
                    <div class="mt-4 border-t pt-3">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-md font-semibold text-gray-700">Contact Persons</h4>
                            <button type="button" id="addEditContactRow"
                                    class="bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">
                                + Add Contact
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-300 rounded-md">
                                <thead class="bg-gray-100 text-sm text-gray-700">
                                    <tr>
                                        <th class="p-2 border">Name</th>
                                        <th class="p-2 border">Email</th>
                                        <th class="p-2 border">Phone</th>
                                        <th class="p-2 border">Designation</th>
                                        <th class="p-2 border text-center">Primary</th>
                                        <th class="p-2 border text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="editContactTableBody">
                                    {{-- Filled dynamically by JS --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer flex justify-end pt-2">
                <button type="button"
                    class="modal-close-edit px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 mr-2">Cancel</button>
                <button id="editSubmit" type="button"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                    data-action="">
                    Update
                </button>
            </div>
        </div>
    </div>
</div>
