@extends('layout.app')
@section('meta-information')
    <title>States Management</title>
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
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        background: #f4f4f4;
        color: #764ba2;
    }
    
    .admin-stats-grid .admin-stat-card.success {
        /* background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); */
        background: #f4f4f4;
        color: #3aa31f;
    }
    
    .admin-stats-grid .admin-stat-card.warning {
        /* background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); */
        background: #f4f4f4;
        color: #f5576c;
    }
    
    .admin-stats-grid .admin-stat-card.info {
        /* background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%); */
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
        color: white
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
</style>
<style>
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
    /* Example: change active page background and text */
    span [aria-current="page"] span{
        background-color: #2563eb !important;
        background: #2563eb !important;
        color: white;
        border-color: #2563eb;
    }
</style>
@endsection
@section('main-content')
    <!-- Stats Overview -->
    <div class="admin-stats-grid">
        <div class="admin-stat-card primary">
            <div class="position-relative">
                <div class="admin-stat-value">{{ $totalStates }}</div>
                <div class="admin-stat-label">Total States</div>
                <div class="admin-stat-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
            </div>
        </div>

        <div class="admin-stat-card success">
            <div class="position-relative">
                <div class="admin-stat-value">{{ $activeStates }}</div>
                <div class="admin-stat-label">Active States</div>
                <div class="admin-stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="admin-stat-card warning">
            <div class="position-relative">
                <div class="admin-stat-value">{{ $inactiveStates }}</div>
                <div class="admin-stat-label">Inactive States</div>
                <div class="admin-stat-icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
            </div>
        </div>

        <div class="admin-stat-card info">
            <div class="position-relative">
                <div class="admin-stat-value">{{ $countriesCount }}</div>
                <div class="admin-stat-label">Countries</div>
                <div class="admin-stat-icon">
                    <i class="fas fa-globe"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- States Table -->    
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-list mr-2"></i>States List
                </h2>
                <button class="btn btn-primary create-state-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New State
                </button>
            </div>

            <div class="states-table-content">
                <!-- Success Alert -->
                <form action="" method="get">
                    <div class="filter-container">
                        <div class="filter-header">
                            <h3><i class="fas fa-filter mr-2"></i>Filter Options</h3>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label for="country_id">Country</label>
                                    <select id="country_id" name="country_id" class="form-control select2" style="width: 100%">
                                        <option value="">All Countries</option>
                                        @foreach ($countries as $country)
                                            <option value="{{ $country->id }}" {{ $country->id == request('country_id') ? 'selected' : '' }}>{{ $country->name }}</option>                                            
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="state_name">State</label>
                                    <input type="text" name="state_name" id="state_name" value="{{ request('state_name') }}" placeholder="Type State Name...">
                                </div>
                                <div class="filter-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" class="form-control select2" style="width: 100%">
                                        <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All Statuses</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>                                    
                                </div>
                            </div>
                            <div class="filter-actions">
                                <button type="button" class="reset-btn">Reset</button>
                                <button type="submit" class="apply-btn">Apply Filters</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Table with Data -->
                @if ($totalStates == 0)
                <div class="text-center pt-4">
                    <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                    <h4 class="text-gray-500 text-xl font-medium mb-2">No states found</h4>
                    <p class="text-gray-400 mb-4">Get started by adding your first state.</p>
                    <button class="btn btn-primary create-state-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Add New State
                    </button>
                </div>                    
                @else                    
                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th style="width: 10%; padding-left: 20px" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                                <th style="width: 25%" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                                <th style="width: 25%" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                                <th style="width: 10%" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th style="width: 15%" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th style="width: 15%" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($states as $key => $state)                       
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: 20px">
                                        <strong>{{ ($states->currentPage() - 1) * $states->perPage() + $key + 1 }}</strong>
                                    </td>                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $state->country?->name }}                                                                                                                        
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $state->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($state->status)
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
                                        <small class="text-gray-500">{{ date('M d, Y', strtotime($state->created_at)) }}</small>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="btn-group btn-group-sm flex space-x-1">
                                            <button class="btn btn-outline-primary edit-state-btn border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded-md transition duration-200" data-country_id="{{ $state->country_id }}" data-country_name="{{ $state->country?->name }}" data-state_name="{{ $state->name }}" data-state_id="{{ $state->id }}" data-state_status="{{ $state->status }}" data-action="{{ route('role.states.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'state' => $state->id]) }}" title="Edit State">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200" onclick="confirmDelete('{{ $state->id }}', '{{ $state->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                                    <h4 class="text-gray-500 text-xl font-medium mb-2">No states found</h4>
                                    <p class="text-gray-400 mb-4">Try filtering with different datas.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Pagination -->
                <div class="p-3 border-t border-gray-200">                    
                    {{ $states->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>
    
    @include('states.create-modal')
    @include('states.edit-modal')
    @include('states.delete-modal')

@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            // initialized select2
            $('.select2').select2();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Show create modal
            $('.create-state-btn').click(function() {
                $('#createStateModal').removeClass('hidden');
            });

            // Show edit modal
            $('.edit-state-btn').click(function() {
                const stateId = $(this).data('state_id');
                const stateName = $(this).data('state_name');
                const countryId = $(this).data('country_id');
                const countryName = $(this).data('country_name');
                const formAction = $(this).data('action');
                const isActive = $(this).data('state_status');                                                
                
                // Set values in the edit form
                $('#editStateId').val(stateId);
                $('#editStateName').val(stateName);
                $('#editCountry').val(countryId).trigger('change');
                $('#editStatus').prop('checked', isActive);         
                $('#updateFormAction').val(formAction);       
                $('#editStateModal').removeClass('hidden');
            });

            // Close modals
            $('.modal-close-create, .modal-backdrop').click(function(e) {
                if ($(e.target).closest('.modal-close-create').length || $(e.target).hasClass('modal-backdrop')) {
                    $('#createStateModal').addClass('hidden');
                }
            });

            $('.modal-close-edit, .modal-backdrop').click(function(e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-edit').length) {
                    $('#editStateModal').addClass('hidden');
                }
            });

            $('.modal-close-delete, .modal-backdrop').click(function(e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-delete').length) {
                    $('#deleteStateModal').addClass('hidden');
                }
            });

            // Close success alert
            $('.close-btn').click(function() {
                $(this).closest('.alert').addClass('hidden');
            });

            // Create state form submission
            $('#createStateSubmit').click(function(e) {
                e.preventDefault();
                console.log(validateCreateForm());                
                if (validateCreateForm()) {
                    $.ajax({
                        url: $('#createStateForm').attr('action'),
                        method: 'POST',
                        data: {
                            country_id: $('#create_country_id').val(),
                            name: $('#create_stateName').val(),
                            state_status: $('#create_status').is(':checked') ? 1 : 0,
                        },
                        success: function (response) {
                            console.log(response);
                            if (response.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Done",
                                    text: "State created successfully!",
                                });
                                $('#createStateModal').addClass('hidden');
                                resetCreateForm();
                                console.log('trigger reload');                                
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Opps...",
                                    text: response.message,
                                });
                            }
                        },
                        error: function (xhr) {
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

            // Edit state form submission
            $('#editStateSubmit').click(function() {
                if (validateEditForm()) {
                    $.ajax({
                        url: $('#updateFormAction').val(),
                        method: 'PUT',
                        data: {
                            id: $('#editStateId').val(),
                            name: $('#editStateName').val(),
                            country_id: $('#editCountry').val(),
                            state_status: $('#editStatus').is(':checked') ? 1 : 0,
                        },
                        success: function (response) {
                            console.log(response);
                            if (response.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Done",
                                    text: "State updated successfully!",
                                });
                                $('#editStateModal').addClass('hidden');                                
                                console.log('trigger reload');                                
                                setTimeout(() => {
                                    window.location.reload();
                                }, 500);
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Opps...",
                                    text: response.message,
                                });
                            }
                        },
                        error: function (xhr) {
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

            // Delete confirmation
            $('#confirmDeleteBtn').click(function() {
                const stateId = $(this).data('state-id');
                const deleteUrl = $(this).data('action');
                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    data: {
                        state_id: stateId,
                    },
                    success: function (response) {
                        console.log(response);
                        if (response.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Done",
                                text: "State deleted successfully!",
                            });
                            $('#deleteStateModal').addClass('hidden');
                            console.log('trigger reload');                                
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Opps...",
                                text: response.message,
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error('❌ Error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong!'
                        });
                    }
                });  
            });
        });

        // Form validation functions
        function validateCreateForm() {
            let isValid = true;
            
            // Reset error messages
            $('#createStateForm .error-message').addClass('hidden');
            $('#createStateForm .form-select, #createStateForm .form-input').removeClass('border-red-500');
            
            // Validate country
            if (!$('#create_country_id').val()) {
                $('#create_country_id').next('.error-message').removeClass('hidden');
                $('#create_country_id').addClass('border-red-500');
                isValid = false;
            }
            
            // Validate state name
            if (!$('#create_stateName').val().trim()) {
                $('#create_stateName').next('.error-message').removeClass('hidden');
                $('#create_stateName').addClass('border-red-500');
                isValid = false;
            }
            
            return isValid;
        }

        function validateEditForm() {
            let isValid = true;
            
            // Reset error messages
            $('#editStateForm .error-message').addClass('hidden');
            $('#editStateForm .form-select, #editStateForm .form-input').removeClass('border-red-500');
            
            // Validate country
            if (!$('#editCountry').val()) {
                $('#editCountry').next('.error-message').removeClass('hidden');
                $('#editCountry').addClass('border-red-500');
                isValid = false;
            }
            
            // Validate state name
            if (!$('#editStateName').val().trim()) {
                $('#editStateName').next('.error-message').removeClass('hidden');
                $('#editStateName').addClass('border-red-500');
                isValid = false;
            }
            
            return isValid;
        }

        // Reset create form
        function resetCreateForm() {
            $('#createStateForm')[0].reset();
            $('#createStateForm .error-message').addClass('hidden');
            $('#createStateForm .form-select, #createStateForm .form-input').removeClass('border-red-500');
        }

        // Delete confirmation
        function confirmDelete(id, name) {
            $('#deleteStateName').text(name);
            $('#confirmDeleteBtn').data('state-id', id);
            $('#deleteStateModal').removeClass('hidden');
        }

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterHeader = document.querySelector('.filter-container .filter-header');
            const filterContent = document.querySelector('.filter-container .filter-content');
            
            filterHeader.addEventListener('click', function() {
                this.classList.toggle('active');
                filterContent.classList.toggle('active');
            });
            
            // Reset button functionality
            document.querySelector('.filter-container .reset-btn').addEventListener('click', function() {
                const inputs = document.querySelectorAll('.filter-container select, .filter-container input');
                inputs.forEach(input => {
                    if (input.type === 'date') {
                        input.value = '';
                    } else {
                        input.selectedIndex = 0;
                    }
                });
            });
            document.querySelector('.reset-btn').addEventListener('click', function (e) {
                e.preventDefault();
                window.location = '{{ route('role.states.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}';
            });
        });
    </script>
@endsection