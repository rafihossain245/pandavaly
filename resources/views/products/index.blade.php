@extends('layout.app')
@section('meta-information')
    <title>Manage Products</title>
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
        span [aria-current="page"] span {
            background-color: #2563eb !important;
            background: #2563eb !important;
            color: white;
            border-color: #2563eb;
        }
    </style>
@endsection
@section('main-content')

    <!-- States Table -->
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-list mr-2"></i>Product List
                </h2>
                <a href="{{ route('role.products.create', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                    class="btn btn-primary bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New Product
                </a>
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
                            <div class="closest filter-row">
                                <div class="filter-group">
                                    <label for="brand_id">Brand</label>
                                    <select id="brand_id" name="brand_id" class="form-control select2" style="width: 100%">
                                        <option value="">All</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ $brand->id == request('brand_id') ? 'selected' : '' }}>
                                                {{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="category_id">Category</label>
                                    <select id="category_id" name="category_id"
                                        onchange="getSubCategory(this, '#sub_category_id')"
                                        data-action="{{ route('role.get-sub-category', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}"
                                        class="form-control select2" style="width: 100%">
                                        <option value="">All</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $category->id == request('category_id') ? 'selected' : '' }}>
                                                {{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="sub_category_id">Sub Category</label>
                                    <select id="sub_category_id" name="sub_category_id" class="form-control select2"
                                        style="width: 100%">
                                        <option value="">All</option>
                                        @if (!empty($req_subdatas))
                                            @foreach ($req_subdatas as $subdata)
                                                <option value="{{ $subdata->id }}"
                                                    {{ $subdata->id == request('sub_category_id') ? 'selected' : '' }}>
                                                    {{ $subdata->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="closest filter-row">
                                <div class="filter-group">
                                    <label for="stock_qty">Stock</label>
                                    <select id="stock_qty" name="stock_qty" class="form-control select2"
                                        style="width: 100%">
                                        <option value="">All</option>
                                        <option value="1" @selected(request('stock_qty') === '1')>In Stock</option>
                                        <option value="0" @selected(request('stock_qty') === '0')>Out of Stock</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="is_active">Status</label>
                                    <select id="is_active" name="is_active" class="form-control select2"
                                        style="width: 100%">
                                        <option value="">All</option>
                                        <option value="1" @selected(request('is_active') === '1')>Active</option>
                                        <option value="0" @selected(request('is_active') === '0')>Draft</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="search">Name/SKU</label>
                                    <input type="text" name="search" value="{{ request('search') }}" id="search"
                                        class="form-control" placeholder="Enter search">
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
                <div class="overflow-x-auto p-4">
                    <table class="w-full min-w-[820px] text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-200 bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
                                <th class="px-4 py-3 font-semibold">Product</th>
                                <th class="px-4 py-3 font-semibold">Category</th>
                                <th class="px-4 py-3 text-right font-semibold">Price</th>
                                <th class="px-4 py-3 font-semibold">Stock</th>
                                <th class="px-4 py-3 font-semibold">Status</th>
                                <th class="px-4 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($datas as $value)
                                @php
                                    // product_prices[0] threw "Undefined array key 0" for any product
                                    // whose price row is missing, taking the whole list down.
                                    $price = $value->product_prices->first();
                                    $stock = (int) $value->stock_qty;
                                    $hasVariants = $value->skus_count > 0;
                                @endphp
                                <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            {{-- Placeholder icon sits underneath, so a missing OR broken
                                                 file degrades to the icon. alt is empty and the box clips,
                                                 otherwise a 404 spills alt text across the row. --}}
                                            <span
                                                class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50 text-gray-300">
                                                <i class="fas fa-image"></i>
                                                @if ($value->thumbnail)
                                                    <img src="{{ asset($value->thumbnail) }}" alt=""
                                                        loading="lazy" onerror="this.remove()"
                                                        class="absolute inset-0 h-full w-full bg-white object-cover">
                                                @endif
                                            </span>
                                            <div class="min-w-0">
                                                <div class="truncate font-medium text-gray-900">{{ $value->name }}</div>
                                                <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-500">
                                                    @if ($value->brand?->name)
                                                        <span>{{ $value->brand->name }}</span>
                                                        <span class="text-gray-300">&middot;</span>
                                                    @endif
                                                    <span class="font-mono">{{ $value->sku }}</span>
                                                    @if ($hasVariants)
                                                        <span
                                                            class="rounded bg-indigo-50 px-1.5 py-0.5 font-medium text-indigo-700">{{ $value->skus_count }}
                                                            {{ Str::plural('variant', $value->skus_count) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-700">{{ $value->category?->name ?? '—' }}</div>
                                        @if ($value->sub_category?->name)
                                            <div class="mt-0.5 text-xs text-gray-400">{{ $value->sub_category->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <div class="font-medium text-gray-900">
                                            ৳{{ $price ? number_format($price->selling_price, 2) : '—' }}</div>
                                        @if ($price && $price->previous_price > $price->selling_price)
                                            <div class="text-xs text-gray-400 line-through">
                                                ৳{{ number_format($price->previous_price, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        {{-- products.stock_qty: typed on the product form for a simple
                                             product, or rolled up from the variant SKUs. --}}
                                        @if ($stock <= 0)
                                            <span
                                                class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">Out
                                                of stock</span>
                                        @elseif ($stock <= 10)
                                            <span
                                                class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200">Low
                                                · {{ $stock }}</span>
                                        @else
                                            <span
                                                class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $stock }}
                                                in stock</span>
                                        @endif
                                        @if ($hasVariants)
                                            <div class="mt-0.5 text-xs text-gray-400">across variants</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        @if ($value->is_active)
                                            <span
                                                class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">Active</span>
                                        @else
                                            <span
                                                class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200">Draft</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        <div class="flex justify-end gap-1.5">
                                            <a href="{{ route('role.products.edit', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'product' => $value->id]) }}"
                                                class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                                title="Edit product">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <button type="button"
                                                class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                                title="Delete product"
                                                onclick="confirmDelete('{{ $value->id }}', 'this item')"
                                                data-action="{{ route('role.products.destroy', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'product' => $value->id]) }}">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-14 text-center">
                                        <i class="fas fa-box-open mb-3 text-4xl text-gray-300"></i>
                                        <h4 class="mb-1 text-lg font-medium text-gray-500">No products found</h4>
                                        <p class="text-sm text-gray-400">Try a different filter, or add your first
                                            product.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 border-t border-gray-200">
                    {{ $datas->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- @include('products.create-modal')
     --}}
    @include('products.delete-modal')

    <div id="editModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-3xl mx-auto rounded shadow-lg z-50">
            <div class="modal-content flex flex-col py-4 text-left px-6" id="appendEditHtml">

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
        $(document).ready(function() {
            $('.select2').select2();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        // Show create modal
        $('.create-new-btn').click(function() {
            $('#createModal').removeClass('hidden');
        });

        // Show edit modal
        $('.edit-item-btn').click(function() {
            const item_id = $(this).data('item_id');
            const edit_sub_category_id = $(this).data('edit_sub_category_id');
            $.ajax({
                url: $(this).data('action'),
                method: 'GET',
                data: {
                    item_id: item_id
                },
                success: function(response) {
                    console.log(response);
                    if (response.success && response.data.modal_view) {
                        $('#appendEditHtml').html(response.data.modal_view);
                        $('#editModal').removeClass('hidden');
                        $('#edit_category_id,#edit_sub_category_id,#edit_brand_id,#edit_unit_id,#edit_supplier_id')
                            .select2();
                        getEditSubCategory(edit_sub_category_id);

                        // Scripts inserted via .html() are not executed by the browser,
                        // so re-run the variants builder script manually.
                        var $variantsScript = $('#appendEditHtml').find('#variants-js-script');
                        if ($variantsScript.length) {
                            $.globalEval($variantsScript.text());
                        }
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

        // Close modals
        $('.modal-close-create, .modal-backdrop').click(function(e) {
            if ($(e.target).closest('.modal-close-create').length || $(e.target).hasClass('modal-backdrop')) {
                $('#createModal').addClass('hidden');
            }
        });

        $('.modal-close-delete, .modal-backdrop').click(function(e) {
            if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-delete').length) {
                $('#deleteModal').addClass('hidden');
            }
        });

        // Close success alert
        $('.close-btn').click(function() {
            $(this).closest('.alert').addClass('hidden');
        });

        // Create state form submission
        $('#createSubmit').click(function(e) {
            e.preventDefault();
            console.log(validateCreateForm());
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
                            $('#createForm')[0].reset();
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


        // Delete confirmation
        $('#confirmDeleteBtn').click(function() {
            const dataId = $(this).data('item-id');
            const deleteUrl = $(this).data('action');
            $.ajax({
                url: deleteUrl,
                method: 'DELETE',
                data: {
                    item_id: dataId,
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Done",
                            text: "Data deleted successfully!",
                        });
                        $('#deleteModal').addClass('hidden');
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

        function getEditSubCategory(edit_sub_category_id = null) {
            $.ajax({
                url: $('.edit-item-btn').data('get_sub_action'),
                method: 'GET',
                data: {
                    category_id: $('#edit_category_id').val()
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        const targetSelect = $('#edit_sub_category_id');
                        console.log(targetSelect);
                        targetSelect.empty();
                        targetSelect.append('<option value="">Select an Item</option>');
                        $.each(response.data, function(index, item) {
                            targetSelect.append(
                                `<option value="${item.id}" ${ edit_sub_category_id && (edit_sub_category_id == item.id) ? 'selected' : ''}>${item.name}</option>`
                            );
                        });
                        if (targetSelect.hasClass('select2-hidden-accessible')) {
                            targetSelect.trigger('change.select2');
                        }
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
        }

        function getSubCategory(obj, targetId) {
            $.ajax({
                url: $(obj).data('action'),
                method: 'GET',
                data: {
                    category_id: $(obj).val()
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        const targetSelect = $(obj).closest('.closest').find(targetId);
                        console.log(targetSelect);
                        targetSelect.empty();
                        targetSelect.append('<option value="">Select an Item</option>');
                        $.each(response.data, function(index, item) {
                            targetSelect.append(
                                `<option value="${item.id}">${item.name}</option>`
                            );
                        });
                        if (targetSelect.hasClass('select2-hidden-accessible')) {
                            targetSelect.trigger('change.select2');
                        }
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
        }

        function select2SetValueNoEvent(selectId, value) {
            var $select = $(selectId);

            // Set the underlying value
            $select.val(value);

            // Find the selected option text
            var text = $select.find('option:selected').text() || '';

            // Update the visible Select2 box manually
            $select.data('select2').$container.find('.select2-selection__rendered').text(text);
        }

        // Form validation functions
        function validateCreateForm() {
            let isValid = true;

            // Reset error messages
            $('#createForm .error-message').addClass('hidden');
            $('#createForm .form-select, #createForm .form-input').removeClass('border-red-500');

            if (!$('#create_name').val().trim()) {
                $('#create_name').next('.error-message').removeClass('hidden');
                $('#create_name').addClass('border-red-500');
                isValid = false;
            }
            if (!$('#create_purchase_price').val().trim()) {
                $('#create_purchase_price').next('.error-message').removeClass('hidden');
                $('#create_purchase_price').addClass('border-red-500');
                isValid = false;
            }

            return isValid;
        }

        function validateEditForm() {
            let isValid = true;

            // Reset error messages
            $('#editForm .error-message').addClass('hidden');
            $('#editForm .form-select, #editForm .form-input').removeClass('border-red-500');

            if (!$('#edit_name').val().trim()) {
                $('#edit_name').next('.error-message').removeClass('hidden');
                $('#edit_name').addClass('border-red-500');
                isValid = false;
            }
            if (!$('#edit_purchase_price').val().trim()) {
                $('#edit_purchase_price').next('.error-message').removeClass('hidden');
                $('#edit_purchase_price').addClass('border-red-500');
                isValid = false;
            }

            return isValid;
        }

        // Reset create form
        function resetCreateForm() {
            $('#createForm')[0].reset();
            $('#createForm .error-message').addClass('hidden');
            $('#createForm .form-select, #createForm .form-input').removeClass('border-red-500');
        }

        // Delete confirmation
        function confirmDelete(id, name = null) {
            $('#deleteName').text(name);
            $('#confirmDeleteBtn').data('item-id', id);
            $('#deleteModal').removeClass('hidden');
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
                const inputs = document.querySelectorAll(
                    '.filter-container select, .filter-container input');
                inputs.forEach(input => {
                    if (input.type === 'date') {
                        input.value = '';
                    } else {
                        input.selectedIndex = 0;
                    }
                });
            });
            document.querySelector('.reset-btn').addEventListener('click', function(e) {
                e.preventDefault();
                window.location =
                    '{{ route('role.products.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}';
            });
        });
    </script>
@endsection
