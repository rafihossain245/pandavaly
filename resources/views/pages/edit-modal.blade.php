{{-- Note: this used to be an unedited copy of the expenses modal and posted to
     role.expenses.update with expense => 1, so saving a page edited expense #1.
     It now posts to the pages endpoint with the real id filled in on open. --}}
<div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="cms-modal bg-white mx-auto rounded-lg shadow-lg z-50">
        <div class="cms-modal-head">
            <div>
                <h3>Edit Page</h3>
                <p class="cms-modal-url"></p>
            </div>
            <button class="modal-close-edit"><i class="fas fa-times"></i></button>
        </div>

        <div class="cms-modal-body">
            <form id="editForm" method="POST"
                  data-action-template="{{ route('role.pages.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'page' => '__ID__']) }}">
                @csrf
                @method('PUT')
                <input type="hidden" id="editItemId" name="id">

                <div class="cms-grid-2">
                    <div class="cms-field">
                        <label for="edit_title">Title <span class="cms-req">*</span></label>
                        <input type="text" id="edit_title" name="title" class="cms-input">
                        <p class="cms-help">The link text shown in the footer.</p>
                    </div>

                    <div class="cms-field">
                        <label for="edit_category_id">Footer column</label>
                        <select id="edit_category_id" name="category_id" class="cms-input">
                            <option value="">— Not shown in the footer —</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p class="cms-help">Moving a page to another column puts it at the end of that column.</p>
                    </div>
                </div>

                <div class="cms-field">
                    <label for="edit_content">Content</label>
                    <textarea id="edit_content" name="content" class="cms-editor"></textarea>
                </div>

                <details class="cms-advanced">
                    <summary>Advanced</summary>

                    <div class="cms-field">
                        <label for="edit_link_url">Link to an existing page instead</label>
                        <input type="text" id="edit_link_url" name="link_url" class="cms-input"
                               placeholder="/track-order">
                        <p class="cms-help">When set, the footer link points here and the content above is not shown.</p>
                    </div>

                    <div class="cms-field" style="margin-bottom:0;">
                        <label for="edit_slug">URL slug</label>
                        <div class="cms-slug-row">
                            <span>/page/</span>
                            <input type="text" id="edit_slug" name="slug" class="cms-input">
                        </div>
                        <p class="cms-help">Changing this breaks any link already shared to the old address.</p>
                    </div>
                </details>

                <label class="cms-check">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                    <span>Visible on the storefront</span>
                </label>
            </form>
        </div>

        <div class="cms-modal-foot">
            <a href="#" target="_blank" class="cms-btn cms-btn-light cms-view-link" style="margin-right:auto;">
                <i class="fas fa-arrow-up-right-from-square"></i> View page
            </a>
            <button type="button" class="cms-btn cms-btn-light modal-close-edit">Cancel</button>
            <button type="button" id="editSubmit" class="cms-btn cms-btn-primary">Save changes</button>
        </div>
    </div>
</div>
