<div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto" style="max-height: 90vh;">
        <div class="modal-content py-4 text-left px-6">
            <div class="modal-header flex justify-between items-center pb-3">
                <h3 class="text-xl font-semibold">Edit Banner</h3>
                <button class="modal-close-edit z-50"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" enctype="multipart/form-data"
                    action="{{ route('role.banners.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'banner' => 1]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editItemId" name="id">
                    <div class="mb-4">
                        <label for="edit_homepage_section_id" class="block text-gray-700 text-sm font-bold mb-2">Section</label>
                        <select id="edit_homepage_section_id" name="homepage_section_id" class="form-select select2 w-full">
                            <option value="">Select Section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_title" class="block text-gray-700 text-sm font-bold mb-2">Title (optional)</label>
                        <input type="text" id="edit_title" name="title" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="edit_subtitle" class="block text-gray-700 text-sm font-bold mb-2">Subtitle (optional)</label>
                        <input type="text" id="edit_subtitle" name="subtitle" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="edit_link" class="block text-gray-700 text-sm font-bold mb-2">Link (optional)</label>
                        <input type="text" id="edit_link" name="link" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label for="edit_image" class="block text-gray-700 text-sm font-medium mb-2">Image</label>
                        <input type="file" id="edit_image" name="image_path" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-2">
                        <label class="block text-gray-700 text-sm font-semibold mb-2">Current Image</label>
                        <img id="edit_preview" src="" alt="" class="rounded border w-full max-h-56 object-contain bg-gray-50" />
                    </div>
                    <div class="mb-4">
                        <label for="edit_sort_order" class="block text-gray-700 text-sm font-bold mb-2">Sort Order</label>
                        <input type="number" id="edit_sort_order" name="sort_order" class="form-input w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" id="edit_is_active" class="form-checkbox h-5 w-5 text-blue-600" checked>
                            <span class="ml-2 text-gray-700">Active</span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end pt-2">
                <button type="button" class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md mr-2 modal-close-edit">Cancel</button>
                <button data-action="{{ route('role.banners.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'banner' => 1]) }}"
                    id="editSubmit" type="button" class="btn btn-primary px-4 py-2 bg-blue-500 text-white rounded-md">Update</button>
            </div>
        </div>
    </div>
</div>
