@extends('layout.app')

@section('meta-information')
    <title>Edit Vendor</title>
@endsection

@section('main-content')
    <div class="bg-white p-8 mt-1">
        <h2 class="text-2xl font-semibold mb-6 text-gray-700">Edit Vendor</h2>

        <form class="p-2"
            action="{{ route('role.vendor.update', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'vendor' => $vendor->id]) }}"
            method="POST">
            @csrf
            @method('PUT')

            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Name --}}
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $vendor->name) }}" required
                    class="mt-1 block w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contact Person --}}
            <div class="mb-4">
                <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
                <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}"
                    class="mt-1 block w-full px-4 py-2 border @error('contact_person') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('contact_person')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $vendor->email) }}"
                    class="mt-1 block w-full px-4 py-2 border @error('email') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div class="mb-4">
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $vendor->phone) }}"
                    class="mt-1 block w-full px-4 py-2 border @error('phone') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('phone')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Address --}}
            <div class="mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="3"
                    class="mt-1 block w-full px-4 py-2 border @error('address') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('address', $vendor->address) }}</textarea>
                @error('address')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password (optional update) --}}
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password <span class="text-gray-400 text-sm">(Leave blank to keep current)</span></label>
                <input type="password" name="password" id="password"
                    class="mt-1 block w-full px-4 py-2 border @error('password') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-4">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 cursor-pointer">
                    Update
                </button>
            </div>
        </form>

        @foreach($vendor->logs()->latest()->get() as $log)
    <div class="bg-gray-100 border-b mb-4 mt-2 p-3 pb-2 rounded">
        <strong>{{ ucfirst($log->action) }} by {{ optional($log->user)->name ?? 'System' }}</strong><br>
        <small>{{ $log->created_at->format('d M Y h:i A') }}</small>

        <ul class="mt-2 text-sm text-gray-600">
            @foreach($log->after as $field => $newValue)
                @php $oldValue = $log->before[$field] ?? null; @endphp
                @if($oldValue !== $newValue)
                    <li>{{ ucfirst($field) }}: {{ $oldValue }} → <strong>{{ $newValue }}</strong></li>
                @endif
            @endforeach
        </ul>

        @if($log->action === 'updated' && $log->before)
            <form action="{{ route('role.vendor.restore', ['role' => Str::slug(Auth::user()->getRoleNames()->first()), 'log' => $log->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to restore this version?');">
                @csrf
                @method('PUT')
                <button type="submit" class="mt-2 inline-block px-4 py-1 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Restore This Version
                </button>
            </form>
        @endif
    </div>
@endforeach

    </div>
@endsection
