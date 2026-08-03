@extends('layout.app')
@section('meta-information')
    <title>Add New Sale</title>
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

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
            padding: 10px 20px;
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

        .ck-editor__editable {
            min-height: 120px;
            resize: vertical;
            /* allow vertical drag */
            overflow: auto;
            /* needed for resize */
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
        span [aria-current="page"] span {
            background-color: #2563eb !important;
            background: #2563eb !important;
            color: white;
            border-color: #2563eb;
        }
    </style>
    <style>
        .sale-modal-body {
            font-family: 'Inter', sans-serif;
        }

        .sale-modal-body .form-input,
        .sale-modal-body .form-select {
            transition: all 0.3s ease;
        }

        .sale-modal-body .form-input:focus,
        .sale-modal-body .form-select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .sale-modal-body .product-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sale-modal-body .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-left-color: #3b82f6;
        }

        .sale-modal-body .calculation-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .sale-modal-body .amount-highlight {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.1rem;
        }

        .sale-modal-body .payment-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .sale-modal-body .payment-status-paid {
            background-color: #dcfce7;
            color: #166534;
        }

        .sale-modal-body .payment-status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .sale-modal-body .payment-status-partial {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .sale-modal-body .add-product-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            transition: all 0.3s ease;
        }

        .sale-modal-body .add-product-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .sale-modal-body .remove-product-btn {
            transition: all 0.2s ease;
        }

        .sale-modal-body .remove-product-btn:hover {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .sale-modal-body .note-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .sale-modal-body .section-divider {
            border-bottom: 3px solid #e8e8e8;
            width: 100%;
            margin: 1.5rem 0;
        }
    </style>
@endsection
@section('main-content')
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden" style="margin-top: 0">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 pb-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-plus mr-2"></i> Add New Product
                </h2>
                <a href="{{ route('role.sales.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                    class="btn btn-primary create-new-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-list mr-2"></i> Products List
                </a>
            </div>

            <div class="states-table-content" style="padding: 15px;">
                <div class="sale-modal-body modal-body overflow-y-auto mt-2">
                    <form class="closest" id="createForm"
                        action="{{ route('role.products.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- The detail sits under each field; this only tells the admin where to look
                             when the failing field is scrolled out of view. --}}
                        @if ($errors->any())
                            <div id="form_error_summary"
                                class="mt-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <strong>{{ $errors->count() }}
                                    {{ Str::plural('field', $errors->count()) }}</strong>
                                need{{ $errors->count() === 1 ? 's' : '' }} your attention. The details are shown
                                in red under each one.
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                            <div class="mb-2">
                                <label for="create_category_id"
                                    class="block text-gray-700 text-sm font-bold mb-2">Category</label>
                                <select id="create_category_id" name="category_id"
                                    onchange="getSubCategory(this, '#create_sub_category_id')"
                                    data-action="{{ route('role.get-sub-category', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                                    class="select2 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2 @error('category_id') border-red-500 @else border-gray-300 @enderror"
                                    style="width: 100%">
                                    <option value="">All</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_sub_category_id" class="block text-gray-700 text-sm font-bold mb-2">Sub
                                    Category</label>
                                <select id="create_sub_category_id" name="sub_category_id"
                                    data-selected="{{ old('sub_category_id') }}"
                                    class="select2 w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2 @error('sub_category_id') border-red-500 @else border-gray-300 @enderror"
                                    style="width: 100%">
                                    <option value="">All</option>
                                </select>
                                @error('sub_category_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_brand_id"
                                    class="block text-gray-700 text-sm font-bold mb-2">Brand</label>
                                <select id="create_brand_id" name="brand_id"
                                    class="form-select w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2 @error('brand_id') border-red-500 @else border-gray-300 @enderror"
                                    style="width: 100%">
                                    <option value="">All</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_unit_id" class="block text-gray-700 text-sm font-bold mb-2">Unit</label>
                                <select id="create_unit_id" name="unit_id"
                                    class="form-select w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2 @error('unit_id') border-red-500 @else border-gray-300 @enderror"
                                    style="width: 100%">
                                    <option value="">All</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_supplier_id"
                                    class="block text-gray-700 text-sm font-bold mb-2">Supplier</label>
                                <select id="create_supplier_id" name="supplier_id"
                                    class="form-select w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 select2 @error('supplier_id') border-red-500 @else border-gray-300 @enderror"
                                    style="width: 100%">
                                    <option value="">All</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                            <div class="mb-2">
                                <label for="create_name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                                <input type="text" id="create_name" name="name" value="{{ old('name') }}"
                                    class="form-input w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @else border-gray-300 @enderror"
                                    placeholder="Enter a Name">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="mb-2">
                                <label for="create_sku" class="block text-gray-700 text-sm font-bold mb-2">SKU</label>
                                <input type="text" id="create_sku" name="sku" value="{{ old('sku') }}"
                                    class="form-input w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('sku') border-red-500 @else border-gray-300 @enderror"
                                    placeholder="Leave blank to generate automatically">
                                @error('sku')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_purchase_price"
                                    class="block text-gray-700 text-sm font-bold mb-2">Purchase Price</label>
                                <input type="number" step="0.01" min="0" id="create_purchase_price" name="purchase_price"
                                    value="{{ old('purchase_price') }}"
                                    class="form-input w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('purchase_price') border-red-500 @else border-gray-300 @enderror"
                                    placeholder="Enter a amount">
                                @error('purchase_price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_previous_price"
                                    class="block text-gray-700 text-sm font-bold mb-2">Previous Price</label>
                                <input type="number" step="0.01" min="0" id="create_previous_price" name="previous_price"
                                    value="{{ old('previous_price') }}"
                                    class="form-input w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('previous_price') border-red-500 @else border-gray-300 @enderror"
                                    placeholder="Enter a amount">
                                @error('previous_price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_selling_price"
                                    class="block text-gray-700 text-sm font-bold mb-2">Selling Price</label>
                                <input type="number" step="0.01" min="0" id="create_selling_price" name="selling_price"
                                    value="{{ old('selling_price') }}"
                                    class="form-input w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('selling_price') border-red-500 @else border-gray-300 @enderror"
                                    placeholder="Enter a amount">
                                @error('selling_price')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label for="create_moq"
                                    class="block text-gray-700 text-sm font-bold mb-2">Minimum Order Qty</label>
                                <input type="number" id="create_moq" name="moq" min="1" value="{{ old('moq', 1) }}"
                                    class="form-input w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('moq') border-red-500 @else border-gray-300 @enderror"
                                    placeholder="Enter minimum order quantity">
                                @error('moq')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center">
                                    {{-- On a validation bounce honour what the admin actually left ticked;
                                         on a fresh form default to Active. --}}
                                    <input type="checkbox" id="create_is_active" name="is_active"
                                        class="form-checkbox h-5 w-5 text-blue-600"
                                        @checked(old('is_active', $errors->any() ? null : 1))>
                                    <span class="ml-2 text-gray-700">Active</span>
                                </label>
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="create_is_trending" name="is_trending"
                                        class="form-checkbox h-5 w-5 text-blue-600" @checked(old('is_trending'))>
                                    <span class="ml-2 text-gray-700">Is Trending</span>
                                </label>
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="create_is_popular" name="is_popular"
                                        class="form-checkbox h-5 w-5 text-blue-600" @checked(old('is_popular'))>
                                    <span class="ml-2 text-gray-700">Is Popular</span>
                                </label>
                            </div>
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="create_is_recommended" name="is_recommended"
                                        class="form-checkbox h-5 w-5 text-blue-600" @checked(old('is_recommended'))>
                                    <span class="ml-2 text-gray-700">Is Recommended</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Specifications
                            </label>

                            <!-- Container -->
                            <div id="specification-container" class="space-y-3">

                                @foreach (old('specification_name', ['']) as $specIndex => $specName)
                                    <!-- Row -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center spec-row">
                                        <input type="text" name="specification_name[]" value="{{ $specName }}"
                                            class="form-input w-full px-3 py-2 border rounded-md"
                                            placeholder="Specification Name">

                                        <input type="text" name="specification_value[]"
                                            value="{{ old('specification_value.' . $specIndex) }}"
                                            class="form-input w-full px-3 py-2 border rounded-md"
                                            placeholder="Specification Value">

                                        <button type="button"
                                            class="remove-spec px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 {{ $loop->first ? 'hidden' : '' }}">
                                            Remove
                                        </button>
                                    </div>
                                @endforeach

                            </div>

                            <!-- Add Button -->
                            <button type="button" id="add-spec"
                                class="mt-3 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                + Add Specification
                            </button>
                        </div>

                        <div class="mt-4">
                            @include('products._variants', ['attributes' => $attributes, 'skus' => []])
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Description
                                </label>

                                <textarea id="editor" class="hidden" name="content">{{ old('content') }}</textarea>

                                <!-- CKEditor container. Seeded with old() so a validation
                                     bounce does not wipe a long description. -->
                                <div id="editor-wrapper"
                                    class="border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500">
                                    {!! old('content') !!}
                                </div>
                                @error('content')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        {{-- <div class="modal-header flex justify-between items-center pt-4 pb-2"
                            style="border-bottom: 3px solid #e8e8e8; width: 100%">
                            <h2 class="text-xl font-semibold">Manage Stock</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-2">
                                <label for="create_available_qty"
                                    class="block text-gray-700 text-sm font-bold mb-2">Available Qty</label>
                                <input type="number" id="create_available_qty" name="available_qty"
                                    class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter a quantity qmount">
                                <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a quantity Amount
                                </p>
                            </div>
                            <div class="mb-2">
                                <label for="create_reserved_qty"
                                    class="block text-gray-700 text-sm font-bold mb-2">Reserved Qty</label>
                                <input type="number" id="create_reserved_qty" name="reserved_qty"
                                    class="form-input w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter a quantity qmount">
                                <p class="text-red-500 text-xs mt-1 hidden error-message">Please enter a a quantity Amount
                                </p>
                            </div>

                        </div> --}}

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        </div>
                        <div class="modal-header flex justify-between items-center pt-4 pb-2"
                            style="border-bottom: 3px solid #e8e8e8; width: 100%">
                            <h2 class="text-xl font-semibold">Manage Gallery</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="mb-2">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Thumbnail Image</label>

                                <input type="file" id="thumbnail_image" name="thumbnail" accept="image/*"
                                    class="form-input w-full px-3 py-2 border rounded-md
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 @error('thumbnail') border-red-500 @else border-gray-300 @enderror" />

                                @error('thumbnail')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-gray-500 text-xs mt-1">JPG, PNG, WEBP or GIF &middot; up to
                                    {{ \App\Helpers\FileLimit::humanUploadMax() }}</p>

                                <!-- Preview -->
                                <div id="thumbnail_preview" class="mt-3"></div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Multiple Images</label>

                                @php $imagesHaveError = $errors->has('images') || count($errors->get('images.*')) > 0; @endphp
                                <input type="file" id="create_images" name="images[]" multiple accept="image/*"
                                    class="form-input w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $imagesHaveError ? 'border-red-500' : 'border-gray-300' }}" />

                                @error('images')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                @foreach ($errors->get('images.*') as $imageErrors)
                                    @foreach ($imageErrors as $imageError)
                                        <p class="text-red-500 text-xs mt-1">{{ $imageError }}</p>
                                    @endforeach
                                @endforeach

                                <p id="image_error" class="text-red-500 text-xs mt-1 hidden">Please upload at least one
                                    image.</p>

                                <div id="image_preview" class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3"></div>

                                <button id="submit_btn"
                                    class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
        (() => {
            // Array to keep selected files
            const filesArray = [];

            const fileInput = document.getElementById('create_images');
            const previewContainer = document.getElementById('image_preview');
            const errorEl = document.getElementById('image_error');
            const submitBtn = document.getElementById('submit_btn');

            // Server limits, so oversized files are caught here instead of being
            // dropped silently by PHP.
            const MAX_FILE_BYTES = {{ \App\Helpers\FileLimit::uploadMaxBytes() }};
            const MAX_POST_BYTES = {{ \App\Helpers\FileLimit::postMaxBytes() }};
            const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            const humanSize = (bytes) => (bytes / 1048576).toFixed(1) + ' MB';

            const rejectFile = (file) => {
                if (!ALLOWED_TYPES.includes(file.type)) {
                    return `"${file.name}" is not a supported image. Use JPG, PNG, WEBP or GIF.`;
                }
                if (file.size > MAX_FILE_BYTES) {
                    return `"${file.name}" is ${humanSize(file.size)}. The server accepts files up to ${humanSize(MAX_FILE_BYTES)}.`;
                }
                return null;
            };

            const warn = (messages) => {
                if (!messages.length) return;
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Some images were not added',
                        html: '<ul class="text-start ps-3 mb-0"><li>' + messages.join('</li><li>') + '</li></ul>',
                        confirmButtonColor: '#d33'
                    });
                } else {
                    alert(messages.join('\n'));
                }
            };

            // Handle newly selected files and append them to filesArray
            fileInput.addEventListener('change', (e) => {
                const newFiles = Array.from(e.target.files);
                const problems = [];

                newFiles.forEach(f => {
                    const problem = rejectFile(f);
                    if (problem) {
                        problems.push(problem);
                        return;
                    }

                    // Optional: filter duplicates by name+size (basic dedupe)
                    const exists = filesArray.some(existing => existing.name === f.name && existing
                        .size === f.size && existing.type === f.type);
                    if (!exists) filesArray.push(f);
                });

                const total = filesArray.reduce((sum, f) => sum + f.size, 0);
                if (total > MAX_POST_BYTES * 0.9) {
                    problems.push(
                        `These images total ${humanSize(total)}. The whole form must stay under ${humanSize(MAX_POST_BYTES)}, so please upload fewer or smaller images.`
                    );
                }

                warn(problems);
                renderPreviews();
            });

            // Write filesArray back into the input at submit time. This lived in a separate
            // <script> where filesArray was out of scope, so removals and multi-pick
            // selections never reached the server.
            const createForm = document.getElementById('createForm');
            if (createForm) {
                createForm.addEventListener('submit', () => {
                    const dt = new DataTransfer();
                    filesArray.forEach(file => dt.items.add(file));
                    fileInput.files = dt.files;
                });
            }

            const thumbnailField = document.getElementById('thumbnail_image');
            if (thumbnailField) {
                thumbnailField.addEventListener('change', () => {
                    const file = thumbnailField.files[0];
                    if (!file) return;
                    const problem = rejectFile(file);
                    if (problem) {
                        thumbnailField.value = '';
                        const preview = document.getElementById('thumbnail_preview');
                        if (preview) preview.innerHTML = '';
                        warn([problem]);
                    }
                });
            }

            // Render file previews from filesArray
            function renderPreviews() {
                previewContainer.innerHTML = '';

                if (filesArray.length === 0) {
                    errorEl.classList.remove('hidden');
                } else {
                    errorEl.classList.add('hidden');
                }

                filesArray.forEach((file, idx) => {
                    const reader = new FileReader();
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative group rounded overflow-hidden border';

                    // placeholder while loading
                    wrapper.innerHTML = `
        <div class="w-full h-28 bg-gray-100 flex items-center justify-center text-xs text-gray-500">Loading...</div>
      `;

                    previewContainer.appendChild(wrapper);

                    reader.onload = (ev) => {
                        wrapper.innerHTML = `
          <img src="${ev.target.result}" alt="${escapeHtml(file.name)}" class="w-full h-28 object-cover"/>
          <button data-idx="${idx}" title="Remove" class="absolute top-1 right-1 bg-white/80 rounded-full p-1 hover:bg-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M6.293 6.293a1 1 0 011.414 0L10 8.586l2.293-2.293a1 1 0 111.414 1.414L11.414 10l2.293 2.293a1 1 0 01-1.414 1.414L10 11.414l-2.293 2.293a1 1 0 01-1.414-1.414L8.586 10 6.293 7.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
          <div class="p-1 text-xs truncate text-center">${escapeHtml(file.name)}</div>
        `;

                        // add remove handler
                        const btn = wrapper.querySelector('button[data-idx]');
                        btn.addEventListener('click', (ev) => {
                            const i = Number(ev.currentTarget.getAttribute('data-idx'));
                            removeFile(i);
                        });
                    };

                    reader.readAsDataURL(file);
                });
            }

            // Remove single file by index
            function removeFile(index) {
                if (index >= 0 && index < filesArray.length) {
                    filesArray.splice(index, 1);
                    renderPreviews();
                }
            }

            // Submit handler (example: using FormData)
            // submitBtn.addEventListener('click', (ev) => {
            //     ev.preventDefault();

            //     if (filesArray.length === 0) {
            //         errorEl.classList.remove('hidden');
            //         return;
            //     }

            //     const fd = new FormData();

            //     // Append all files from our array
            //     filesArray.forEach((file, i) => {
            //         fd.append('images[]', file, file.name);
            //     });

            //     // Example: append other fields if needed
            //     // fd.append('reserved_qty[]', document.querySelector('#create_reserved_qty').value);

            //     // Example fetch (adjust URL & method)
            //     fetch('/upload-endpoint', {
            //             method: 'POST',
            //             body: fd
            //         })
            //         .then(r => {
            //             if (!r.ok) throw new Error('Upload failed');
            //             return r.json();
            //         })
            //         .then(data => {
            //             console.log('Upload success', data);
            //             // clear on success
            //             filesArray.length = 0;
            //             renderPreviews();
            //             alert('Uploaded!');
            //         })
            //         .catch(err => {
            //             console.error(err);
            //             alert('Upload error: ' + err.message);
            //         });
            // });

            // small helper to avoid XSS in file names shown in DOM
            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, function(s) {
                    return ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    })[s];
                });
            }
        })();
    </script>
    @if ($errors->any())
        <script>
            // Land the admin on the first thing that needs fixing rather than at the top.
            document.addEventListener('DOMContentLoaded', () => {
                const firstError = document.querySelector('.border-red-500');
                if (!firstError) return;
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof firstError.focus === 'function') {
                    firstError.focus({ preventScroll: true });
                }
            });
        </script>
    @endif

    <script>
        const thumbnailInput = document.getElementById('thumbnail_image');
        const thumbnailPreview = document.getElementById('thumbnail_preview');

        thumbnailInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            thumbnailPreview.innerHTML = ""; // clear old preview

            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                thumbnailPreview.innerHTML = `
                <img src="${e.target.result}" 
                     class="w-28 h-28 object-cover border rounded-md" />
            `;
            };
            reader.readAsDataURL(file);
        });
    </script>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor-wrapper'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'link', 'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ]
            })
            .then(editor => {
                // editor.ui.view.editable.element.style.minHeight = '200px';
                const textarea = document.querySelector('#editor');

                editor.model.document.on('change:data', () => {
                    textarea.value = editor.getData();
                });
            })
            .catch(error => {
                console.error(error);
            });
    </script>
    <script>
        document.getElementById('add-spec').addEventListener('click', function() {
            const container = document.getElementById('specification-container');

            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 items-center spec-row';

            row.innerHTML = `
            <input type="text" name="specification_name[]"
                   class="form-input w-full px-3 py-2 border rounded-md"
                   placeholder="Specification Name">

            <input type="text" name="specification_value[]"
                   class="form-input w-full px-3 py-2 border rounded-md"
                   placeholder="Specification Value">

            <button type="button"
                        class="remove-spec inline-flex items-center justify-center w-10 h-10
                               bg-red-500 text-white text-xl rounded-full hover:bg-red-600">
                    −
                </button>
        `;

            container.appendChild(row);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-spec')) {
                e.target.closest('.spec-row').remove();
            }
        });
    </script>
    <script>
        function getSubCategory(el, targetSelector) {
            const categoryId = el.value;
            const url = el.dataset.action;
            const $target = $(targetSelector);

            $target.html('<option value="">Loading...</option>');

            if (!categoryId) {
                $target.html('<option value="">All</option>');
                return;
            }

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    category_id: categoryId
                },
                success: function(res) {
                    // Honour a preselected value once, so editing a product (or bouncing
                    // off a validation error) keeps the sub category instead of resetting
                    // it to "All" and silently clearing the column on save.
                    const preselect = $target.attr('data-selected');
                    let options = '<option value="">All</option>';

                    res.forEach(item => {
                        const selected = preselect && String(item.id) === String(preselect) ? ' selected' : '';
                        options += `<option value="${item.id}"${selected}>${item.name}</option>`;
                    });

                    $target.removeAttr('data-selected');
                    $target.html(options).trigger('change');
                },
                error: function() {
                    $target.html('<option value="">Failed to load</option>');
                }
            });
        }

        // Populate the sub category list on load when a category is already chosen.
        $(function () {
            const categoryEl = document.getElementById('create_category_id');
            if (categoryEl && categoryEl.value) {
                getSubCategory(categoryEl, '#create_sub_category_id');
            }
        });
    </script>
    @include('products._variants_js')
@endsection
