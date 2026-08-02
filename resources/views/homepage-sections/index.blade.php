@extends('layout.app')
@section('meta-information')
    <title>Manage Homepage Sections</title>
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .modal { transition: opacity 0.25s ease; }
    .modal-backdrop { background-color: rgba(0, 0, 0, 0.5); }
    .states-table { margin-top: 2rem; }
    .states-table .states-table-container { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); overflow: hidden; }
    .states-table .states-table-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e9ecef; }
    .states-table .states-table-header .states-table-title { margin: 0; font-size: 1.25rem; font-weight: 600; color: #333; }
    .states-table .states-table-header .btn { border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; }
    .states-table .states-table-content { padding: 0; }
    .states-table .states-table-content .table-responsive { overflow-x: auto; }
    .states-table .states-table-content .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
    .states-table .states-table-content .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 1rem 0.75rem; font-weight: 600; color: #495057; }
    .states-table .states-table-content .table tbody td { padding: 1rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #e9ecef; }
    .states-table .states-table-content .table tbody tr:hover { background-color: #f8f9fa; }
    .states-table .states-table-content .badge { font-size: 0.75rem; padding: 0.375rem 0.75rem; border-radius: 6px; font-weight: 500; }
    .states-table-header { background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%); color: white }
    .states-table .states-table-content .btn-group { border-radius: 6px; overflow: hidden; }
    .states-table .states-table-content .btn-group .btn { border-radius: 0; padding: 0.375rem 0.75rem; }
    .states-table .states-table-content .btn-group .btn:first-child { border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
    .states-table .states-table-content .btn-group .btn:last-child { border-top-right-radius: 6px; border-bottom-right-radius: 6px; }
    .states-table .states-table-content .pagination { margin-bottom: 0; padding: 1rem; }
    .select2-container .select2-selection--single { height: 42px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px; position: absolute; top: 1px; right: 3px; width: 20px; }
    .drag-handle { cursor: grab; color: #9ca3af; }
    .drag-handle:active { cursor: grabbing; }
    .sortable-ghost { background: #eff6ff !important; }
    .type-fields { display: none; }
    .type-fields.active { display: block; }
</style>
@endsection
@section('main-content')
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-list mr-2"></i>Homepage Sections
                </h2>
                <button class="btn btn-primary create-new-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New Section
                </button>
            </div>

            <div class="states-table-content">
                <p class="text-gray-500 text-sm p-4 pb-0"><i class="fas fa-arrows-alt mr-1"></i> Drag rows by the handle to reorder how sections appear on the homepage.</p>
                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th style="padding-left: 0" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title / Heading</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-sections" class="bg-white divide-y divide-gray-200">
                            @forelse ($datas as $key => $value)
                                <tr data-id="{{ $value->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap"><i class="fas fa-grip-vertical drag-handle"></i></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <strong>{{ ($datas->currentPage() - 1) * $datas->perPage() + $key + 1 }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge text-white bg-indigo-500 px-2 py-1 rounded-full text-xs">{{ str_replace('_', ' ', $value->type) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium">{{ $value->title }}</div>
                                        @if($value->heading)
                                            <div class="text-xs text-gray-500">Heading: {{ $value->heading }}</div>
                                        @endif
                                        @if($value->type === 'product_row' && $value->config)
                                            <div class="text-xs text-gray-400">Source: {{ $value->config['source'] ?? 'manual' }}{{ !empty($value->config['limit']) ? ' · Limit '.$value->config['limit'] : '' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($value->is_active)
                                            <span class="badge text-white bg-green-500 px-2 py-1 rounded-full text-xs">Active</span>
                                        @else
                                            <span class="badge text-white bg-yellow-500 px-2 py-1 rounded-full text-xs">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="btn-group btn-group-sm flex space-x-1">
                                            @if (in_array($value->type, ['split_banner', 'hero_slider']))
                                                <a href="{{ route('role.banners.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'homepage_section_id' => $value->id]) }}"
                                                    class="btn btn-outline-secondary border border-gray-400 text-gray-600 hover:bg-gray-600 hover:text-white px-3 py-1 rounded-md transition duration-200"
                                                    title="{{ $value->type === 'hero_slider' ? 'Manage Side Banner(s)' : 'Manage Banners' }}">
                                                    <i class="fas fa-images"></i>
                                                </a>
                                            @endif
                                            <button class="btn btn-outline-primary edit-item-btn border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded-md transition duration-200"
                                                data-item_id="{{ $value->id }}"
                                                data-type="{{ $value->type }}"
                                                data-title="{{ $value->title }}"
                                                data-heading="{{ $value->heading }}"
                                                data-subheading="{{ $value->subheading }}"
                                                data-source="{{ $value->config['source'] ?? '' }}"
                                                data-category_id="{{ $value->config['category_id'] ?? '' }}"
                                                data-limit="{{ $value->config['limit'] ?? '' }}"
                                                data-product_ids="{{ $value->products->pluck('id')->implode(',') }}"
                                                data-starts_at="{{ optional($value->starts_at)->format('Y-m-d\TH:i') }}"
                                                data-ends_at="{{ optional($value->ends_at)->format('Y-m-d\TH:i') }}"
                                                data-is_active="{{ (int) $value->is_active }}"
                                                title="Edit Section">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200" onclick="confirmDelete('{{ $value->id }}', '{{ $value->title }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8">
                                        <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                                        <h4 class="text-gray-500 text-xl font-medium mb-2">No homepage sections yet</h4>
                                        <p class="text-gray-400 mb-4">Add a section to start building the homepage.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t border-gray-200">
                    {{ $datas->links() }}
                </div>
            </div>
        </div>
    </div>

    @include('homepage-sections.create-modal')
    @include('homepage-sections.edit-modal')
    @include('homepage-sections.delete-modal')
@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            function toggleTypeFields(prefix) {
                const type = $('#' + prefix + '_type').val();
                $('#' + prefix + '_product_row_fields').toggleClass('active', type === 'product_row');
                toggleSourceFields(prefix);
            }

            function toggleSourceFields(prefix) {
                const source = $('#' + prefix + '_source').val();
                $('#' + prefix + '_category_field').toggleClass('active', source === 'category');
                $('#' + prefix + '_manual_field').toggleClass('active', source === 'manual');
            }

            $('#create_type').on('change', function () { toggleTypeFields('create'); });
            $('#create_source').on('change', function () { toggleSourceFields('create'); });
            $('#edit_type').on('change', function () { toggleTypeFields('edit'); });
            $('#edit_source').on('change', function () { toggleSourceFields('edit'); });

            // Show create modal
            $('.create-new-btn').click(function () {
                $('#createForm')[0].reset();
                $('.select2').val(null).trigger('change');
                toggleTypeFields('create');
                $('#createModal').removeClass('hidden');
            });

            // Show edit modal
            $('.edit-item-btn').click(function () {
                $('#editItemId').val($(this).data('item_id'));
                $('#edit_type').val($(this).data('type')).trigger('change');
                $('#edit_title').val($(this).data('title'));
                $('#edit_heading').val($(this).data('heading'));
                $('#edit_subheading').val($(this).data('subheading'));
                $('#edit_source').val($(this).data('source')).trigger('change');
                $('#edit_category_id').val($(this).data('category_id')).trigger('change');
                $('#edit_limit').val($(this).data('limit'));
                const productIds = ($(this).data('product_ids') || '').toString().split(',').filter(Boolean).map(Number);
                $('#edit_product_ids').val(productIds).trigger('change');
                $('#edit_starts_at').val($(this).data('starts_at'));
                $('#edit_ends_at').val($(this).data('ends_at'));
                $('#edit_is_active').prop('checked', !!$(this).data('is_active'));
                $('#editModal').removeClass('hidden');
            });

            $('.modal-close-create, .modal-backdrop').click(function (e) {
                if ($(e.target).closest('.modal-close-create').length || $(e.target).hasClass('modal-backdrop')) {
                    $('#createModal').addClass('hidden');
                }
            });
            $('.modal-close-edit, .modal-backdrop').click(function (e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-edit').length) {
                    $('#editModal').addClass('hidden');
                }
            });
            $('.modal-close-delete, .modal-backdrop').click(function (e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-delete').length) {
                    $('#deleteModal').addClass('hidden');
                }
            });

            $('#createSubmit').click(function () {
                if (!$('#create_title').val().trim() || !$('#create_type').val()) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Type and Title are required.' });
                    return;
                }
                let formData = new FormData($('#createForm')[0]);
                $.ajax({
                    url: $('#createForm').attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            $('#createModal').addClass('hidden');
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message || 'Something went wrong.' });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create section.' });
                    }
                });
            });

            $('#editSubmit').click(function () {
                if (!$('#edit_title').val().trim() || !$('#edit_type').val()) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Type and Title are required.' });
                    return;
                }
                let formData = new FormData($('#editForm')[0]);
                $.ajax({
                    url: $(this).data('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            $('#editModal').addClass('hidden');
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message || 'Update failed.' });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong!' });
                    }
                });
            });

            $('#confirmDeleteBtn').click(function () {
                const dataId = $(this).data('item-id');
                $.ajax({
                    url: $(this).data('action'),
                    method: 'DELETE',
                    data: { item_id: dataId },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            $('#deleteModal').addClass('hidden');
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong!' });
                    }
                });
            });

            // Drag & drop reorder
            const sortableEl = document.getElementById('sortable-sections');
            if (sortableEl) {
                new Sortable(sortableEl, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function () {
                        const order = [...sortableEl.querySelectorAll('tr')].map(tr => tr.dataset.id);
                        $.ajax({
                            url: '{{ route('role.homepage-sections.reorder', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}',
                            method: 'POST',
                            data: { order: order },
                            success: function () {
                                toastr.success('Order updated');
                            }
                        });
                    }
                });
            }
        });

        function confirmDelete(id, name) {
            $('#deleteName').text(name);
            $('#confirmDeleteBtn').data('item-id', id);
            $('#deleteModal').removeClass('hidden');
        }
    </script>
@endsection
