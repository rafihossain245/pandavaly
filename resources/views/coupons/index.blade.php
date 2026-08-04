@extends('layout.app')
@section('meta-information')
    <title>Manage Coupons</title>
@endsection
@section('css')
    <style>
        .modal { transition: opacity 0.25s ease; }
        .modal-backdrop { background-color: rgba(0, 0, 0, 0.5); }
        .states-table { margin-top: 0; }
        .states-table .states-table-container { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .states-table .states-table-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e9ecef; }
        .states-table .states-table-header .states-table-title { margin: 0; font-size: 1.25rem; font-weight: 600; color: #333; }
        .states-table .states-table-content .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 1rem 0.75rem; font-weight: 600; color: #495057; }
        .states-table .states-table-content .table tbody td { padding: 1rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #e9ecef; }
        .states-table .states-table-content .table tbody tr:hover { background-color: #f8f9fa; }
        .states-table-header { background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%); color: white }
        .coupon-code { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-weight: 700; letter-spacing: .5px; }
        .cap-field.hidden-field { display: none; }
    </style>
@endsection
@section('main-content')
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-tags mr-2"></i>Coupons &amp; Gift Vouchers
                </h2>
                <button class="btn btn-primary create-new-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New Coupon
                </button>
            </div>

            <div class="states-table-content">
                <p class="text-gray-500 text-sm p-4 pb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    Buyers enter these codes in the &ldquo;Have any coupon or gift voucher?&rdquo; box at checkout. The discount is applied to the subtotal, before delivery cost.
                </p>
                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conditions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($datas as $key => $value)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <strong>{{ ($datas->currentPage() - 1) * $datas->perPage() + $key + 1 }}</strong>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="coupon-code">{{ $value->code }}</div>
                                        @if ($value->description)
                                            <div class="text-xs text-gray-500">{{ $value->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- bg-blue-500 not bg-indigo-500: this project's Tailwind build has no
                                             indigo palette, so an indigo badge renders white-on-transparent. --}}
                                        <span class="badge text-white bg-blue-500 px-2 py-1 rounded-full text-xs">{{ $value->label }}</span>
                                        @if ($value->type === 'percent' && $value->max_discount)
                                            <div class="text-xs text-gray-500">Max Tk {{ number_format($value->max_discount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($value->min_order_amount)
                                            <div class="text-xs text-gray-600">Min order Tk {{ number_format($value->min_order_amount, 2) }}</div>
                                        @else
                                            <span class="text-xs text-gray-400">No minimum</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-600">
                                            {{ $value->starts_at ? $value->starts_at->format('d M Y') : 'Anytime' }}
                                            &rarr;
                                            {{ $value->ends_at ? $value->ends_at->format('d M Y') : 'No end' }}
                                        </div>
                                        @if ($value->ends_at && $value->ends_at->isPast())
                                            <div class="text-xs text-red-500">Expired</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm">{{ $value->used_count }}{{ $value->usage_limit ? ' / ' . $value->usage_limit : '' }}</span>
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
                                            <button class="btn btn-outline-primary edit-item-btn border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-3 py-1 rounded-md transition duration-200"
                                                data-item_id="{{ $value->id }}"
                                                data-code="{{ $value->code }}"
                                                data-description="{{ $value->description }}"
                                                data-type="{{ $value->type }}"
                                                data-value="{{ $value->value }}"
                                                data-min_order_amount="{{ $value->min_order_amount }}"
                                                data-max_discount="{{ $value->max_discount }}"
                                                data-starts_at="{{ optional($value->starts_at)->format('Y-m-d\TH:i') }}"
                                                data-ends_at="{{ optional($value->ends_at)->format('Y-m-d\TH:i') }}"
                                                data-usage_limit="{{ $value->usage_limit }}"
                                                data-is_active="{{ (int) $value->is_active }}"
                                                title="Edit Coupon">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200"
                                                onclick="confirmDelete('{{ $value->id }}', '{{ $value->code }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8">
                                        <i class="fas fa-tags fa-3x text-gray-400 mb-4"></i>
                                        <h4 class="text-gray-500 text-xl font-medium mb-2">No coupons yet</h4>
                                        <p class="text-gray-400 mb-4">Create a coupon so buyers can redeem it at checkout.</p>
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

    @include('coupons.create-modal')
    @include('coupons.edit-modal')
    @include('coupons.delete-modal')
@endsection
@section('js')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // The max-discount cap only applies to percentage coupons.
            function toggleCapField(prefix) {
                var isPercent = $('#' + prefix + '_type').val() === 'percent';
                $('#' + prefix + '_cap_field').toggleClass('hidden-field', !isPercent);
                $('#' + prefix + '_value_suffix').text(isPercent ? '%' : 'Tk');
            }

            $('#create_type').on('change', function () { toggleCapField('create'); });
            $('#edit_type').on('change', function () { toggleCapField('edit'); });

            $('.create-new-btn').click(function () {
                $('#createForm')[0].reset();
                $('#create_is_active').prop('checked', true);
                toggleCapField('create');
                $('#createModal').removeClass('hidden');
            });

            $('.edit-item-btn').click(function () {
                var $btn = $(this);
                $('#editItemId').val($btn.data('item_id'));
                $('#editForm').attr('action', '{{ route('role.coupons.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}/' + $btn.data('item_id'));
                $('#edit_code').val($btn.data('code'));
                $('#edit_description').val($btn.data('description'));
                $('#edit_type').val($btn.data('type'));
                $('#edit_value').val($btn.data('value'));
                $('#edit_min_order_amount').val($btn.data('min_order_amount'));
                $('#edit_max_discount').val($btn.data('max_discount'));
                $('#edit_starts_at').val($btn.data('starts_at'));
                $('#edit_ends_at').val($btn.data('ends_at'));
                $('#edit_usage_limit').val($btn.data('usage_limit'));
                $('#edit_is_active').prop('checked', !!$btn.data('is_active'));
                toggleCapField('edit');
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

            function submitForm($form, $modal, failMessage) {
                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: new FormData($form[0]),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Done', text: response.message });
                            $modal.addClass('hidden');
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message || 'Something went wrong.' });
                        }
                    },
                    error: function (xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || failMessage;
                        Swal.fire({ icon: 'error', title: 'Oops...', text: msg });
                    }
                });
            }

            $('#createSubmit').click(function () {
                if (!$('#create_code').val().trim() || !$('#create_value').val()) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Code and discount value are required.' });
                    return;
                }
                submitForm($('#createForm'), $('#createModal'), 'Failed to create coupon.');
            });

            $('#editSubmit').click(function () {
                if (!$('#edit_code').val().trim() || !$('#edit_value').val()) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Code and discount value are required.' });
                    return;
                }
                submitForm($('#editForm'), $('#editModal'), 'Failed to update coupon.');
            });

            $('#confirmDeleteBtn').click(function () {
                var $btn = $(this);
                $.ajax({
                    url: $btn.data('action'),
                    method: 'POST',
                    data: { _method: 'DELETE' },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ icon: 'success', title: 'Deleted', text: response.message });
                            $('#deleteModal').addClass('hidden');
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error!', text: 'Failed to delete coupon.' });
                    }
                });
            });
        });

        function confirmDelete(id, code) {
            $('#deleteName').text(code);
            $('#confirmDeleteBtn').data('action', '{{ route('role.coupons.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}/' + id);
            $('#deleteModal').removeClass('hidden');
        }
    </script>
@endsection
