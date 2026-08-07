<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-lg mx-auto rounded shadow-lg z-50 overflow-y-auto">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">New Attribute</h3>
                <button class="modal-close-create z-50"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form class="closest" id="createForm"
                      action="{{ route('role.attributes.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                      method="POST">
                    @csrf

                    <div class="attr-field">
                        <label for="create_name">Name <span class="attr-req">*</span></label>
                        <input type="text" id="create_name" name="name" class="attr-input"
                               placeholder="e.g. Size, Colour, Weight">
                        <p class="attr-help">What shoppers will see above the choices on the product page.</p>
                    </div>

                    <div class="attr-field">
                        <label>How should shoppers pick a value? <span class="attr-req">*</span></label>
                        <div class="attr-display-picker">
                            @php
                                $displayHints = [
                                    'pill' => ['fa-square', 'Best for sizes, weights, packs'],
                                    'swatch' => ['fa-palette', 'Best for colours'],
                                    'dropdown' => ['fa-caret-down', 'Best for long lists'],
                                ];
                            @endphp
                            @foreach (\App\Models\Attribute::DISPLAY_TYPES as $value => $label)
                                <label class="attr-display-option">
                                    <input type="radio" name="display_type" value="{{ $value }}" @checked($value === 'pill')>
                                    <span class="attr-display-card">
                                        <i class="fas {{ $displayHints[$value][0] }}"></i>
                                        <strong>{{ $label }}</strong>
                                        <small>{{ $displayHints[$value][1] }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="attr-field">
                        <label for="create_values">Values</label>
                        <textarea id="create_values" name="values" rows="3" class="attr-input"
                                  placeholder="Small, Medium, Large"></textarea>
                        <p class="attr-help">Separate with commas or new lines. You can add more later.</p>
                    </div>

                    <details class="attr-advanced">
                        <summary>Advanced</summary>
                        <div class="attr-field" style="margin-bottom:0;">
                            <label for="create_code">Code</label>
                            <input type="text" id="create_code" name="code" class="attr-input"
                                   placeholder="Left blank, generated from the name">
                            <p class="attr-help">Internal identifier. Letters, numbers, dashes and underscores only.</p>
                        </div>
                    </details>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition duration-200 mr-2 modal-close-create">
                    Cancel
                </button>
                <button id="createSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-200">
                    Create attribute
                </button>
            </div>
        </div>
    </div>
</div>
