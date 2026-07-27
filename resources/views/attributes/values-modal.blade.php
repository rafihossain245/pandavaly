<div id="valuesModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded shadow-lg z-50 overflow-y-auto">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Manage Values - <span id="valuesAttributeName"></span></h3>
                <button class="modal-close-values z-50">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="valuesAttributeId">

                <div class="mb-3 flex gap-2">
                    <input type="text" id="newValueInput" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Red, Small, 12W">
                    <button id="addValueBtn" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-200 whitespace-nowrap">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>

                <ul id="valuesList" class="divide-y divide-gray-200 border rounded-md">
                    <!-- populated via JS -->
                </ul>
                <p id="valuesEmptyMsg" class="text-gray-400 text-sm mt-2 hidden">No values added yet.</p>
            </div>
            <div class="modal-footer flex justify-end pt-3">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200 modal-close-values">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
