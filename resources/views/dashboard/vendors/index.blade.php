@extends('layout.app')

@section('meta-information')
    <title>Vendor List</title>
@endsection

@section('main-content')

<div class="bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Vendor List</h2>

    {{-- Filter Form --}}
    <form method="GET" action="{{ route('role.vendor.index', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" class="mb-4 flex space-x-4">
        <input type="text" name="search" placeholder="Search by name, email, phone" value="{{ request('search') }}"
               class="border border-gray-300 px-4 py-2 rounded-lg w-1/3 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-200">Filter</button>
    </form>

    {{-- Vendors Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-left border border-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-4 border-b">#</th>
                    <th class="py-2 px-4 border-b">Name</th>
                    <th class="py-2 px-4 border-b">Username</th>
                    <th class="py-2 px-4 border-b">Email</th>
                    <th class="py-2 px-4 border-b">Phone</th>
                    <th class="py-2 px-4 border-b">Status</th>
                    <th class="py-2 px-4 border-b">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">{{ $loop->iteration + ($vendors->currentPage() - 1) * $vendors->perPage() }}</td>
                        <td class="py-2 px-4 border-b">{{ $vendor->name }}</td>
                        <td class="py-2 px-4 border-b">{{ $vendor->username }}</td>
                        <td class="py-2 px-4 border-b">{{ $vendor->email }}</td>
                        <td class="py-2 px-4 border-b">{{ $vendor->phone }}</td>
                        <td class="py-2 px-4 border-b">
                            {{-- <form action="{{ route('role.vendor.toggleStatus', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'id' => $vendor->id]) }}"
                                method="POST">
                                @csrf
                                @method('PUT')

                                <button type="submit"
                                        class="px-3 py-1 text-xs rounded
                                            {{ $vendor->status === 'active' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }} cursor-pointer hover:opacity-80 transition">
                                    {{ ucfirst($vendor->status) }}
                                </button>
                            </form> --}}
                            <button
                                type="button"
                                class="px-3 py-1 text-xs rounded
                                    {{ $vendor->status === 'active' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}
                                    cursor-pointer hover:opacity-80 transition"
                                data-vendor-id="{{ $vendor->id }}"
                                data-vendor-status="{{ $vendor->status }}"
                                onclick="openConfirmModal(this)">
                                {{ ucfirst($vendor->status) }}
                            </button>
                        </td>
                        <td class="py-2 px-4 border-b">
                            <a href="{{ route('role.vendor.edit', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'vendor'=> $vendor->id]) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 px-4 text-center text-gray-500">No vendors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $vendors->withQueryString()->links() }}
    </div>
</div>

<!-- Confirm Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded p-6 w-80 max-w-full shadow-lg">
        <h3 class="text-lg font-semibold mb-4">Confirm Status Change</h3>
        <p id="confirmMessage" class="mb-6">Are you sure you want to change the status?</p>

        <div class="flex justify-end space-x-3">
            <button onclick="closeConfirmModal()" class="px-4 py-2 rounded border border-gray-300 cursor-pointer hover:bg-gray-100 hover:text-red">Cancel</button>

            <form id="confirmForm" method="POST" class="inline">
                @csrf
                @method('PUT')
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded cursor-pointer hover:bg-blue-700">
                    Yes, Change
                </button>
            </form>
        </div>
    </div>
</div>



@endsection


@section('raw-script')
    <script>
    function openConfirmModal(button) {
        const vendorId = button.getAttribute('data-vendor-id');
        const currentStatus = button.getAttribute('data-vendor-status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

        // Update modal message dynamically
        document.getElementById('confirmMessage').textContent =
            `Are you sure you want to change status from "${currentStatus}" to "${newStatus}"?`;

        // Update form action dynamically
        const form = document.getElementById('confirmForm');
        form.action = `/{{ Str::slug(Auth::user()->getRoleNames()->first()) }}/vendor/${vendorId}/toggle-status`;

        // Show modal
        document.getElementById('confirmModal').classList.remove('hidden');
        document.getElementById('confirmModal').classList.add('flex');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        document.getElementById('confirmModal').classList.remove('flex');
    }
</script>

@endsection
