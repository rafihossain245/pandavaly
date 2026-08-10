@extends('layout.app')

@section('meta-information')
    <title>Roles</title>
@endsection

@section('main-content')
    @php
        $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());
    @endphp

    <div class="mx-auto max-w-[1440px]">
        <div class="mb-5 overflow-hidden rounded-xl shadow-sm">
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">
                    <i class="fas fa-shield-halved mr-2"></i> Roles
                </h2>
                <a href="{{ route('role.roles.create', ['role' => $roleSlug]) }}"
                    class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-blue-800 transition hover:bg-blue-50">
                    <i class="fas fa-plus mr-1"></i> Add role
                </a>
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

        @if ($permissionCount === 0)
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <strong>No permissions are defined yet.</strong>
                Roles still work for grouping people, but until permissions exist there is nothing to
                tick against them — every signed-in user reaches every screen.
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">What each role may do</h3>
                <p class="mt-1 text-xs text-gray-500">
                    Assign a role to someone under
                    <a href="{{ route('role.user.index', ['role' => $roleSlug]) }}" class="text-blue-600 hover:underline">Users</a>.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <th class="px-5 py-3 font-semibold">Role</th>
                            <th class="px-5 py-3 font-semibold">Users</th>
                            <th class="px-5 py-3 font-semibold">Permissions</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            @php $locked = in_array($role->name, $protectedRoles, true); @endphp
                            <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="font-medium capitalize text-gray-900">{{ $role->name }}</div>
                                    @if ($locked)
                                        <div class="text-xs text-gray-400">Built in — cannot be renamed or deleted</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $role->users_count }}</td>
                                <td class="px-5 py-3 text-gray-600">
                                    @if ($role->permissions_count)
                                        {{ $role->permissions_count }} granted
                                    @else
                                        <span class="text-gray-400">None</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('role.roles.edit', ['role' => $roleSlug, 'roles' => $role->id]) }}"
                                        class="mr-1 inline-block rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-500 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                        title="Edit role">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @unless ($locked)
                                        <form method="POST"
                                            action="{{ route('role.roles.destroy', ['role' => $roleSlug, 'roles' => $role->id]) }}"
                                            class="inline"
                                            onsubmit="return confirm('Delete the {{ $role->name }} role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs text-gray-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                                title="Delete role">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <i class="fas fa-shield-halved mb-3 text-3xl text-gray-300"></i>
                                    <p class="text-gray-500">No roles defined yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
