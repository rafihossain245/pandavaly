<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded shadow-lg z-50">
        <div class="modal-content flex flex-col py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-2 border-b-2 border-gray-200 w-full">
                <h3 class="text-xl font-semibold">Add New Supplier</h3>
                <button class="modal-close-create z-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body overflow-y-auto mt-2" style="max-height: calc(90vh - 120px); scrollbar-width: thin;">
                <form id="createForm" action="{{ route('role.suppliers.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                        <!-- Supplier Info -->
                        <div class="mb-2">
                            <label for="create_name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                            <input type="text" id="create_name" name="name" class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Name">
                        </div>
                        <div class="mb-2">
                            <label for="create_email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" id="create_email" name="email" class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Email">
                        </div>
                        <div class="mb-2">
                            <label for="create_phone" class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                            <input type="number" id="create_phone" name="phone" class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Phone">
                        </div>
                        <div class="mt-2 md:col-span-3">
                            <label for="create_address" class="block text-gray-700 text-sm font-medium mb-2">Address</label>
                            <textarea id="create_address" name="address" rows="2" class="w-full border rounded-md p-3 text-sm" placeholder="Enter Address"></textarea>
                        </div>
                        <div class="mb-2 md:col-span-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" id="create_is_active" class="form-checkbox h-5 w-5 text-blue-600" checked>
                                <span class="ml-2 text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>

                    <!-- Bank Accounts Section -->
                    <div class="mt-4 border-t pt-3">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-md font-semibold text-gray-700">Bank Accounts</h4>
                            <button type="button" id="addBankRow" class="bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">
                                + Add Row
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-300 rounded-md">
                                <thead class="bg-gray-100 text-sm text-gray-700">
                                    <tr>
                                        <th class="p-2 border">Bank Name</th>
                                        <th class="p-2 border">Account Name</th>
                                        <th class="p-2 border">Account Number</th>
                                        <th class="p-2 border">Branch</th>
                                        <th class="p-2 border">Routing Number</th>
                                        <th class="p-2 border text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bankTableBody">
                                    <tr>
                                        <td class="border p-2"><input type="text" name="bank_accounts[0][bank_name]" class="w-full border rounded-md px-2 py-1" placeholder="Bank Name"></td>
                                        <td class="border p-2"><input type="text" name="bank_accounts[0][account_name]" class="w-full border rounded-md px-2 py-1" placeholder="Account Name"></td>
                                        <td class="border p-2"><input type="text" name="bank_accounts[0][account_no]" class="w-full border rounded-md px-2 py-1" placeholder="Account Number"></td>
                                        <td class="border p-2"><input type="text" name="bank_accounts[0][branch]" class="w-full border rounded-md px-2 py-1" placeholder="Branch"></td>
                                        <td class="border p-2"><input type="text" name="bank_accounts[0][swift]" class="w-full border rounded-md px-2 py-1" placeholder="Routing No."></td>
                                        <td class="border p-2 text-center">
                                            <button type="button" class="removeRow bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">X</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="modal-close-create px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 mr-2">Cancel</button>
                <button id="createSubmit" type="button" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Submit</button>
            </div>
        </div>
    </div>
</div>
