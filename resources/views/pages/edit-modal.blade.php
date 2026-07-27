<div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto" style="min-width: 800px">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Edit </h3>
                <button class="modal-close-edit z-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('role.expenses.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'expense' => 1]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editItemId" name="id">                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         <div class="mb-2">
                            <label for="create_user_id" class="block text-gray-700 text-sm font-bold mb-2">Users</label>
                            <select id="create_user_id" name="user_id" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="">All</option>
                                @foreach ($categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>                                            
                                @endforeach
                            </select>
                            <p id="create_user_msg" class="text-red-500 text-xs mt-1 hidden error-message">Please select a user</p>
                        </div>
                        
                         
                        <div class="mb-2">
                            <label for="edit_title" class="block text-gray-700 text-sm font-bold mb-2">Title</label>
                            <input type="text" id="edit_title" name="title" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Title">
                            <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a Title</p>
                        </div>
                                                              
                                                                                                                                                                  
                                                               
                        
                        <div class="mb-2">
                            <label for="edit_status" class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                            <select id="edit_status" name="status" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                                <option value="1">Active</option>                                                                        
                                <option value="0">Inactive</option>                                                                        
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="edit_description" class="block text-gray-700 text-sm font-medium mb-2">Description</label>
                        <textarea id="edit_description" name="description" rows="4" placeholder="Enter Description"
                            class="w-full rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 p-3 text-sm text-gray-700 outline-none resize-none transition duration-150"></textarea>
                        <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter an Description</p>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200 mr-2 modal-close-edit">
                    Cancel
                </button>
                <button data-action="{{ route('role.expenses.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'expense' => 1]) }}" id="editSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-200">
                    Update State
                </button>
            </div>
        </div>
    </div>
</div>
