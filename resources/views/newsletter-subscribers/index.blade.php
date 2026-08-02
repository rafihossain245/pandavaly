@extends('layout.app')
@section('meta-information')
    <title>Newsletter Subscribers</title>
@endsection
@section('css')
<style>
    .states-table { margin-top: 2rem; }
    .states-table .states-table-container { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); overflow: hidden; }
    .states-table .states-table-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid #e9ecef; }
    .states-table .states-table-header .states-table-title { margin: 0; font-size: 1.25rem; font-weight: 600; color: #333; }
    .states-table .states-table-content .table-responsive { overflow-x: auto; }
    .states-table .states-table-content .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 1rem 0.75rem; font-weight: 600; color: #495057; }
    .states-table .states-table-content .table tbody td { padding: 1rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #e9ecef; }
    .states-table-header { background: linear-gradient(90deg, #1e3a8a 0%, #1e40af 100%); color: white }
    .modal { transition: opacity 0.25s ease; }
    .modal-backdrop { background-color: rgba(0, 0, 0, 0.5); }
</style>
@endsection
@section('main-content')
    <div class="states-table bg-white rounded-lg shadow-md overflow-hidden">
        <div class="states-table-container">
            <div class="states-table-header bg-blue-600 px-6 py-4 flex justify-between items-center">
                <h2 class="states-table-title text-white text-xl font-semibold" style="color: white">
                    <i class="fas fa-envelope mr-2"></i>Newsletter Subscribers
                </h2>
            </div>
            <div class="states-table-content">
                <div class="table-responsive overflow-x-auto" style="padding: 15px">
                    <table class="table table-hover min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th style="padding-left: 20px" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SL</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribed At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($datas as $key => $value)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap" style="padding-left: 20px"><strong>{{ ($datas->currentPage() - 1) * $datas->perPage() + $key + 1 }}</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $value->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button class="btn btn-outline-danger border border-red-500 text-red-500 hover:bg-red-500 hover:text-white px-3 py-1 rounded-md transition duration-200" onclick="confirmDelete('{{ $value->id }}', '{{ $value->email }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8">
                                        <i class="fas fa-inbox fa-3x text-gray-400 mb-4"></i>
                                        <h4 class="text-gray-500 text-xl font-medium mb-2">No subscribers yet</h4>
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

    <div id="deleteModal" class="modal fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="modal-backdrop fixed inset-0 bg-black opacity-50"></div>
        <div class="modal-container bg-white w-11/12 md:max-w-md mx-auto rounded shadow-lg z-50 overflow-y-auto">
            <div class="modal-content py-4 text-left px-6">
                <div class="modal-header flex justify-between items-center pb-3">
                    <h3 class="text-xl font-semibold">Confirm Delete</h3>
                    <button class="modal-close-delete z-50"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <p>Remove <span id="deleteName" class="font-semibold"></span> from the subscriber list?</p>
                </div>
                <div class="modal-footer flex justify-end pt-2">
                    <button class="btn btn-secondary px-4 py-2 bg-gray-300 text-gray-700 rounded-md mr-2 modal-close-delete">Cancel</button>
                    <button id="confirmDeleteBtn" data-action="{{ route('role.newsletter-subscribers.destroy', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'id' => 1]) }}" class="btn btn-danger px-4 py-2 bg-red-500 text-white rounded-md">Delete</button>
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
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $('.modal-close-delete, .modal-backdrop').click(function (e) {
                if ($(e.target).hasClass('modal-backdrop') || $(e.target).closest('.modal-close-delete').length) $('#deleteModal').addClass('hidden');
            });
            $('#confirmDeleteBtn').click(function () {
                $.ajax({
                    url: $(this).data('action'),
                    method: 'DELETE',
                    data: { item_id: $(this).data('item-id') },
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

        function confirmDelete(id, email) {
            $('#deleteName').text(email);
            $('#confirmDeleteBtn').data('item-id', id);
            $('#deleteModal').removeClass('hidden');
        }
    </script>
@endsection
