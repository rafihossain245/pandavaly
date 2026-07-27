<div id="editStateModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Edit State</h3>
                <button class="modal-close-edit z-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editStateForm">
                    <input type="hidden" id="editStateId">
                    <input type="hidden" id="updateFormAction">
                    <div class="mb-4">
                        <label for="editCountry" class="block text-gray-700 text-sm font-bold mb-2">Country</label>
                        <select id="editCountry" class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2" style="width: 100%">
                            <option value="">Select Country</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>                                            
                            @endforeach
                        </select>
                        <p class="text-red-500 text-xs mt-1 hidden error-message">Please select a country</p>
                    </div>
                    <div class="mb-4">
                        <label for="editStateName" class="block text-gray-700 text-sm font-bold mb-2">State Name</label>
                        <input type="text" id="editStateName" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter state name">
                        <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a state name</p>
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="editStatus" class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2 text-gray-700">Active</span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200 mr-2 modal-close-edit">
                    Cancel
                </button>
                <button id="editStateSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-200">
                    Update State
                </button>
            </div>
        </div>
    </div>
</div>
