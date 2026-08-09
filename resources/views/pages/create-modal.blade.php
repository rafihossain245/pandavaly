<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="cms-modal bg-white mx-auto rounded-lg shadow-lg z-50">
        <div class="cms-modal-head">
            <div>
                <h3>New Page</h3>
                <p>Appears as a link in the footer column you choose.</p>
            </div>
            <button class="modal-close-create"><i class="fas fa-times"></i></button>
        </div>

        <div class="cms-modal-body">
            <form id="createForm"
                  action="{{ route('role.pages.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                  method="POST">
                @csrf

                <div class="cms-grid-2">
                    <div class="cms-field">
                        <label for="create_title">Title <span class="cms-req">*</span></label>
                        <input type="text" id="create_title" name="title" class="cms-input"
                               placeholder="e.g. Refund Policy">
                        <p class="cms-help">The link text shown in the footer.</p>
                    </div>

                    <div class="cms-field">
                        <label for="create_category_id">Footer column</label>
                        <select id="create_category_id" name="category_id" class="cms-input">
                            <option value="">— Not shown in the footer —</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p class="cms-help">
                            Manage columns under
                            <a href="{{ route('role.page-categories.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                               target="_blank">Page Categories</a>.
                        </p>
                    </div>
                </div>

                <div class="cms-field">
                    <label for="create_content">Content</label>
                    <textarea id="create_content" name="content" class="cms-editor"></textarea>
                    <p class="cms-help">Leave empty for now if you want to write it later — the page will say it is being written.</p>
                </div>

                <details class="cms-advanced">
                    <summary>Advanced</summary>

                    <div class="cms-field">
                        <label for="create_link_url">Link to an existing page instead</label>
                        <input type="text" id="create_link_url" name="link_url" class="cms-input"
                               placeholder="/track-order">
                        <p class="cms-help">
                            Set this and the footer link points here instead of showing content.
                            Use it for real features (like Order Tracking) rather than writing a page.
                        </p>
                    </div>

                    <div class="cms-field" style="margin-bottom:0;">
                        <label for="create_slug">URL slug</label>
                        <div class="cms-slug-row">
                            <span>/page/</span>
                            <input type="text" id="create_slug" name="slug" class="cms-input"
                                   placeholder="generated from the title">
                        </div>
                        <p class="cms-help">Letters, numbers and dashes only.</p>
                    </div>
                </details>

                <label class="cms-check">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Visible on the storefront</span>
                </label>
            </form>
        </div>

        <div class="cms-modal-foot">
            <button type="button" class="cms-btn cms-btn-light modal-close-create">Cancel</button>
            <button type="button" id="createSubmit" class="cms-btn cms-btn-primary">Create page</button>
        </div>
    </div>
</div>
