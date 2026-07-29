@extends('layout.app')
@section('meta-information')
    <title>Manage Banners</title>
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
    .select2-container .select2-selection--single { height: 42px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 42px; position: absolute; top: 1px; right: 3px; width: 20px; }
</style>
@endsection
@section('main-content')
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-images mr-2"></i>Banners
                    @if($sectionId && $sections->firstWhere('id', (int) $sectionId))
                        <span class="text-sm font-normal">— {{ $sections->firstWhere('id', (int) $sectionId)->title }}</span>
                    @endif
                </h2>
                <div class="flex gap-2">
                    <a href="{{ route('role.homepage-sections.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="btn btn-outline-light text-white border border-white px-4 py-2 rounded-md">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Sections
                    </a>
                    <button class="btn btn-primary create-new-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Add New Banner
                    </button>
                </div>
            </div>

            <div class="states-table-content">
                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th style="padding-left: 20px" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($datas as $key => $value)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: 20px">
                                        <strong>{{ ($datas->currentPage() - 1) * $datas->perPage() + $key + 1 }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->homepageSection->title ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap"><img src="{{ asset($value->image_path) }}" alt="" width="120"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->title }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($value->is_active)
                                            <span class="badge text-white bg-green-500 px-2 py-1 rounded-full text-xs">Active</span>
                                        @else
                                            <span class="badge text-white bg-yellow-500 px-2 py-1 rounded-full text-xs">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="btn-group btn-group-sm flex space-x-1">
                                            <button class="btn btn-outline-primary edit-item-btn border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded-md transition duration-200"
                                                data-item_id="{{ $value->id }}"
                                                data-homepage_section_id="{{ $value->homepage_section_id }}"
                                                data-title="{{ $value->title }}"
                                                data-subtitle="{{ $value->subtitle }}"
                                                data-link="{{ $value->link }}"
                                                data-sort_order="{{ $value->sort_order }}"
                                                data-image="{{ asset($value->image_path) }}"
                                                data-is_active="{{ (int) $value->is_active }}"
                                                title="Edit Banner">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200" onclick="confirmDelete('{{ $value->id }}', 'this banner')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8">
                                        <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                                        <h4 class="text-gray-500 text-xl font-medium mb-2">No banners found</h4>
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

    @include('banners.create-modal')
    @include('banners.edit-modal')
    @include('banners.delete-modal')
@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            @if($sectionId)
                $('#create_homepage_section_id').val('{{ $sectionId }}').trigger('change');
            @endif

            $('.create-new-btn').click(function () { $('#createModal').removeClass('hidden'); });

            $('.edit-item-btn').click(function () {
                $('#editItemId').val($(this).data('item_id'));
                $('#edit_homepage_section_id').val($(this).data('homepage_section_id')).trigger('change');
                $('#edit_title').val($(this).data('title'));
                $('#edit_subtitle').val($(this).data('subtitle'));
                $('#edit_link').val($(this).data('link'));
                $('#edit_sort_order').val($(this).data('sort_order'));
                $('#edit_is_active').prop('checked', !!$(this).data('is_active'));
                $('#edit_preview').attr('src', $(this).data('image') || '');
                $('#editModal').removeClass('hidden');
            });

            $('.modal-close-create, .modal-backdrop').click(function (e) {
                if ($(e.target).closest('.modal-close-create').length || $(e.target).hasClass('modal-backdrop')) $('#createModal').addClass('hidden');
            });
            $('.modal-close-edit, .modal-backdrop').click(function (e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-edit').length) $('#editModal').addClass('hidden');
            });
            $('.modal-close-delete, .modal-backdrop').click(function (e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-delete').length) $('#deleteModal').addClass('hidden');
            });

            $('#createSubmit').click(function () {
                if (!$('#create_homepage_section_id').val()) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please select a section.' });
                    return;
                }
                let formData = new FormData($('#createForm')[0]);
                $.ajax({
                    url: $('#createForm').attr('action'), method: 'POST', data: formData, processData: false, contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            $('#createModal').addClass('hidden');
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message || 'Something went wrong.' });
                        }
                    },
                    error: function () { Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create banner.' }); }
                });
            });

            $('#editSubmit').click(function () {
                let formData = new FormData($('#editForm')[0]);
                $.ajax({
                    url: $(this).data('action'), method: 'POST', data: formData, processData: false, contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            $('#editModal').addClass('hidden');
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message || 'Update failed.' });
                        }
                    },
                    error: function () { Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong!' }); }
                });
            });

            $('#confirmDeleteBtn').click(function () {
                $.ajax({
                    url: $(this).data('action'), method: 'DELETE', data: { item_id: $(this).data('item-id') },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            $('#deleteModal').addClass('hidden');
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                        }
                    },
                    error: function () { Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong!' }); }
                });
            });
        });

        function confirmDelete(id, name) {
            $('#deleteName').text(name);
            $('#confirmDeleteBtn').data('item-id', id);
            $('#deleteModal').removeClass('hidden');
        }
    </script>
@endsection
