@extends('layout.app')

@section('meta-information')
    <title>Website Setting Create</title>
@endsection

@section('main-content')

<div class="bg-white p-8 mt-1">
    <h2 class="text-2xl font-semibold mb-6 text-gray-700">Add Website Setting</h2>
@if (session('success'))
    <div class="mb-4 flex items-center p-4 rounded-lg bg-green-50 border-l-4 border-green-500 text-green-700">
        <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

    <form action="{{ route('role.website-settings.store', ['role' => Str::slug(Auth::user()->getRoleNames()->first())]) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- title -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->title ?? old('title')}}">
            </div>

            <!-- Logo -->
            <div class="mb-4">
                <label for="logo_path" class="block text-sm font-medium text-gray-700">Logo</label>
                <img src="{{asset($settings->logo_path ?? '')}}" alt="" srcset="" width="100px">
                <input type="file" name="logo_path" id="logo_path"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Favicon -->
            <div class="mb-4">
                <label for="favicon_path" class="block text-sm font-medium text-gray-700">Favicon</label>
                <img src="{{asset($settings->favicon_path ?? '')}}" alt="" srcset="" width="50px">
                <input type="file" name="favicon_path" id="favicon_path"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                <input type="tel" name="contact_phone" id="contact_phone"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->contact_phone ?? old('contact_phone')}}">
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                <input type="email" name="contact_email" id="contact_email"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->contact_email ?? old('contact_email')}}">
            </div>

            <!-- Address (full width row = spans 3 columns) -->
            <div class="md:col-span-3 mb-4">
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="3"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{$settings->address ?? old('address')}}</textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end mt-6">
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                Save
            </button>
        </div>

    </form>
</div>
@endsection
