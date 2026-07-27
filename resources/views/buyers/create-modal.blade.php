<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded shadow-lg z-50">
        <div class="modal-content flex flex-col py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-2 border-b-2 border-gray-200 w-full">
                <h3 class="text-xl font-semibold">Add New Buyer</h3>
                <button class="modal-close-create z-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body overflow-y-auto mt-2" style="max-height: calc(90vh - 120px); scrollbar-width: thin;">
                <form id="createForm"
                    action="{{ route('role.buyers.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                    method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                        <!-- Supplier Info -->
                        <div class="mb-2">
                            <label for="create_name" class="block text-gray-700 text-sm font-bold mb-2">Business
                                Name</label>
                            <input type="text" id="business_name" name="business_name"
                                class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Business Name">
                        </div>
                        <div class="mb-2">
                            <label for="create_category"
                                class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                            <input type="text" id="create_category" name="category"
                                class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Category">
                        </div>
                        <div class="mb-2">
                            <label for="create_email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                            <input type="email" id="create_email" name="email"
                                class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Email">
                        </div>
                        <div class="mb-2">
                            <label for="create_phone" class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                            <input type="number" id="create_phone" name="phone"
                                class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Phone">
                        </div>
                        <div class="mb-2">
                            <label for="create_tin" class="block text-gray-700 text-sm font-bold mb-2">Tin</label>
                            <input type="number" id="create_tin" name="tin"
                                class="form-input w-full border rounded-md px-3 py-2" placeholder="Enter Tin No">
                        </div>
                        <div class="mb-2">
                            <label for="create_trade_license_no"
                                class="block text-gray-700 text-sm font-bold mb-2">Trade License No</label>
                            <input type="text" id="create_trade_license_no" name="trade_license_no"
                                class="form-input w-full border rounded-md px-3 py-2"
                                placeholder="Enter Trade License No">

                        </div>
                        <div class="mb-2 md:col-span-3">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                                <select name="status" id="" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                    <option disabled selected>Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="blacklisted">Blacklisted</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    <!-- Bank Accounts Section -->
                    <!-- Contact Persons Section -->
                    <div class="mt-4 border-t pt-3">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-md font-semibold text-gray-700">Contact Persons</h4>
                            <button type="button" id="addContactRow"
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
                                <tbody id="contactTableBody">
                                    <tr>
                                        <td class="border p-2">
                                            <input type="text" name="contacts[0][name]"
                                                class="w-full border rounded-md px-2 py-1" placeholder="Contact Name">
                                        </td>
                                        <td class="border p-2">
                                            <input type="email" name="contacts[0][email]"
                                                class="w-full border rounded-md px-2 py-1" placeholder="Email">
                                        </td>
                                        <td class="border p-2">
                                            <input type="text" name="contacts[0][phone]"
                                                class="w-full border rounded-md px-2 py-1" placeholder="Phone">
                                        </td>
                                        <td class="border p-2">
                                            <input type="text" name="contacts[0][designation]"
                                                class="w-full border rounded-md px-2 py-1" placeholder="Designation">
                                        </td>
                                        <td class="border p-2 text-center">
                                            <!-- radio: only one primary -->
                                            <input type="radio" name="primary_contact_index" value="0"
                                                checked>
                                        </td>
                                        <td class="border p-2 text-center">
                                            <button type="button"
                                                class="removeContactRow bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">X</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer flex justify-end pt-2">
                <button type="button"
                    class="modal-close-create px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 mr-2">Cancel</button>
                <button id="createSubmit" type="button"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Submit</button>
            </div>
        </div>
    </div>
</div>
