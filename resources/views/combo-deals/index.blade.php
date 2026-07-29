@extends('layout.app')
@section('meta-information')
    <title>Manage Combo Deals</title>
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
    .states-table .states-table-content .table-responsive { overflow-x: auto; }
    .states-table .states-table-content .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 1rem 0.75rem; font-weight: 600; color: #495057; }
    .states-table .states-table-content .table tbody td { padding: 1rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #e9ecef; }
    .states-table-header { background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%); color: white }
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
                    <i class="fas fa-gift mr-2"></i>Combo Deals
                </h2>
                <button class="btn btn-primary create-new-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New Combo
                </button>
            </div>
            <div class="states-table-content">
                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th style="padding-left: 20px" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($datas as $key => $value)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: 20px"><strong>{{ ($datas->currentPage() - 1) * $datas->perPage() + $key + 1 }}</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($value->image)
                                            <img src="{{ asset($value->image) }}" alt="" width="60">
                                        @else
                                            <span class="text-gray-400 text-xs">No image</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->products_count }} product(s)</td>
                                    <td class="px-6 py-4 whitespace-nowrap">৳{{ number_format($value->price, 2) }}</td>
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
                                                data-name="{{ $value->name }}"
                                                data-description="{{ $value->description }}"
                                                data-price="{{ $value->price }}"
                                                data-product_ids="{{ $value->products->pluck('id')->implode(',') }}"
                                                data-starts_at="{{ optional($value->starts_at)->format('Y-m-d\TH:i') }}"
                                                data-ends_at="{{ optional($value->ends_at)->format('Y-m-d\TH:i') }}"
                                                data-is_active="{{ (int) $value->is_active }}"
                                                title="Edit Combo">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200" onclick="confirmDelete('{{ $value->id }}', '{{ $value->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8">
                                        <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                                        <h4 class="text-gray-500 text-xl font-medium mb-2">No combo deals yet</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t border-gray-200">{{ $datas->links() }}</div>
            </div>
        </div>
    </div>

    @include('combo-deals.create-modal')
    @include('combo-deals.edit-modal')
    @include('combo-deals.delete-modal')
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

            $('.create-new-btn').click(function () {
                $('#createForm')[0].reset();
                $('#create_product_ids').val(null).trigger('change');
                $('#createModal').removeClass('hidden');
            });

            $('.edit-item-btn').click(function () {
                $('#editItemId').val($(this).data('item_id'));
                $('#edit_name').val($(this).data('name'));
                $('#edit_description').val($(this).data('description'));
                $('#edit_price').val($(this).data('price'));
                const productIds = ($(this).data('product_ids') || '').toString().split(',').filter(Boolean).map(Number);
                $('#edit_product_ids').val(productIds).trigger('change');
                $('#edit_starts_at').val($(this).data('starts_at'));
                $('#edit_ends_at').val($(this).data('ends_at'));
                $('#edit_is_active').prop('checked', !!$(this).data('is_active'));
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
                if (!$('#create_name').val().trim() || !$('#create_price').val() || $('#create_product_ids').val().length < 2) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Name, price and at least 2 products are required.' });
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
                    error: function () { Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to create combo deal.' }); }
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
