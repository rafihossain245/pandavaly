<div id="valuesModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-xl mx-auto rounded shadow-lg z-50">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <div>
                    <h3 class="text-xl font-semibold">Values &mdash; <span id="valuesAttributeName"></span></h3>
                    <p class="attr-help" style="margin-top:2px;">Drag to reorder. This is the order shoppers see.</p>
                </div>
                <button class="modal-close-values z-50"><i class="fas fa-times"></i></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="valuesAttributeId">
                <input type="hidden" id="valuesDisplayType">

                <div class="attr-add-row">
                    <input type="color" id="newValueColor" class="attr-color" value="#3b82f6" title="Swatch colour">
                    <input type="text" id="newValueInput" class="attr-input"
                           placeholder="Red, Blue, Green — or one value at a time">
                    <button id="addValueBtn" type="button" class="attr-btn attr-btn-primary">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
                <p class="attr-help" id="valuesAddHint">Separate several values with commas to add them all at once.</p>

                <ul id="valuesList" class="attr-value-list"></ul>

                <p id="valuesEmptyMsg" class="attr-empty-note hidden">
                    <i class="fas fa-inbox"></i> No values yet. Add the first one above.
                </p>
            </div>

            <div class="modal-footer flex justify-end pt-3">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200 modal-close-values">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
