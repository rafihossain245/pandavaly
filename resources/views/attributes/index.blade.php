@extends('layout.app')
@section('meta-information')
    <title>Attributes</title>
@endsection

@section('css')
<style>
    .modal { transition: opacity .25s ease; }
    .modal-backdrop { background-color: rgba(0,0,0,.5); }
    .hidden { display: none; }

    .attr-page { padding-bottom: 30px; }

    /* ---- Page header ---- */
    .attr-header { display: flex; justify-content: space-between; align-items: center; gap: 14px; flex-wrap: wrap; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; margin-bottom: 18px; }
    .attr-header h1 { font-size: 1.35rem; font-weight: 600; margin: 0; color: #111827; }
    .attr-header p { margin: 4px 0 0; font-size: .85rem; color: #6b7280; }

    /* ---- Stats ---- */
    .attr-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 18px; }
    .attr-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
    .attr-stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex: 0 0 auto; }
    .attr-stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; color: #111827; }
    .attr-stat-label { font-size: .78rem; color: #6b7280; margin-top: 4px; }
    .attr-i-blue { background: #eff6ff; color: #2563eb; }
    .attr-i-green { background: #ecfdf5; color: #059669; }
    .attr-i-amber { background: #fffbeb; color: #d97706; }
    .attr-i-rose { background: #fef2f2; color: #e11d48; }

    /* ---- Filters ---- */
    .attr-filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 16px; margin-bottom: 18px; }
    .attr-filters .attr-input, .attr-filters select { width: auto; min-width: 190px; margin: 0; }

    /* ---- Attribute cards ---- */
    .attr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 16px; }
    .attr-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px 18px; display: flex; flex-direction: column; gap: 12px; transition: box-shadow .15s, border-color .15s; }
    .attr-card:hover { border-color: #cbd5e1; box-shadow: 0 3px 10px rgba(0,0,0,.05); }
    .attr-card.sortable-ghost { opacity: .4; }
    .attr-card-top { display: flex; align-items: flex-start; gap: 10px; }
    .attr-drag { cursor: grab; color: #cbd5e1; padding-top: 3px; }
    .attr-drag:active { cursor: grabbing; }
    .attr-card-title { font-size: 1rem; font-weight: 600; color: #111827; margin: 0; }
    .attr-card-meta { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }

    .attr-tag { font-size: .7rem; padding: 2px 8px; border-radius: 999px; font-weight: 600; letter-spacing: .02em; }
    .attr-tag-code { background: #f3f4f6; color: #4b5563; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .attr-tag-pill { background: #eff6ff; color: #1d4ed8; }
    .attr-tag-swatch { background: #fdf2f8; color: #be185d; }
    .attr-tag-dropdown { background: #f5f3ff; color: #6d28d9; }
    .attr-tag-use { background: #ecfdf5; color: #047857; }
    .attr-tag-unused { background: #fffbeb; color: #b45309; }

    .attr-values-preview { display: flex; flex-wrap: wrap; gap: 6px; min-height: 30px; }
    .attr-vchip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid #e5e7eb; background: #fafafa; border-radius: 999px; padding: 3px 10px; font-size: .78rem; color: #374151; }
    .attr-vdot { width: 13px; height: 13px; border-radius: 50%; border: 1px solid rgba(0,0,0,.15); }
    .attr-vmore { font-size: .78rem; color: #6b7280; align-self: center; }
    .attr-novalues { font-size: .8rem; color: #b45309; background: #fffbeb; border: 1px dashed #fde68a; border-radius: 6px; padding: 6px 10px; width: 100%; }

    .attr-card-actions { display: flex; gap: 8px; border-top: 1px solid #f3f4f6; padding-top: 12px; margin-top: auto; }

    /* ---- Buttons ---- */
    .attr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 7px; padding: 7px 14px; font-size: .84rem; font-weight: 500; cursor: pointer; border: 1px solid transparent; white-space: nowrap; }
    .attr-btn-primary { background: #2563eb; color: #fff; }
    .attr-btn-primary:hover { background: #1d4ed8; color: #fff; }
    .attr-btn-light { background: #fff; border-color: #d1d5db; color: #374151; flex: 1; }
    .attr-btn-light:hover { background: #f9fafb; border-color: #9ca3af; }
    .attr-btn-danger-ghost { background: #fff; border-color: #fecaca; color: #dc2626; }
    .attr-btn-danger-ghost:hover { background: #fef2f2; }

    /* ---- Form fields (shared with the modals) ---- */
    .attr-field { margin-bottom: 16px; }
    .attr-field > label { display: block; font-size: .84rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .attr-req { color: #ef4444; }
    .attr-input { width: 100%; padding: 8px 11px; border: 1px solid #d1d5db; border-radius: 7px; font-size: .88rem; background: #fff; color: #111827; }
    .attr-input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.12); }
    .attr-help { font-size: .77rem; color: #6b7280; margin: 5px 0 0; }
    .attr-advanced { margin-top: 4px; }
    .attr-advanced summary { cursor: pointer; font-size: .82rem; color: #2563eb; margin-bottom: 10px; }

    .attr-display-picker { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .attr-display-option input { position: absolute; opacity: 0; pointer-events: none; }
    .attr-display-card { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 3px; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 12px 8px; cursor: pointer; height: 100%; transition: all .12s; }
    .attr-display-card i { font-size: 1.05rem; color: #9ca3af; }
    .attr-display-card strong { font-size: .8rem; color: #374151; }
    .attr-display-card small { font-size: .68rem; color: #9ca3af; line-height: 1.25; }
    .attr-display-option input:checked + .attr-display-card { border-color: #2563eb; background: #eff6ff; }
    .attr-display-option input:checked + .attr-display-card i,
    .attr-display-option input:checked + .attr-display-card strong { color: #1d4ed8; }

    /* ---- Values editor ---- */
    .attr-add-row { display: flex; gap: 8px; align-items: center; }
    .attr-color { width: 40px; height: 38px; padding: 2px; border: 1px solid #d1d5db; border-radius: 7px; background: #fff; cursor: pointer; flex: 0 0 auto; }
    .attr-value-list { list-style: none; margin: 14px 0 0; padding: 0; border: 1px solid #e5e7eb; border-radius: 8px; max-height: 340px; overflow-y: auto; }
    .attr-value-list:empty { display: none; }
    .attr-value-row { display: flex; align-items: center; gap: 9px; padding: 8px 10px; border-bottom: 1px solid #f3f4f6; background: #fff; }
    .attr-value-row:last-child { border-bottom: none; }
    .attr-value-row.sortable-ghost { opacity: .4; }
    .attr-value-row .attr-input { flex: 1; padding: 5px 9px; font-size: .85rem; }
    .attr-vhandle { cursor: grab; color: #cbd5e1; }
    .attr-vuse { font-size: .7rem; color: #6b7280; background: #f3f4f6; border-radius: 999px; padding: 2px 8px; white-space: nowrap; }
    .attr-vdel { background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px 7px; border-radius: 5px; }
    .attr-vdel:hover { color: #dc2626; background: #fef2f2; }
    .attr-empty-note { font-size: .84rem; color: #6b7280; text-align: center; padding: 22px 10px; }

    /* ---- Empty state ---- */
    .attr-empty { background: #fff; border: 1px dashed #d1d5db; border-radius: 10px; padding: 48px 24px; text-align: center; }
    .attr-empty i { font-size: 2.4rem; color: #d1d5db; margin-bottom: 14px; }
    .attr-empty h4 { font-size: 1.05rem; color: #374151; font-weight: 600; margin-bottom: 6px; }
    .attr-empty p { color: #6b7280; font-size: .87rem; margin-bottom: 16px; }
</style>
@endsection

@section('main-content')
<div class="attr-page">
    <div class="attr-header">
        <div>
            <h1>Attributes</h1>
            <p>Options a product can vary by &mdash; size, colour, weight. Add the values here, then pick them on a product.</p>
        </div>
        <button class="attr-btn attr-btn-primary create-new-btn">
            <i class="fas fa-plus"></i> New attribute
        </button>
    </div>

    <div class="attr-stats">
        <div class="attr-stat">
            <div class="attr-stat-icon attr-i-blue"><i class="fas fa-tags"></i></div>
            <div>
                <div class="attr-stat-value">{{ $stats['attributes'] }}</div>
                <div class="attr-stat-label">Attributes</div>
            </div>
        </div>
        <div class="attr-stat">
            <div class="attr-stat-icon attr-i-green"><i class="fas fa-list-ul"></i></div>
            <div>
                <div class="attr-stat-value">{{ $stats['values'] }}</div>
                <div class="attr-stat-label">Values</div>
            </div>
        </div>
        <div class="attr-stat">
            <div class="attr-stat-icon attr-i-rose"><i class="fas fa-palette"></i></div>
            <div>
                <div class="attr-stat-value">{{ $stats['swatches'] }}</div>
                <div class="attr-stat-label">Colour swatches</div>
            </div>
        </div>
        <div class="attr-stat">
            <div class="attr-stat-icon attr-i-amber"><i class="fas fa-box-open"></i></div>
            <div>
                <div class="attr-stat-value">{{ $stats['in_use'] }}</div>
                <div class="attr-stat-label">Used on products</div>
            </div>
        </div>
    </div>

    <form method="get" class="attr-filters">
        <input type="text" name="search" value="{{ request('search') }}" class="attr-input"
               placeholder="Search name or code…">
        <select name="display_type" class="attr-input">
            <option value="">All display types</option>
            @foreach (\App\Models\Attribute::DISPLAY_TYPES as $value => $label)
                <option value="{{ $value }}" @selected(request('display_type') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="attr-btn attr-btn-primary"><i class="fas fa-filter"></i> Filter</button>
        @if (request()->hasAny(['search', 'display_type']))
            <a href="{{ route('role.attributes.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
               class="attr-btn attr-btn-light" style="flex:0 0 auto;">Reset</a>
        @endif
    </form>

    @if ($attributes->isEmpty())
        <div class="attr-empty">
            <i class="fas fa-tags"></i>
            <h4>No attributes yet</h4>
            <p>Start with <strong>Size</strong> or <strong>Colour</strong> &mdash; then products can be sold in variants.</p>
            <button class="attr-btn attr-btn-primary create-new-btn"><i class="fas fa-plus"></i> Create your first attribute</button>
        </div>
    @else
        <div class="attr-grid" id="attributeGrid">
            @foreach ($attributes as $attribute)
                @php $used = $usageCounts[$attribute->id] ?? 0; @endphp
                <div class="attr-card" data-id="{{ $attribute->id }}">
                    <div class="attr-card-top">
                        <span class="attr-drag" title="Drag to reorder"><i class="fas fa-grip-vertical"></i></span>
                        <div style="flex:1;">
                            <h3 class="attr-card-title">{{ $attribute->name }}</h3>
                            <div class="attr-card-meta">
                                <span class="attr-tag attr-tag-code">{{ $attribute->code }}</span>
                                <span class="attr-tag attr-tag-{{ $attribute->display_type }}">
                                    {{ \App\Models\Attribute::DISPLAY_TYPES[$attribute->display_type] ?? $attribute->display_type }}
                                </span>
                                @if ($used > 0)
                                    <span class="attr-tag attr-tag-use">{{ $used }} {{ Str::plural('product', $used) }}</span>
                                @else
                                    <span class="attr-tag attr-tag-unused">Not used yet</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="attr-values-preview">
                        @forelse ($attribute->values->take(8) as $value)
                            <span class="attr-vchip">
                                @if ($attribute->isSwatch())
                                    <span class="attr-vdot" style="background: {{ $value->swatchColor() }}"></span>
                                @endif
                                {{ $value->value }}
                            </span>
                        @empty
                            <span class="attr-novalues">
                                <i class="fas fa-triangle-exclamation"></i>
                                No values yet — this attribute will not appear on the product form.
                            </span>
                        @endforelse

                        @if ($attribute->values_count > 8)
                            <span class="attr-vmore">+{{ $attribute->values_count - 8 }} more</span>
                        @endif
                    </div>

                    <div class="attr-card-actions">
                        <button class="attr-btn attr-btn-light manage-values-btn"
                                data-attribute_id="{{ $attribute->id }}"
                                data-attribute_name="{{ $attribute->name }}"
                                data-display_type="{{ $attribute->display_type }}">
                            <i class="fas fa-list-ul"></i> Values ({{ $attribute->values_count }})
                        </button>
                        <button class="attr-btn attr-btn-light edit-item-btn"
                                data-item_id="{{ $attribute->id }}"
                                data-name="{{ $attribute->name }}"
                                data-code="{{ $attribute->code }}"
                                data-display_type="{{ $attribute->display_type }}">
                            <i class="fas fa-pen"></i> Edit
                        </button>
                        <button class="attr-btn attr-btn-danger-ghost"
                                onclick="confirmDelete('{{ $attribute->id }}', '{{ $attribute->name }}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $attributes->appends(request()->all())->links() }}
        </div>
    @endif
</div>

@include('attributes.create-modal')
@include('attributes.edit-modal')
@include('attributes.delete-modal')
@include('attributes.values-modal')
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
$(function () {
    const roleSlug = @json(Str::slug(Auth::user()->getRoleNames()->first()));
    const valuesUrl = @json(route('role.attribute-values.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]));
    const valuesReorderUrl = @json(route('role.attribute-values.reorder', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]));
    const attributesReorderUrl = @json(route('role.attributes.reorder', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]));
    const valuesIndexTemplate = @json(route('role.attributes.values.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'attribute' => '__ID__']));
    const destroyTemplate = @json(route('role.attributes.destroy', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'attribute' => '__ID__']));

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 2200, timerProgressBar: true
    });

    function fail(xhr, fallback) {
        const message = xhr?.responseJSON?.message
            || (xhr?.responseJSON?.errors ? Object.values(xhr.responseJSON.errors)[0][0] : null)
            || fallback;
        Swal.fire({ icon: 'error', title: 'Could not continue', text: message });
    }

    /* ---------------------------------------------------------- modal plumbing */

    $('.create-new-btn').on('click', () => $('#createModal').removeClass('hidden'));
    $('.modal-close-create').on('click', () => $('#createModal').addClass('hidden'));
    $('.modal-close-edit').on('click', () => $('#editModal').addClass('hidden'));
    $('.modal-close-delete').on('click', () => $('#deleteModal').addClass('hidden'));
    $('.modal-close-values').on('click', function () {
        $('#valuesModal').addClass('hidden');
        // Value counts and chips on the cards are rendered server-side.
        if ($('#valuesModal').data('dirty')) window.location.reload();
    });

    /* ---------------------------------------------------------- create */

    $('#createSubmit').on('click', function () {
        const $btn = $(this).prop('disabled', true);

        $.post($('#createForm').attr('action'), $('#createForm').serialize())
            .done(function (response) {
                if (!response.success) {
                    $btn.prop('disabled', false);
                    return Swal.fire({ icon: 'error', title: 'Oops…', text: response.message });
                }
                toast.fire({ icon: 'success', title: 'Attribute created' });
                setTimeout(() => window.location.reload(), 700);
            })
            .fail(function (xhr) {
                $btn.prop('disabled', false);
                fail(xhr, 'Failed to create the attribute.');
            });
    });

    /* ---------------------------------------------------------- edit */

    $('.edit-item-btn').on('click', function () {
        const $btn = $(this);
        $('#editItemId').val($btn.data('item_id'));
        $('#edit_name').val($btn.data('name'));
        $('#edit_code').val($btn.data('code'));
        $('#editForm input[name="display_type"][value="' + $btn.data('display_type') + '"]').prop('checked', true);
        $('#editModal').removeClass('hidden');
    });

    $('#editSubmit').on('click', function () {
        const $btn = $(this).prop('disabled', true);
        const url = $('#editForm').data('action-template').replace('__ID__', $('#editItemId').val());

        $.post(url, $('#editForm').serialize())
            .done(function (response) {
                if (!response.success) {
                    $btn.prop('disabled', false);
                    return Swal.fire({ icon: 'error', title: 'Oops…', text: response.message });
                }
                toast.fire({ icon: 'success', title: 'Attribute updated' });
                setTimeout(() => window.location.reload(), 700);
            })
            .fail(function (xhr) {
                $btn.prop('disabled', false);
                fail(xhr, 'Failed to update the attribute.');
            });
    });

    /* ---------------------------------------------------------- delete */

    window.confirmDelete = function (id, name) {
        $('#deleteName').text(name);
        $('#confirmDeleteBtn').data('target-id', id);
        $('#deleteModal').removeClass('hidden');
    };

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
            toast.fire({ icon: 'success', title: 'Attribute deleted' });
            setTimeout(() => window.location.reload(), 700);
        })
        .fail(function (xhr) {
            $('#deleteModal').addClass('hidden');
            fail(xhr, 'Failed to delete the attribute.');
        });
    });

    /* ---------------------------------------------------------- values editor */

    function isSwatch() { return $('#valuesDisplayType').val() === 'swatch'; }

    function renderValues(values) {
        const $list = $('#valuesList').empty();
        $('#valuesEmptyMsg').toggleClass('hidden', values.length > 0);

        values.forEach(function (value) {
            const $row = $('<li class="attr-value-row"></li>').attr('data-id', value.id);
            $row.append('<span class="attr-vhandle"><i class="fas fa-grip-vertical"></i></span>');

            if (isSwatch()) {
                $row.append(
                    $('<input type="color" class="attr-color attr-v-color">')
                        .val(value.color_code || '#d1d5db')
                );
            }

            $row.append($('<input type="text" class="attr-input attr-v-text">').val(value.value));

            if (value.in_use > 0) {
                $row.append($('<span class="attr-vuse"></span>').text(value.in_use + ' in use'));
            }

            $row.append('<button type="button" class="attr-vdel" title="Delete value"><i class="fas fa-trash"></i></button>');
            $list.append($row);
        });

        makeValuesSortable();
    }

    function loadValues(attributeId) {
        $.get(valuesIndexTemplate.replace('__ID__', attributeId))
            .done(function (response) {
                $('#valuesDisplayType').val(response.attribute.display_type || 'pill');
                $('#newValueColor').toggle(isSwatch());
                $('#valuesAddHint').text(isSwatch()
                    ? 'Pick a colour, type the name, then Add. Paste several names at once to add them without colours.'
                    : 'Separate several values with commas to add them all at once.');
                renderValues(response.values || []);
            })
            .fail((xhr) => fail(xhr, 'Failed to load values.'));
    }

    $('.manage-values-btn').on('click', function () {
        const $btn = $(this);
        $('#valuesAttributeId').val($btn.data('attribute_id'));
        $('#valuesDisplayType').val($btn.data('display_type'));
        $('#valuesAttributeName').text($btn.data('attribute_name'));
        $('#newValueInput').val('');
        $('#valuesModal').removeClass('hidden').data('dirty', false);
        loadValues($btn.data('attribute_id'));
    });

    function addValue() {
        const attributeId = $('#valuesAttributeId').val();
        const value = $('#newValueInput').val().trim();
        if (!value) return;

        $.post(valuesUrl, {
            attribute_id: attributeId,
            value: value,
            color_code: isSwatch() ? $('#newValueColor').val() : null
        })
        .done(function (response) {
            if (!response.success) {
                return Swal.fire({ icon: 'error', title: 'Oops…', text: response.message });
            }
            $('#newValueInput').val('');
            $('#valuesModal').data('dirty', true);
            loadValues(attributeId);
        })
        .fail((xhr) => fail(xhr, 'Failed to add the value.'));
    }

    $('#addValueBtn').on('click', addValue);
    $('#newValueInput').on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addValue(); }
    });

    // Inline edits save when the field loses focus — no per-row save button.
    function saveValue($row) {
        const id = $row.data('id');
        const value = $row.find('.attr-v-text').val().trim();
        if (!value) return;

        $.ajax({
            url: '/' + roleSlug + '/attribute-values/' + id,
            method: 'POST',
            data: {
                _method: 'PUT',
                value: value,
                color_code: isSwatch() ? $row.find('.attr-v-color').val() : null
            }
        })
        .done(function (response) {
            if (!response.success) {
                Swal.fire({ icon: 'error', title: 'Oops…', text: response.message });
                return loadValues($('#valuesAttributeId').val());
            }
            $('#valuesModal').data('dirty', true);
        })
        .fail((xhr) => fail(xhr, 'Failed to save the value.'));
    }

    $('#valuesList').on('change blur', '.attr-v-text', function () { saveValue($(this).closest('.attr-value-row')); });
    $('#valuesList').on('change', '.attr-v-color', function () { saveValue($(this).closest('.attr-value-row')); });

    $('#valuesList').on('click', '.attr-vdel', function () {
        const $row = $(this).closest('.attr-value-row');

        Swal.fire({
            icon: 'warning',
            title: 'Delete this value?',
            text: 'It will be removed from the list shoppers can choose from.',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            confirmButtonColor: '#dc2626'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: '/' + roleSlug + '/attribute-values/' + $row.data('id'),
                method: 'POST',
                data: { _method: 'DELETE' }
            })
            .done(function (response) {
                if (!response.success) {
                    return Swal.fire({ icon: 'error', title: 'Cannot delete', text: response.message });
                }
                $('#valuesModal').data('dirty', true);
                loadValues($('#valuesAttributeId').val());
            })
            .fail((xhr) => fail(xhr, 'Failed to delete the value.'));
        });
    });

    /* ---------------------------------------------------------- reordering */

    let valuesSortable = null;
    function makeValuesSortable() {
        if (valuesSortable) valuesSortable.destroy();
        valuesSortable = Sortable.create(document.getElementById('valuesList'), {
            handle: '.attr-vhandle',
            animation: 150,
            onEnd: function () {
                const ids = $('#valuesList .attr-value-row').map(function () { return $(this).data('id'); }).get();
                $.post(valuesReorderUrl, { ids: ids })
                    .done(() => $('#valuesModal').data('dirty', true))
                    .fail((xhr) => fail(xhr, 'Failed to save the new order.'));
            }
        });
    }

    const grid = document.getElementById('attributeGrid');
    if (grid) {
        Sortable.create(grid, {
            handle: '.attr-drag',
            animation: 150,
            onEnd: function () {
                const ids = $('#attributeGrid .attr-card').map(function () { return $(this).data('id'); }).get();
                $.post(attributesReorderUrl, { ids: ids })
                    .done(() => toast.fire({ icon: 'success', title: 'Order saved' }))
                    .fail((xhr) => fail(xhr, 'Failed to save the new order.'));
            }
        });
    }
});
</script>
@endsection
