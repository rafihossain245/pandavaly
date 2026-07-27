@extends('layout.app')

@section('meta-information')
    <title>Manage Buyers</title>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .modal {
            transition: opacity 0.25s ease;
        }

        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .admin-stats-grid .admin-stat-card {
            border-radius: 6px;
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .admin-stats-grid .admin-stat-card.primary {
            background: #f4f4f4;
            color: #764ba2;
        }

        .admin-stats-grid .admin-stat-card.success {
            background: #f4f4f4;
            color: #3aa31f;
        }

        .admin-stats-grid .admin-stat-card.warning {
            background: #f4f4f4;
            color: #f5576c;
        }

        .admin-stats-grid .admin-stat-card.info {
            background: #f4f4f4;
            color: #129fa7;
        }

        .admin-stats-grid .admin-stat-card .position-relative {
            position: relative;
        }

        .admin-stats-grid .admin-stat-card .admin-stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .admin-stats-grid .admin-stat-card .admin-stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .admin-stats-grid .admin-stat-card .admin-stat-icon {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 1.5rem;
            opacity: 0.7;
        }

        .states-table {
            margin-top: 2rem;
        }

        .states-table .states-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .states-table .states-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
        }

        .states-table .states-table-header .states-table-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }

        .states-table .states-table-header .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .states-table .states-table-content {
            padding: 0;
        }

        .states-table .states-table-content .alert {
            margin: 1rem;
            border-radius: 8px;
            border: none;
        }

        .states-table .states-table-content .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .states-table .states-table-content .text-center {
            padding: 3rem 1rem;
        }

        .states-table .states-table-content .text-center .fa-inbox {
            opacity: 0.5;
        }

        .states-table .states-table-content .table-responsive {
            overflow-x: auto;
        }

        .states-table .states-table-content .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .states-table .states-table-content .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            padding: 1rem 0.75rem;
            font-weight: 600;
            color: #495057;
        }

        .states-table .states-table-content .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        .states-table .states-table-content .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .states-table .states-table-content .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .states-table .states-table-content .badge.bg-light {
            color: #6c757d !important;
            background-color: #f8f9fa !important;
        }

        .states-table .states-table-content .badge.bg-info {
            background-color: #17a2b8 !important;
        }

        .states-table .states-table-content .badge.bg-success {
            background-color: #28a745 !important;
        }

        .states-table .states-table-content .badge.bg-secondary {
            background-color: #6c757d !important;
        }

        .states-table .states-table-content .badge.bg-warning {
            background-color: orange !important;
        }

        .states-table-header {
            background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%);
            color: white;
        }

        .states-table .states-table-content .btn-group {
            border-radius: 6px;
            overflow: hidden;
        }

        .states-table .states-table-content .btn-group .btn {
            border-radius: 0;
            padding: 0.375rem 0.75rem;
        }

        .states-table .states-table-content .btn-group .btn:first-child {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
        }

        .states-table .states-table-content .btn-group .btn:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        .states-table .states-table-content .pagination {
            margin-bottom: 0;
            padding: 1rem;
        }

        .states-table .states-table-content .pagination .page-link {
            border-radius: 6px;
            margin: 0 0.2rem;
            border: 1px solid #dee2e6;
            color: #007bff;
        }

        .states-table .states-table-content .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        @media (max-width: 768px) {
            .admin-stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .states-table .states-table-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .states-table .states-table-header .btn {
                width: 100%;
            }
        }

        .filter-container {
            margin: 15px 15px 0 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filter-container .filter-header {
            background-color: #f8f9fa;
            padding: 16px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #3b82f6;
            transition: background-color 0.3s;
        }

        .filter-container .filter-header:hover {
            background-color: #e9ecef;
        }

        .filter-container .filter-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
        }

        .filter-container .filter-header .toggle-icon {
            transition: transform 0.3s;
        }

        .filter-container .filter-header.active .toggle-icon {
            transform: rotate(180deg);
        }

        .filter-container .filter-content {
            background-color: white;
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease-out;
        }

        .filter-container .filter-content.active {
            padding: 20px;
            max-height: 500px;
        }

        .filter-container .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 16px;
        }

        .filter-container .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-container .filter-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
        }

        .filter-container .filter-group select,
        .filter-container .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .filter-container .filter-group select:focus,
        .filter-container .filter-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter-container .filter-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .filter-container .filter-actions button {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-container .filter-actions .apply-btn {
            background-color: #3b82f6;
            color: white;
            border: none;
        }

        .filter-container .filter-actions .apply-btn:hover {
            background-color: #2563eb;
        }

        .filter-container .filter-actions .reset-btn {
            background-color: #f8f9fa;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .filter-container .filter-actions .reset-btn:hover {
            background-color: #e5e7eb;
        }

        .select2-container .select2-selection--single {
            height: 42px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            position: absolute;
            top: 1px;
            right: 3px;
            width: 20px;
        }

        span [aria-current="page"] span {
            background-color: #2563eb !important;
            background: #2563eb !important;
            color: white;
            border-color: #2563eb;
        }
    </style>
@endsection

@section('main-content')

    {{-- EDIT MODAL --}}
    <div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded shadow-lg z-50">
            <div class="modal-content flex flex-col py-4 text-left px-6">
                <div class="modal-header flex justify-between items-center pb-2 border-b-2 border-gray-200 w-full">
                    <h3 class="text-xl font-semibold">Edit Buyer</h3>
                    <button class="modal-close-edit z-50">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body overflow-y-auto mt-2" style="max-height: calc(90vh - 120px); scrollbar-width: thin;">
                    <form id="editForm" method="POST" action="">
                        @csrf
                        @method('PUT')

                        <input type="hidden" id="edit_buyer_id" name="buyer_id">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                            <div class="mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Business Name</label>
                                <input type="text" id="edit_business_name" name="business_name"
                                    class="form-input w-full border rounded-md px-3 py-2"
                                    placeholder="Enter Business Name">
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                                <input type="text" id="edit_category" name="category"
                                    class="form-input w-full border rounded-md px-3 py-2"
                                    placeholder="Enter Category">
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                <input type="email" id="edit_email" name="email"
                                    class="form-input w-full border rounded-md px-3 py-2"
                                    placeholder="Enter Email">
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Phone</label>
                                <input type="text" id="edit_phone" name="phone"
                                    class="form-input w-full border rounded-md px-3 py-2"
                                    placeholder="Enter Phone">
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tin</label>
                                <input type="text" id="edit_tin" name="tin"
                                    class="form-input w-full border rounded-md px-3 py-2"
                                    placeholder="Enter Tin No">
                            </div>
                            <div class="mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Trade License No</label>
                                <input type="text" id="edit_trade_license_no" name="trade_license_no"
                                    class="form-input w-full border rounded-md px-3 py-2"
                                    placeholder="Enter Trade License No">
                            </div>
                            <div class="mb-2 md:col-span-3">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Status</label>
                                <select name="status" id="edit_status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2"
                                    style="width: 100%">
                                    <option disabled selected>Select Status</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="blacklisted">Blacklisted</option>
                                </select>
                            </div>
                        </div>

                        {{-- Contact Persons Section --}}
                        <div class="mt-4 border-t pt-3">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-md font-semibold text-gray-700">Contact Persons</h4>
                                <button type="button" id="addEditContactRow"
                                    class="bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">
                                    + Add Contact
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-300 rounded-md">
                                    <thead class="bg-gray-100 text-sm text-gray-700">
                                        <tr>
                                            <th class="p-2 border">Name</th>
                                            <th class="p-2 border">Email</th>
                                            <th class="p-2 border">Phone</th>
                                            <th class="p-2 border">Designation</th>
                                            <th class="p-2 border text-center">Primary</th>
                                            <th class="p-2 border text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editContactTableBody">
                                        {{-- Filled dynamically by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer flex justify-end pt-2">
                    <button type="button"
                        class="modal-close-edit px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 mr-2">Cancel</button>
                    <button id="editSubmit" type="button"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN LIST --}}
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-list mr-2"></i>Buyers List
                </h2>
                <button
                    class="btn btn-primary create-new-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New Buyer
                </button>
            </div>

            <div class="states-table-content">
                <form action="" method="get">
                    <div class="filter-container">
                        <div class="filter-header">
                            <h3><i class="fas fa-filter mr-2"></i>Filter Options</h3>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="filter-content">
                            <div class="closest filter-row">
                                <div class="filter-group">
                                    <label for="is_active">Status</label>
                                    <select id="is_active" name="is_active" class="form-control select2"
                                        style="width: 100%">
                                        <option value="">All</option>
                                        <option value="1" @if(request('is_active')==='1') selected @endif>Active</option>
                                        <option value="0" @if(request('is_active')==='0') selected @endif>Inactive</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="name">Business Name</label>
                                    <input type="text" name="business_name" value="{{ request('business_name') }}"
                                        id="name" class="form-control" placeholder="Enter Name">
                                </div>
                                <div class="filter-group">
                                    <label for="email">Email</label>
                                    <input type="text" name="email" value="{{ request('email') }}" id="email"
                                        class="form-control" placeholder="Enter Email">
                                </div>
                                <div class="filter-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" name="phone" value="{{ request('phone') }}" id="phone"
                                        class="form-control" placeholder="Enter phone">
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="button" class="reset-btn"
                                    onclick="window.location='{{ url()->current() }}'">Reset</button>
                                <button type="submit" class="apply-btn">Apply Filters</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th style="padding-left: 20px"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Business Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tin No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($datas as $key => $value)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: 20px">
                                        <strong>{{ ($datas->currentPage() - 1) * $datas->perPage() + $key + 1 }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->business_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->phone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->tin }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($value->is_active)
                                            <span class="badge text-white bg-green-500 px-2 py-1 rounded-full text-xs">
                                                Active
                                            </span>
                                        @else
                                            <span class="badge text-white bg-yellow-500 px-2 py-1 rounded-full text-xs">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="btn-group btn-group-sm flex space-x-1">
                                            <button type="button"
                                                class="edit-item-btn px-2 py-1 bg-blue-500 text-white rounded text-xs"
                                                data-item_id="{{ $value->id }}"
                                                data-business_name="{{ $value->business_name }}"
                                                data-category="{{ $value->category }}"
                                                data-email="{{ $value->email }}"
                                                data-phone="{{ $value->phone }}"
                                                data-tin="{{ $value->tin }}"
                                                data-trade_license_no="{{ $value->trade_license_no }}"
                                                data-status="{{ $value->status }}">
                                                Edit
                                            </button>

                                            <button
                                                class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200"
                                                onclick="confirmDelete('{{ $value->id }}', 'this item')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-8">
                                        <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                                        <h4 class="text-gray-500 text-xl font-medium mb-2">No data found</h4>
                                        <p class="text-gray-400 mb-4">Try filtering with different datas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-t border-gray-200">
                    {{ $datas->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- If you still have these partials, keep them --}}
    @include('buyers.create-modal')
    @include('buyers.delete-modal')
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {

            // =====================
            // Global setup
            // =====================
            $('.select2').select2();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // =====================
            // Filter toggle
            // =====================
            $('.filter-header').on('click', function() {
                $(this).toggleClass('active');
                $(this).next('.filter-content').toggleClass('active');
            });

            // =====================
            // Modal: Open/Close
            // =====================

            // Show create modal
            $('.create-new-btn').click(function() {
                resetCreateForm();
                $('#createModal').removeClass('hidden');
            });

            // Close create modal
            $('.modal-close-create, .modal-backdrop').click(function(e) {
                if ($(e.target).closest('.modal-close-create').length || $(e.target).hasClass('modal-backdrop')) {
                    $('#createModal').addClass('hidden');
                }
            });

            // Close edit modal
            $(document).on('click', '.modal-close-edit, .modal-backdrop', function(e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-edit').length) {
                    $('#editModal').addClass('hidden');
                }
            });

            // Close delete modal
            $('.modal-close-delete, .modal-backdrop').click(function(e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-delete').length) {
                    $('#deleteModal').addClass('hidden');
                }
            });

            // =====================
            // CREATE – Contact Persons dynamic rows
            // =====================
            let contactIndex = $('#contactTableBody tr').length || 1;

            $('#addContactRow').on('click', function() {
                let row = `
                <tr>
                    <td class="border p-2">
                        <input type="text" name="contacts[${contactIndex}][name]"
                               class="w-full border rounded-md px-2 py-1"
                               placeholder="Contact Name">
                    </td>
                    <td class="border p-2">
                        <input type="email" name="contacts[${contactIndex}][email]"
                               class="w-full border rounded-md px-2 py-1"
                               placeholder="Email">
                    </td>
                    <td class="border p-2">
                        <input type="text" name="contacts[${contactIndex}][phone]"
                               class="w-full border rounded-md px-2 py-1"
                               placeholder="Phone">
                    </td>
                    <td class="border p-2">
                        <input type="text" name="contacts[${contactIndex}][designation]"
                               class="w-full border rounded-md px-2 py-1"
                               placeholder="Designation">
                    </td>
                    <td class="border p-2 text-center">
                        <input type="radio" name="primary_contact_index" value="${contactIndex}">
                    </td>
                    <td class="border p-2 text-center">
                        <button type="button"
                                class="removeContactRow bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                            X
                        </button>
                    </td>
                </tr>
            `;
                $('#contactTableBody').append(row);
                contactIndex++;
            });

            $(document).on('click', '.removeContactRow', function() {
                $(this).closest('tr').remove();
            });

            // =====================
            // CREATE – AJAX submit
            // =====================
            $('#createSubmit').click(function(e) {
                e.preventDefault();

                if (validateCreateForm()) {
                    let formData = new FormData($('#createForm')[0]);

                    $.ajax({
                        url: $('#createForm').attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Done',
                                    text: 'Data created successfully!'
                                });
                                $('#createModal').addClass('hidden');
                                resetCreateForm();
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message || 'Something went wrong.'
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to create data.'
                            });
                        }
                    });
                }
            });

            // =====================
            // EDIT – open modal and load data
            // =====================
            $(document).on('click', '.edit-item-btn', function() {
                let buyerId = $(this).data('item_id');
                let business_name = $(this).data('business_name');
                let category = $(this).data('category');
                let email = $(this).data('email');
                let phone = $(this).data('phone');
                let tin = $(this).data('tin');
                let trade_license_no = $(this).data('trade_license_no');
                let status = $(this).data('status');

                // Fill buyer info
                $('#edit_buyer_id').val(buyerId);
                $('#edit_business_name').val(business_name);
                $('#edit_category').val(category);
                $('#edit_email').val(email);
                $('#edit_phone').val(phone);
                $('#edit_tin').val(tin);
                $('#edit_trade_license_no').val(trade_license_no);

                if (status) {
                    $('#edit_status').val(status).trigger('change');
                } else {
                    $('#edit_status').val('').trigger('change');
                }

                // Set update URL
                let updateUrl =
                    "{{ route('role.buyers.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'buyer' => '__id__']) }}";
                updateUrl = updateUrl.replace('__id__', buyerId);
                $('#editForm').attr('action', updateUrl);

                // Load contacts via AJAX
                let $tbody = $('#editContactTableBody');
                $tbody.empty();

                let contactsUrl =
                    "{{ route('role.buyers.contacts.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'id' => '__id__']) }}";
                contactsUrl = contactsUrl.replace('__id__', buyerId);

                $.ajax({
                    url: contactsUrl,
                    type: 'GET',
                    success: function(res) {
                        // You can return either {success: true, data: [...]}
                        // or just an array. Handle both:
                        let contacts = [];

                        if (Array.isArray(res)) {
                            contacts = res;
                        } else if (res.success && Array.isArray(res.data)) {
                            contacts = res.data;
                        }

                        contacts.forEach(function(contact, index) {
                            let row = `
                                <tr>
                                    <td class="border p-2">
                                        <input type="hidden" name="contacts[${index}][id]" value="${contact.id ?? ''}">
                                        <input type="text" name="contacts[${index}][name]" class="w-full border px-2 py-1"
                                            value="${contact.name ?? ''}">
                                    </td>
                                    <td class="border p-2">
                                        <input type="email" name="contacts[${index}][email]" class="w-full border px-2 py-1"
                                            value="${contact.email ?? ''}">
                                    </td>
                                    <td class="border p-2">
                                        <input type="text" name="contacts[${index}][phone]" class="w-full border px-2 py-1"
                                            value="${contact.phone ?? ''}">
                                    </td>
                                    <td class="border p-2">
                                        <input type="text" name="contacts[${index}][designation]" class="w-full border px-2 py-1"
                                            value="${contact.designation ?? ''}">
                                    </td>
                                    <td class="border p-2 text-center">
                                        <input type="radio" name="contacts[${index}][primary_contact_index]" value="${contact.is_primary ? 1 : 0}"
                                            ${contact.is_primary ? 'checked' : ''}>
                                    </td>
                                    <td class="border p-2 text-center">
                                        <button type="button" class="removeEditRow text-red-600 text-sm">Remove</button>
                                    </td>
                                </tr>
                            `;
                            $tbody.append(row);
                        });
                    },
                    error: function(xhr) {
                        console.error('Could not load buyer contacts.', xhr.responseText);
                    }
                });

                $('#editModal').removeClass('hidden');
            });

            // =====================
            // EDIT – Add new contact row
            // =====================
            $(document).on('click', '#addEditContactRow', function() {
                let index = $('#editContactTableBody tr').length;

                let newRow = `
                    <tr>
                        <td class="border p-2">
                            <input type="hidden" name="contacts[${index}][id]" value="">
                            <input type="text" name="contacts[${index}][name]" class="w-full border px-2 py-1" placeholder="Enter Name">
                        </td>
                        <td class="border p-2">
                            <input type="email" name="contacts[${index}][email]" class="w-full border px-2 py-1" placeholder="Enter Email">
                        </td>
                        <td class="border p-2">
                            <input type="text" name="contacts[${index}][phone]" class="w-full border px-2 py-1" placeholder="Enter Phone">
                        </td>
                        <td class="border p-2">
                            <input type="text" name="contacts[${index}][designation]" class="w-full border px-2 py-1" placeholder="Enter Designation">
                        </td>
                        <td class="border p-2 text-center">
                            <input type="radio" name="primary_contact_index" value="new_${index}">
                        </td>
                        <td class="border p-2 text-center">
                            <button type="button" class="removeEditRow text-red-600 text-sm">Remove</button>
                        </td>
                    </tr>
                `;

                $('#editContactTableBody').append(newRow);
            });

            // Remove contact row in edit form
            $(document).on('click', '.removeEditRow', function() {
                $(this).closest('tr').remove();
            });

            // =====================
            // EDIT – AJAX submit
            // =====================
            $('#editSubmit').click(function() {
                if (validateEditForm()) {
                    let formData = new FormData($('#editForm')[0]);

                    $.ajax({
                        url: $('#editForm').attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Done",
                                    text: "Data updated successfully!",
                                });
                                $('#editModal').addClass('hidden');
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Oops...",
                                    text: response.message || "Update failed.",
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('❌ Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong!'
                            });
                        }
                    });
                }
            });

            // =====================
            // DELETE – AJAX
            // =====================
            $('#confirmDeleteBtn').click(function() {
                const dataId = $(this).data('item-id');

                // Build the route URL dynamically with the correct buyer id
                let deleteUrl = "{{ route('role.buyers.destroy', [
                    'role'  => Str::slug(Auth::user()->getRoleNames()->first()),
                    'buyer' => '__id__'
                ]) }}";

                deleteUrl = deleteUrl.replace('__id__', dataId);

                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    data: {
                        item_id: dataId,
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Done",
                                text: "Data deleted successfully!",
                            });
                            $('#deleteModal').addClass('hidden');
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Opps...",
                                text: response.message,
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('❌ Error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong!'
                        });
                    }
                });
            });

        }); // end document.ready

        // ==========================
        // Validation
        // ==========================
        function validateCreateForm() {
            let isValid = true;

            $('#createForm .error-message').addClass('hidden');
            $('#createForm .form-select, #createForm .form-input').removeClass('border-red-500');

            const $businessName = $('#business_name');
            if ($businessName.length && !$businessName.val().trim()) {
                $businessName.addClass('border-red-500');
                $businessName.next('.error-message').removeClass('hidden');
                isValid = false;
            }

            return isValid;
        }

        function validateEditForm() {
            let isValid = true;

            $('#editForm .error-message').addClass('hidden');
            $('#editForm .form-select, #editForm .form-input').removeClass('border-red-500');

            const $businessName = $('#edit_business_name');
            if ($businessName.length && !$businessName.val().trim()) {
                $businessName.addClass('border-red-500');
                $businessName.next('.error-message').removeClass('hidden');
                isValid = false;
            }

            return isValid;
        }

        // ==========================
        // Reset + Delete helper
        // ==========================
        function resetCreateForm() {
            if ($('#createForm').length) {
                $('#createForm')[0].reset();
                $('#createForm .error-message').addClass('hidden');
                $('#createForm .form-select, #createForm .form-input').removeClass('border-red-500');
                $('#contactTableBody').empty();
            }
        }

        function confirmDelete(id, name = null) {
            $('#deleteName').text(name || '');
            $('#confirmDeleteBtn').data('item-id', id);
            $('#deleteModal').removeClass('hidden');
        }
    </script>
@endsection
