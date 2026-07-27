@extends('layout.app')
@section('meta-information')
    <title>Warehouses</title>
@endsection
@section('css')
<style>
    .modal { transition: opacity 0.25s ease; }
    .modal-backdrop { background-color: rgba(0, 0, 0, 0.5); }

    .states-table .states-table-container {
        background: white; border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,.1); overflow: hidden;
    }
    .states-table-header {
        background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%);
        color: white;
    }
    .states-table .states-table-content .table thead th {
        background-color: #f8f9fa; border-bottom: 2px solid #e9ecef;
        padding: 0.75rem 1rem; font-weight: 600; color: #495057;
    }
    .states-table .states-table-content .table tbody td {
        padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid #e9ecef;
    }
    .states-table .states-table-content .table tbody tr:hover { background-color: #f8f9fa; }
    .badge { font-size: .75rem; padding: .35rem .65rem; border-radius: 5px; font-weight: 500; }
    .badge-secondary { background: #6c757d; color: white; }

    .wh-input {
        width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db;
        border-radius: 6px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;
    }
    .wh-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
    .wh-label { display: block; margin-bottom: 5px; font-weight: 600; color: #374151; font-size: .875rem; }
</style>
@endsection
@section('main-content')

<div class="states-table bg-white rounded-lg shadow-md overflow-hidden" style="margin-top:0">
    <div class="states-table-container">

        {{-- Header --}}
        <div class="states-table-header px-6 py-4 flex justify-between items-center">
            <h2 class="text-white text-xl font-semibold">
                <i class="fas fa-warehouse mr-2"></i> Warehouses
            </h2>
            <button id="addWarehouseBtn"
                class="bg-white text-blue-800 hover:bg-blue-50 px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                <i class="fas fa-plus mr-1"></i> Add Warehouse
            </button>
        </div>

        {{-- Table --}}
        <div class="states-table-content" style="padding: 15px">
            <div class="table-responsive" style="overflow-x:auto">
                <table class="table w-full border-collapse" style="min-width:500px">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Stock Records</th>
                            <th style="width:130px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warehouses as $wh)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $wh->name }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $wh->code }}</span></td>
                            <td>{{ $wh->stocks_count }} SKU(s)</td>
                            <td>
                                <div class="flex gap-1">
                                    <button
                                        data-id="{{ $wh->id }}"
                                        data-name="{{ $wh->name }}"
                                        data-code="{{ $wh->code }}"
                                        class="edit-btn btn btn-outline-primary border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded-md text-sm transition duration-200"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        onclick="confirmDelete('{{ $wh->id }}', '{{ addslashes($wh->name) }}')"
                                        class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md text-sm transition duration-200"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-400">
                                <i class="fas fa-warehouse fa-2x mb-2" style="opacity:.4; display:block; margin-bottom:8px"></i>
                                No warehouses yet. Click <strong>"Add Warehouse"</strong> to create one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $warehouses->links() }}</div>
        </div>
    </div>
</div>

{{-- ===== CREATE MODAL ===== --}}
<div id="createModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="relative bg-white w-11/12 md:max-w-md mx-auto rounded-lg shadow-xl z-50 overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4" style="background:linear-gradient(90deg,#1e3a8a,#1e40af)">
            <h3 class="text-white text-lg font-semibold"><i class="fas fa-plus mr-2"></i> Add Warehouse</h3>
            <button class="modal-close text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            <div>
                <label class="wh-label">Warehouse Name <span class="text-red-500">*</span></label>
                <input type="text" id="create_name" class="wh-input" placeholder="e.g. Main Warehouse">
            </div>
            <div>
                <label class="wh-label">Code <span class="text-red-500">*</span></label>
                <input type="text" id="create_code" class="wh-input" placeholder="e.g. WH01" style="text-transform:uppercase">
                <p class="text-gray-400 text-xs mt-1">Short unique code (max 20 chars). Auto-uppercased.</p>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100">
            <button class="modal-close px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm transition">Cancel</button>
            <button id="createSaveBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm transition">Save Warehouse</button>
        </div>
    </div>
</div>

{{-- ===== EDIT MODAL ===== --}}
<div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="relative bg-white w-11/12 md:max-w-md mx-auto rounded-lg shadow-xl z-50 overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4" style="background:#f59e0b">
            <h3 class="text-white text-lg font-semibold"><i class="fas fa-edit mr-2"></i> Edit Warehouse</h3>
            <button class="modal-close text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            <input type="hidden" id="edit_id">
            <div>
                <label class="wh-label">Warehouse Name <span class="text-red-500">*</span></label>
                <input type="text" id="edit_name" class="wh-input">
            </div>
            <div>
                <label class="wh-label">Code <span class="text-red-500">*</span></label>
                <input type="text" id="edit_code" class="wh-input" style="text-transform:uppercase">
            </div>
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100">
            <button class="modal-close px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm transition">Cancel</button>
            <button id="editSaveBtn" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 text-sm transition">Update Warehouse</button>
        </div>
    </div>
</div>

{{-- ===== DELETE MODAL ===== --}}
<div id="deleteModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
    <div class="relative bg-white w-11/12 md:max-w-sm mx-auto rounded-lg shadow-xl z-50 overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 bg-red-600">
            <h3 class="text-white text-lg font-semibold"><i class="fas fa-trash mr-2"></i> Confirm Delete</h3>
            <button class="modal-close text-white hover:text-gray-200 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-5">
            <p class="text-gray-700">Are you sure you want to delete <strong id="deleteName"></strong>?</p>
            <p class="text-red-500 text-sm mt-1">This cannot be undone. Warehouses with existing stock cannot be deleted.</p>
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100">
            <button class="modal-close px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm transition">Cancel</button>
            <button id="confirmDeleteBtn"
                data-item-id=""
                data-action=""
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm transition">
                Yes, Delete
            </button>
        </div>
    </div>
</div>

@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Open create modal
            $('#addWarehouseBtn').click(function () {
                $('#create_name').val('');
                $('#create_code').val('');
                $('#createModal').removeClass('hidden');
            });

            // Close any modal via .modal-close buttons or backdrop click
            $(document).on('click', '.modal-close, .modal-backdrop', function () {
                $(this).closest('.modal').addClass('hidden');
            });

            // Open edit modal via delegated click on .edit-btn
            $(document).on('click', '.edit-btn', function () {
                $('#edit_id').val($(this).data('id'));
                $('#edit_name').val($(this).data('name'));
                $('#edit_code').val($(this).data('code'));
                $('#editModal').removeClass('hidden');
            });

            // Create warehouse
            $('#createSaveBtn').click(function () {
                var name = $('#create_name').val().trim();
                var code = $('#create_code').val().trim();

                if (!name || !code) {
                    Swal.fire({ icon: 'error', title: 'Required', text: 'Name and Code are both required.' });
                    return;
                }

                $.ajax({
                    url: '{{ route("role.warehouses.store", ["role" => Str::slug(Auth::user()->getRoleNames()->first())]) }}',
                    method: 'POST',
                    data: { name: name, code: code },
                    success: function (response) {
                        if (response.success) {
                            $('#createModal').addClass('hidden');
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong!' });
                    }
                });
            });

            // Update warehouse
            $('#editSaveBtn').click(function () {
                var id   = $('#edit_id').val();
                var name = $('#edit_name').val().trim();
                var code = $('#edit_code').val().trim();

                if (!name || !code) {
                    Swal.fire({ icon: 'error', title: 'Required', text: 'Name and Code are both required.' });
                    return;
                }

                $.ajax({
                    url: '{{ url("") }}' + '/{{ Str::slug(Auth::user()->getRoleNames()->first()) }}/warehouses/' + id,
                    method: 'POST',
                    data: { name: name, code: code, _method: 'PUT' },
                    success: function (response) {
                        if (response.success) {
                            $('#editModal').addClass('hidden');
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong!' });
                    }
                });
            });

            // Confirm delete AJAX
            $('#confirmDeleteBtn').click(function () {
                var dataId    = $(this).data('item-id');
                var deleteUrl = $(this).data('action');

                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    data: { item_id: dataId },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: 'Warehouse deleted successfully!' });
                            $('#deleteModal').addClass('hidden');
                            setTimeout(function () { window.location.reload(); }, 500);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Something went wrong!' });
                    }
                });
            });
        });

        // Triggered from table row delete button
        function confirmDelete(id, name) {
            $('#deleteName').text(name);
            $('#confirmDeleteBtn').data('item-id', id);
            $('#confirmDeleteBtn').data('action', '{{ url("") }}' + '/{{ Str::slug(Auth::user()->getRoleNames()->first()) }}/warehouses/' + id);
            $('#deleteModal').removeClass('hidden');
        }
    </script>
@endsection
