@extends('layout.app')
@section('meta-information')
    <title>Users &amp; Roles</title>
@endsection
@section('main-content')
    @php
        $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());

        // Each role gets a fixed colour so the same badge reads the same on every row.
        $roleStyles = [
            'super admin' => 'bg-purple-50 text-purple-700 ring-purple-200',
            'admin' => 'bg-blue-50 text-blue-700 ring-blue-200',
            'accountant' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'vendor' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'agent' => 'bg-sky-50 text-sky-700 ring-sky-200',
        ];
    @endphp

    <div class="mx-auto max-w-[1440px]">
        <div class="mb-5 overflow-hidden rounded-xl shadow-sm">
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">
                    <i class="fas fa-user-shield mr-2"></i> Users &amp; Roles
                </h2>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Role head-count strip --}}
        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($roles as $role)
                <div class="rounded-xl border border-gray-200 bg-white px-4 py-3">
                    <div class="text-2xl font-semibold text-gray-900">{{ $role->users_count }}</div>
                    <div class="mt-0.5 text-xs capitalize text-gray-500">{{ $role->name }}</div>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Admin users</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        People who can sign in to this dashboard. Storefront shoppers live under
                        <a href="{{ route('role.buyers.index', ['role' => $roleSlug]) }}"
                            class="text-blue-600 hover:underline">Customers</a>.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3 font-semibold">Name</th>
                            <th class="px-5 py-3 font-semibold">Email</th>
                            <th class="px-5 py-3 font-semibold">Phone</th>
                            <th class="px-5 py-3 font-semibold">Roles</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold uppercase text-blue-700">
                                            {{ Str::substr($user->name, 0, 2) }}
                                        </span>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                            @if ($user->id === Auth::id())
                                                <div class="text-xs text-gray-400">That's you</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->phone ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    @forelse ($user->roles as $r)
                                        <span
                                            class="mr-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ring-1 ring-inset {{ $roleStyles[$r->name] ?? 'bg-gray-50 text-gray-700 ring-gray-200' }}">
                                            {{ $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400">No role</span>
                                    @endforelse
                                </td>
                                <td class="px-5 py-3">
                                    @if ($user->status)
                                        <span
                                            class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">Active</span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-200">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @if ($user->id !== Auth::id())
                                        <form method="POST"
                                            action="{{ route('role.user.destroy', ['role' => $roleSlug, 'user' => $user->id]) }}"
                                            class="inline"
                                            onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                                title="Delete user">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <i class="fas fa-user-shield mb-3 text-3xl text-gray-300"></i>
                                    <p class="text-gray-500">No admin users found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-gray-200 p-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <p class="mt-4 text-xs text-gray-500">
            Roles are defined by Spatie permissions. Adding and editing users is not built yet —
            <code class="rounded bg-gray-100 px-1 py-0.5">UserController@create/edit/update</code> are still stubs.
        </p>
    </div>
@endsection
