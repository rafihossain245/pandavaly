<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto" style="min-width: 800px">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Add New </h3>
                <button class="modal-close-create z-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form class="closest" id="createForm" action="{{ route('role.expenses.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="mb-2">
                            <label for="create_user_id" class="block text-gray-700 text-sm font-bold mb-2">Users</label>
                            <select id="create_user_id" name="user_id" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="">All</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>                                            
                                @endforeach
                            </select>
                            <p id="create_user_msg" class="text-red-500 text-xs mt-1 hidden error-message">Please select a user</p>
                        </div>
                        <div class="mb-2">
                            <label for="create_company_id" class="block text-gray-700 text-sm font-bold mb-2">Company</label>
                            <select id="create_company_id" name="company_id" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="">All</option>                                        
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>                                            
                                @endforeach
                            </select>
                            <p id="create_company_msg" class="text-red-500 text-xs mt-1 hidden error-message">Please choose a company</p>
                        </div>
                        <div class="mb-2">
                            <label for="create_bank_id" class="block text-gray-700 text-sm font-bold mb-2">Bank</label>
                            <select id="create_bank_id" name="bank_id" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="">All</option>                                        
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>                                            
                                @endforeach
                            </select>
                            <p id="create_bank_msg" class="text-red-500 text-xs mt-1 hidden error-message">Please choose a bank</p>
                        </div>   
                        <div class="mb-2">
                            <label for="create_expense_category_id" class="block text-gray-700 text-sm font-bold mb-2">Expense Category</label>
                            <select id="create_expense_category_id" name="expense_category_id" onchange="getExpSubCategory(this, '#create_expense_sub_category_id')" data-action="{{ route('role.get-expense-sub-category', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="">All</option>                                        
                                @foreach ($expense_categories as $expense_category)
                                    <option value="{{ $expense_category->id }}">{{ $expense_category->name }}</option>                                            
                                @endforeach
                            </select>
                            <p id="create_expense_category_msg" class="text-red-500 text-xs mt-1 hidden error-message">Please choose a Category</p>
                        </div>
                        <div class="mb-2">
                            <label for="create_expense_sub_category_id" class="block text-gray-700 text-sm font-bold mb-2">Expense SubCategory</label>
                            <select id="create_expense_sub_category_id" name="expense_sub_category_id" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="">All</option>    
                            </select>
                            <p id="create_expense_category_msg" class="text-red-500 text-xs mt-1 hidden error-message">Please choose a sub category</p>
                        </div>
                        <div class="mb-2">
                            <label for="create_expense_date" class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                            <input type="date" id="create_expense_date" name="expense_date" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Date">
                            <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a Date</p>
                        </div>    
                        <div class="mb-2">
                            <label for="create_title" class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                            <input type="text" id="create_title" name="title" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Title">
                            <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a Title</p>
                        </div>
                        <div class="mb-2">
                            <label for="create_amount" class="block text-gray-700 text-sm font-bold mb-2">Amount</label>
                            <input type="number" id="create_amount" name="amount" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Amount">
                            <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a Amount</p>
                        </div>                                        
                        <div class="mb-2">
                            <label for="create_reference" class="block text-gray-700 text-sm font-bold mb-2">Reference</label>
                            <input type="text" id="create_reference" name="reference" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Reference">
                            <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a Reference</p>
                        </div>                                                                                                                                           
                        <div class="mb-2">
                            <label for="create_attachment" class="block text-gray-700 text-sm font-medium mb-2">Attachment</label>
                            <input type="file" id="create_attachment" name="attachment" style="padding: 10px"
                                class="block w-full text-sm text-gray-700 border-2 border-gray-200 rounded-md cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition duration-150">
                            <p class="text-red-500 text-xs mt-1 hidden error-message">Please choose an Attachment</p>
                        </div>                                         
                        <div class="mb-2">
                            <label for="create_payment_mode" class="block text-gray-700 text-sm font-bold mb-2">Payment Mode</label>
                            <select id="create_payment_mode" name="payment_mode" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="cash" selected>Cash</option>
                                <option value="mobile">Mobile</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>                                                                     
                            </select>
                            <p id="create_payment_mode_msg" class="text-red-500 text-xs mt-1 hidden error-message">Please choose a payment mode</p>
                        </div>
                        <div class="mb-2">
                            <label for="create_status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                            <select id="create_status" name="status" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="1">Active</option>                                                                        
                                <option value="0">Inactive</option>                                                                        
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="create_description" class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                        <textarea id="create_description" name="description" rows="4" placeholder="Enter Description"
                            class="w-full rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 p-3 text-sm text-gray-700 outline-none resize-none transition duration-150"></textarea>
                        <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter an Description</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200 mr-2 modal-close-create">
                    Cancel
                </button>
                <button id="createSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-200">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>
