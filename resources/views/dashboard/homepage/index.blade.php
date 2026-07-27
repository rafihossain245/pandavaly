@extends('layout.app')

@section('meta-information')
    <title>Home Pages</title>
@endsection

@section('main-content')

<div class="bg-white p-8 mt-1">
    <h2 class="text-2xl font-semibold mb-6 text-gray-700">Add Home Page setting</h2>
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

            <!-- Logo -->
            <div class="mb-4">
                <label for="home_banner_one" class="block text-sm font-medium text-gray-700">Home Banner 1</label>
                <img src="{{asset($settings->home_banner_one ?? '')}}" alt="" srcset="" width="100px">
                <input type="file" name="home_banner_one" id="home_banner_one"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Favicon -->
            <div class="mb-4">
                <label for="home_banner_two" class="block text-sm font-medium text-gray-700">Home Banner 2</label>
                <img src="{{asset($settings->home_banner_two ?? '')}}" alt="" srcset="" width="50px">
                <input type="file" name="home_banner_two" id="home_banner_two"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Featured Title One -->
            <div class="mb-4">
                <label for="featured_title_one" class="block text-sm font-medium text-gray-700">Featured Title One</label>
                <input type="text" name="featured_title_one" id="featured_title_one"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_title_one ?? old('featured_title_one')}}">
            </div>

            <!-- Featured Description One -->
            <div class="mb-4">
                <label for="featured_description_one" class="block text-sm font-medium text-gray-700">Featured Description One</label>
                <input type="text" name="featured_description_one" id="featured_description_one"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_description_one ?? old('featured_description_one')}}">
            </div>
            <!-- Featured icon -->
            <div class="mb-4">
                <label for="featured_icon_one" class="block text-sm font-medium text-gray-700">Featured Icon One</label>
                <input type="file" name="featured_icon_one" id="featured_icon_one"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_icon_one ?? old('featured_icon_one')}}">
            </div>
            {{-- featured two --}}
            <!-- Featured Title Two -->
            <div class="mb-4">
                <label for="featured_title_two" class="block text-sm font-medium text-gray-700">Featured Title Two</label>
                <input type="text" name="featured_title_two" id="featured_title_two"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_title_two ?? old('featured_title_two')}}">
            </div>
            <!-- Featured Description Two -->
            <div class="mb-4">
                <label for="featured_description_two" class="block text-sm font-medium text-gray-700">Featured Description Two</label>
                <input type="text" name="featured_description_two" id="featured_description_two"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_description_two ?? old('featured_description_two')}}">
            </div>
            <!-- Featured Icon Two -->
            <div class="mb-4">
                <label for="featured_icon_two" class="block text-sm font-medium text-gray-700">Featured Icon Two</label>
                <input type="file" name="featured_icon_two" id="featured_icon_two"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_icon_two ?? old('featured_icon_two')}}">
            </div>
            {{-- featured three --}}
            <!-- Featured Title Three -->
            <div class="mb-4">
                <label for="featured_title_three" class="block text-sm font-medium text-gray-700">Featured Title Three</label>
                <input type="text" name="featured_title_three" id="featured_title_three"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_title_three ?? old('featured_title_three')}}">
            </div>
            <!-- Featured Description Three -->
            <div class="mb-4">
                <label for="featured_description_three" class="block text-sm font-medium text-gray-700">Featured Description Three</label>
                <input type="text" name="featured_description_three" id="featured_description_three"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_description_three ?? old('featured_description_three')}}">
            </div>
            <!-- Featured Icon Three -->
            <div class="mb-4">
                <label for="featured_icon_three" class="block text-sm font-medium text-gray-700">Featured Icon Three</label>
                <input type="file" name="featured_icon_three" id="featured_icon_three"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_icon_three ?? old('featured_icon_three')}}">
            </div>
            {{-- featured four --}}
            <!-- Featured Title Four -->
            <div class="mb-4">
                <label for="featured_title_four" class="block text-sm font-medium text-gray-700">Featured Title Four</label>
                <input type="text" name="featured_title_four" id="featured_title_four"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_title_four ?? old('featured_title_four')}}">
            </div>
            <!-- Featured Description Four -->
            <div class="mb-4">
                <label for="featured_description_four" class="block text-sm font-medium text-gray-700">Featured Description Four</label>
                <input type="text" name="featured_description_four" id="featured_description_four"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_description_four ?? old('featured_description_four')}}">
            </div>
            <!-- Featured Icon Four -->
            <div class="mb-4">
                <label for="featured_icon_four" class="block text-sm font-medium text-gray-700">Featured Icon Four</label>
                <input type="file" name="featured_icon_four" id="featured_icon_four"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{$settings->featured_icon_four ?? old('featured_icon_four')}}">
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
