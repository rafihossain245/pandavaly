@extends('layout.app')
@section('meta-information')
    <title>Pages</title>
@endsection

@section('css')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<style>
    .modal { transition: opacity .25s ease; }
    .modal-backdrop { background-color: rgba(0,0,0,.5); }
    .hidden { display: none; }

    .cms-page { padding-bottom: 30px; }

    .cms-header { display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; margin-bottom: 18px; }
    .cms-header h1 { font-size: 1.35rem; font-weight: 600; margin: 0; color: #111827; }
    .cms-header p { margin: 4px 0 0; font-size: .85rem; color: #6b7280; }
    .cms-header-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .cms-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 18px; }
    .cms-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
    .cms-stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .cms-stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; color: #111827; }
    .cms-stat-label { font-size: .78rem; color: #6b7280; margin-top: 4px; }
    .cms-i-blue { background: #eff6ff; color: #2563eb; }
    .cms-i-green { background: #ecfdf5; color: #059669; }
    .cms-i-violet { background: #f5f3ff; color: #7c3aed; }
    .cms-i-amber { background: #fffbeb; color: #d97706; }

    .cms-filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 16px; margin-bottom: 18px; }
    .cms-filters .cms-input { width: auto; min-width: 190px; }

    /* ---- Column groups ---- */
    .cms-column { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 16px; overflow: hidden; }
    .cms-column-head { display: flex; align-items: center; gap: 10px; padding: 13px 18px; border-bottom: 1px solid #f1f2f4; background: #fafafa; }
    .cms-column-head h2 { font-size: .98rem; font-weight: 600; margin: 0; color: #111827; }
    .cms-column-count { font-size: .72rem; background: #eff6ff; color: #1d4ed8; border-radius: 999px; padding: 2px 9px; font-weight: 600; }
    .cms-column-hidden { font-size: .72rem; background: #fffbeb; color: #b45309; border-radius: 999px; padding: 2px 9px; font-weight: 600; }
    .cms-column-hint { margin-left: auto; font-size: .76rem; color: #9ca3af; }

    .cms-rows { list-style: none; margin: 0; padding: 0; }
    .cms-row { display: flex; align-items: center; gap: 12px; padding: 11px 18px; border-bottom: 1px solid #f6f7f8; }
    .cms-row:last-child { border-bottom: none; }
    .cms-row.sortable-ghost { opacity: .4; }
    .cms-handle { cursor: grab; color: #d1d5db; }
    .cms-handle:active { cursor: grabbing; }
    .cms-row-main { flex: 1; min-width: 0; }
    .cms-row-title { font-size: .9rem; font-weight: 500; color: #111827; }
    .cms-row-url { font-size: .76rem; color: #9ca3af; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; margin-top: 2px; word-break: break-all; }
    .cms-badges { display: flex; gap: 6px; flex-wrap: wrap; }
    .cms-badge { font-size: .68rem; font-weight: 600; border-radius: 999px; padding: 2px 8px; white-space: nowrap; }
    .cms-b-live { background: #ecfdf5; color: #047857; }
    .cms-b-hidden { background: #f3f4f6; color: #6b7280; }
    .cms-b-empty { background: #fffbeb; color: #b45309; }
    .cms-b-link { background: #f5f3ff; color: #6d28d9; }
    .cms-row-actions { display: flex; gap: 6px; }

    /* ---- Buttons / fields ---- */
    .cms-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 7px; padding: 7px 13px; font-size: .84rem; font-weight: 500; cursor: pointer; border: 1px solid transparent; white-space: nowrap; text-decoration: none; }
    .cms-btn-primary { background: #2563eb; color: #fff; }
    .cms-btn-primary:hover { background: #1d4ed8; color: #fff; }
    .cms-btn-light { background: #fff; border-color: #d1d5db; color: #374151; }
    .cms-btn-light:hover { background: #f9fafb; border-color: #9ca3af; color: #374151; }
    .cms-btn-icon { background: none; border: none; color: #9ca3af; cursor: pointer; padding: 5px 7px; border-radius: 6px; }
    .cms-btn-icon:hover { color: #2563eb; background: #eff6ff; }
    .cms-btn-icon.danger:hover { color: #dc2626; background: #fef2f2; }

    .cms-field { margin-bottom: 16px; }
    .cms-field > label { display: block; font-size: .84rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .cms-req { color: #ef4444; }
    .cms-input { width: 100%; padding: 8px 11px; border: 1px solid #d1d5db; border-radius: 7px; font-size: .88rem; background: #fff; color: #111827; }
    .cms-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.12); }
    .cms-help { font-size: .77rem; color: #6b7280; margin: 5px 0 0; }
    .cms-help a { color: #2563eb; }
    .cms-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .cms-advanced summary { cursor: pointer; font-size: .82rem; color: #2563eb; margin-bottom: 12px; }
    .cms-slug-row { display: flex; align-items: center; gap: 0; }
    .cms-slug-row span { font-size: .82rem; color: #6b7280; background: #f3f4f6; border: 1px solid #d1d5db; border-right: none; border-radius: 7px 0 0 7px; padding: 8px 10px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .cms-slug-row .cms-input { border-radius: 0 7px 7px 0; }
    .cms-check { display: flex; align-items: center; gap: 9px; margin: 4px 0 0; cursor: pointer; font-size: .87rem; color: #374151; }
    .cms-check input { width: 16px; height: 16px; cursor: pointer; }

    /* ---- Modal shell ---- */
    .cms-modal { width: 94%; max-width: 780px; max-height: 92vh; display: flex; flex-direction: column; }
    .cms-modal-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 16px 22px; border-bottom: 1px solid #f1f2f4; }
    .cms-modal-head h3 { font-size: 1.1rem; font-weight: 600; margin: 0; color: #111827; }
    .cms-modal-head p { margin: 3px 0 0; font-size: .8rem; color: #6b7280; }
    .cms-modal-head button { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1rem; }
    .cms-modal-body { padding: 20px 22px; overflow-y: auto; flex: 1; }
    .cms-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 22px; border-top: 1px solid #f1f2f4; background: #fafafa; }
    .ck-editor__editable_inline { min-height: 220px; }

    .cms-empty { background: #fff; border: 1px dashed #d1d5db; border-radius: 10px; padding: 48px 24px; text-align: center; }
    .cms-empty i { font-size: 2.4rem; color: #d1d5db; margin-bottom: 14px; }
    .cms-empty h4 { font-size: 1.05rem; color: #374151; font-weight: 600; margin-bottom: 6px; }
    .cms-empty p { color: #6b7280; font-size: .87rem; margin-bottom: 16px; }

    @media (max-width: 640px) { .cms-grid-2 { grid-template-columns: 1fr; } }
</style>
@endsection

@section('main-content')
@php $roleSlug = Str::slug(Auth::user()->getRoleNames()->first()); @endphp
<div class="cms-page">
    <div class="cms-header">
        <div>
            <h1>Pages</h1>
            <p>The footer's link columns. Each column is a page category; the pages inside it are its links.</p>
        </div>
        <div class="cms-header-actions">
            <a href="{{ route('role.page-categories.index', ['role' => $roleSlug]) }}" class="cms-btn cms-btn-light">
                <i class="fas fa-layer-group"></i> Footer columns
            </a>
            <button class="cms-btn cms-btn-primary create-new-btn"><i class="fas fa-plus"></i> New page</button>
        </div>
    </div>

    <div class="cms-stats">
        <div class="cms-stat">
            <div class="cms-stat-icon cms-i-blue"><i class="far fa-file-lines"></i></div>
            <div>
                <div class="cms-stat-value">{{ $stats['pages'] }}</div>
                <div class="cms-stat-label">Pages</div>
            </div>
        </div>
        <div class="cms-stat">
            <div class="cms-stat-icon cms-i-green"><i class="fas fa-eye"></i></div>
            <div>
                <div class="cms-stat-value">{{ $stats['live'] }}</div>
                <div class="cms-stat-label">Visible on site</div>
            </div>
        </div>
        <div class="cms-stat">
            <div class="cms-stat-icon cms-i-violet"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="cms-stat-value">{{ $stats['columns'] }}</div>
                <div class="cms-stat-label">Footer columns</div>
            </div>
        </div>
        <div class="cms-stat">
            <div class="cms-stat-icon cms-i-amber"><i class="fas fa-pen-to-square"></i></div>
            <div>
                <div class="cms-stat-value">{{ $stats['empty'] }}</div>
                <div class="cms-stat-label">Still to be written</div>
            </div>
        </div>
    </div>

    <form method="get" class="cms-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="cms-input" placeholder="Search title or slug…">
        <select name="category_id" class="cms-input">
            <option value="">All columns</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <select name="is_active" class="cms-input">
            <option value="">Any status</option>
            <option value="1" @selected(request('is_active') === '1')>Visible</option>
            <option value="0" @selected(request('is_active') === '0')>Hidden</option>
        </select>
        <button type="submit" class="cms-btn cms-btn-primary"><i class="fas fa-filter"></i> Filter</button>
        @if (request()->hasAny(['search', 'category_id', 'is_active']))
            <a href="{{ route('role.pages.index', ['role' => $roleSlug]) }}" class="cms-btn cms-btn-light">Reset</a>
        @endif
    </form>

    @if ($pages->isEmpty())
        <div class="cms-empty">
            <i class="far fa-file-lines"></i>
            <h4>No pages found</h4>
            <p>
                @if (request()->hasAny(['search', 'category_id', 'is_active']))
                    Nothing matches that filter.
                @else
                    Create pages like About us or Refund Policy and group them into footer columns.
                @endif
            </p>
            <button class="cms-btn cms-btn-primary create-new-btn"><i class="fas fa-plus"></i> New page</button>
        </div>
    @else
        {{-- One block per footer column, in the order the storefront renders them,
             then anything not filed into a column. --}}
        @foreach ($categories as $category)
            @php $columnPages = $grouped[$category->id] ?? collect(); @endphp
            @if ($columnPages->isNotEmpty())
                <div class="cms-column">
                    <div class="cms-column-head">
                        <h2>{{ $category->name }}</h2>
                        <span class="cms-column-count">{{ $columnPages->count() }} {{ Str::plural('link', $columnPages->count()) }}</span>
                        @unless ($category->is_active)
                            <span class="cms-column-hidden">Column hidden</span>
                        @endunless
                        <span class="cms-column-hint">Drag to reorder</span>
                    </div>
                    <ul class="cms-rows cms-sortable">
                        @include('pages._rows', ['rows' => $columnPages])
                    </ul>
                </div>
            @endif
        @endforeach

        @php $unfiled = $grouped[''] ?? collect(); @endphp
        @if ($unfiled->isNotEmpty())
            <div class="cms-column">
                <div class="cms-column-head">
                    <h2>Not in any footer column</h2>
                    <span class="cms-column-count">{{ $unfiled->count() }}</span>
                    <span class="cms-column-hint">Reachable by direct link only</span>
                </div>
                <ul class="cms-rows">
                    @include('pages._rows', ['rows' => $unfiled])
                </ul>
            </div>
        @endif
    @endif
</div>

@include('pages.create-modal')
@include('pages.edit-modal')
@include('pages.delete-modal')
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
$(function () {
    const roleSlug = @json($roleSlug);
    const reorderUrl = @json(route('role.pages.reorder', ['role' => $roleSlug]));
    const destroyTemplate = @json(route('role.pages.destroy', ['role' => $roleSlug, 'page' => '__ID__']));
    const pageUrlTemplate = @json(url('/page/__SLUG__'));

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true });

    function fail(xhr, fallback) {
        const message = xhr?.responseJSON?.message
            || (xhr?.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : null)
            || fallback;
        Swal.fire({ icon: 'error', title: 'Could not continue', text: message });
    }

    /* ---------------------------------------------------- rich text editors */

    // CKEditor is created when a modal opens and destroyed when it closes, so
    // reopening never stacks a second instance on the same textarea.
    const editors = {};

    function openEditor(id) {
        if (editors[id]) return Promise.resolve(editors[id]);
        return ClassicEditor.create(document.querySelector('#' + id))
            .then(function (editor) { editors[id] = editor; return editor; })
            .catch(function (e) { console.error(e); });
    }

    function closeEditor(id) {
        if (!editors[id]) return Promise.resolve();
        return editors[id].destroy().then(function () { delete editors[id]; });
    }

    /** CKEditor keeps its value out of the textarea, so sync before serializing. */
    function syncEditor(id) {
        if (editors[id]) $('#' + id).val(editors[id].getData());
    }

    /* ---------------------------------------------------- create */

    $('.create-new-btn').on('click', function () {
        $('#createForm')[0].reset();
        $('#createModal').removeClass('hidden');
        openEditor('create_content').then(function (editor) { if (editor) editor.setData(''); });
    });

    $('.modal-close-create').on('click', function () {
        $('#createModal').addClass('hidden');
        closeEditor('create_content');
    });

    $('#createSubmit').on('click', function () {
        const $btn = $(this).prop('disabled', true);
        syncEditor('create_content');

        $.post($('#createForm').attr('action'), $('#createForm').serialize())
            .done(function (response) {
                if (!response.success) {
                    $btn.prop('disabled', false);
                    return Swal.fire({ icon: 'error', title: 'Oops…', text: response.message });
                }
                toast.fire({ icon: 'success', title: 'Page created' });
                setTimeout(() => window.location.reload(), 700);
            })
            .fail(function (xhr) { $btn.prop('disabled', false); fail(xhr, 'Failed to create the page.'); });
    });

    /* ---------------------------------------------------- edit */

    $(document).on('click', '.edit-item-btn', function () {
        const $btn = $(this);
        const slug = $btn.data('slug');

        $('#editItemId').val($btn.data('item_id'));
        $('#edit_title').val($btn.data('title'));
        $('#edit_slug').val(slug);
        $('#edit_link_url').val($btn.data('link_url') || '');
        $('#edit_category_id').val($btn.data('category_id') || '');
        $('#edit_is_active').prop('checked', String($btn.data('is_active')) === '1');

        const url = pageUrlTemplate.replace('__SLUG__', slug);
        $('.cms-modal-url').text(url);
        $('.cms-view-link').attr('href', $btn.data('link_url') || url);

        $('#editModal').removeClass('hidden');

        // The body is held on the button as an encoded attribute rather than
        // fetched, so opening the editor costs no extra request.
        const content = $btn.attr('data-content') || '';
        openEditor('edit_content').then(function (editor) { if (editor) editor.setData(content); });
    });

    $('.modal-close-edit').on('click', function () {
        $('#editModal').addClass('hidden');
        closeEditor('edit_content');
    });

    $('#editSubmit').on('click', function () {
        const $btn = $(this).prop('disabled', true);
        syncEditor('edit_content');
        const url = $('#editForm').data('action-template').replace('__ID__', $('#editItemId').val());

        $.post(url, $('#editForm').serialize())
            .done(function (response) {
                if (!response.success) {
                    $btn.prop('disabled', false);
                    return Swal.fire({ icon: 'error', title: 'Oops…', text: response.message });
                }
                toast.fire({ icon: 'success', title: 'Page updated' });
                setTimeout(() => window.location.reload(), 700);
            })
            .fail(function (xhr) { $btn.prop('disabled', false); fail(xhr, 'Failed to update the page.'); });
    });

    /* ---------------------------------------------------- delete */

    $(document).on('click', '.delete-item-btn', function () {
        $('#deleteName').text($(this).data('title'));
        $('#confirmDeleteBtn').data('target-id', $(this).data('item_id'));
        $('#deleteModal').removeClass('hidden');
    });

    $('.modal-close-delete').on('click', () => $('#deleteModal').addClass('hidden'));

    $('#confirmDeleteBtn').on('click', function () {
        const id = $(this).data('target-id');

        $.ajax({
            url: destroyTemplate.replace('__ID__', id),
            method: 'POST',
            data: { _method: 'DELETE', item_id: id }
        })
        .done(function (response) {
            $('#deleteModal').addClass('hidden');
            if (!response.success) {
                return Swal.fire({ icon: 'error', title: 'Cannot delete', text: response.message });
            }
            toast.fire({ icon: 'success', title: 'Page deleted' });
            setTimeout(() => window.location.reload(), 700);
        })
        .fail(function (xhr) { $('#deleteModal').addClass('hidden'); fail(xhr, 'Failed to delete the page.'); });
    });

    /* ---------------------------------------------------- reordering */

    document.querySelectorAll('.cms-sortable').forEach(function (list) {
        Sortable.create(list, {
            handle: '.cms-handle',
            animation: 150,
            onEnd: function () {
                const ids = $(list).find('.cms-row').map(function () { return $(this).data('id'); }).get();
                $.post(reorderUrl, { ids: ids })
                    .done(() => toast.fire({ icon: 'success', title: 'Order saved' }))
                    .fail((xhr) => fail(xhr, 'Failed to save the new order.'));
            }
        });
    });
});
</script>
@endsection
