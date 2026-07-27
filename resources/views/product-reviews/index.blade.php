@extends('layout.app')
@section('meta-information')
    <title>Product Reviews</title>
@endsection
@section('css')
    <style>
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
        }

        .filter-container .filter-actions .apply-btn {
            background-color: #3b82f6;
            color: white;
            border: none;
        }

        .filter-container .filter-actions .reset-btn {
            background-color: #f8f9fa;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        .review-stars i {
            color: #d1d5db;
        }

        .review-stars i.filled {
            color: #f59e0b;
        }

        .review-comment {
            max-width: 320px;
            white-space: normal;
        }
    </style>
@endsection
@section('main-content')

    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-star mr-2"></i>Product Reviews
                </h2>
            </div>

            <div class="states-table-content">
                <form action="" method="get">
                    <div class="filter-container">
                        <div class="filter-header">
                            <h3><i class="fas fa-filter mr-2"></i>Filter Options</h3>
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </div>
                        <div class="filter-content">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="rating">Rating</label>
                                    <select name="rating" id="rating" class="form-control">
                                        <option value="">All</option>
                                        @for ($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" {{ (string) request('rating') === (string) $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="filter-actions">
                                <a href="{{ route('role.product-reviews.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="reset-btn">Reset</a>
                                <button type="submit" class="apply-btn">Apply Filters</button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th style="padding-left: 20px" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($reviews as $key => $review)
                                <tr data-review-row="{{ $review->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: 20px">
                                        <strong>{{ ($reviews->currentPage() - 1) * $reviews->perPage() + $key + 1 }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $review->product->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $review->buyer->business_name ?? 'Guest' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="review-stars">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $review->rating ? 'filled' : '' }}"></i>
                                            @endfor
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 review-comment">
                                        @if($review->title)
                                            <strong class="d-block">{{ $review->title }}</strong>
                                        @endif
                                        {{ \Illuminate\Support\Str::limit($review->comment, 120) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" data-status-cell>
                                        @if($review->is_approved)
                                            <span class="badge text-white bg-green-500 px-2 py-1 rounded-full text-xs">Approved</span>
                                        @else
                                            <span class="badge text-white bg-yellow-500 px-2 py-1 rounded-full text-xs">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $review->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="btn-group btn-group-sm flex space-x-1">
                                            @if(!$review->is_approved)
                                                <button type="button"
                                                    class="btn btn-outline-success border border-green-500 text-green-500 hover:bg-green-500 hover:text-white px-3 py-1 rounded-md transition duration-200 approve-review-btn"
                                                    data-action="{{ route('role.product-reviews.approve', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'product_review' => $review->id]) }}"
                                                    title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-outline-warning border border-yellow-500 text-yellow-600 hover:bg-yellow-500 hover:text-white px-3 py-1 rounded-md transition duration-200 reject-review-btn"
                                                    data-action="{{ route('role.product-reviews.reject', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'product_review' => $review->id]) }}"
                                                    title="Unapprove">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif
                                            <button type="button"
                                                class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200 delete-review-btn"
                                                data-id="{{ $review->id }}"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8">
                                        <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                                        <h4 class="text-gray-500 text-xl font-medium mb-2">No reviews found</h4>
                                        <p class="text-gray-400 mb-4">Try filtering with different criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-t border-gray-200">
                    {{ $reviews->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const reviewsBaseUrl = '{{ url(Str::slug(Auth::user()->getRoleNames()->first()) . "/product-reviews") }}';

            $('.approve-review-btn, .reject-review-btn').click(function () {
                const $btn = $(this);
                const $row = $btn.closest('tr');
                $.ajax({
                    url: $btn.data('action'),
                    method: 'PUT',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message, timer: 1200, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong.' });
                    }
                });
            });

            $('.delete-review-btn').click(function () {
                const id = $(this).data('id');
                const $row = $(this).closest('tr');

                Swal.fire({
                    icon: 'warning',
                    title: 'Are you sure?',
                    text: 'This review will be permanently deleted.',
                    showCancelButton: true,
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: reviewsBaseUrl,
                            method: 'DELETE',
                            data: { item_id: id },
                            success: function (response) {
                                if (response.success) {
                                    $row.remove();
                                    Swal.fire({ icon: 'success', title: 'Deleted', text: response.message, timer: 1200, showConfirmButton: false });
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                                }
                            },
                            error: function () {
                                Swal.fire({ icon: 'error', title: 'Oops...', text: 'Something went wrong.' });
                            }
                        });
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const filterHeader = document.querySelector('.filter-container .filter-header');
            const filterContent = document.querySelector('.filter-container .filter-content');

            filterHeader.addEventListener('click', function () {
                this.classList.toggle('active');
                filterContent.classList.toggle('active');
            });

            if ('{{ request('status') }}' || '{{ request('rating') }}') {
                filterHeader.classList.add('active');
                filterContent.classList.add('active');
            }
        });
    </script>
@endsection
