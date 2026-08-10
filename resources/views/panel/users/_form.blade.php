{{--
    Shared by create and edit. $user is null when creating, which is also what
    decides whether the password is required and what the hint underneath says.
--}}
@php
    $user = $user ?? null;
    $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());
    $currentRole = old('role', $user?->roles->first()?->name);
@endphp

<div class="mx-auto max-w-3xl">
    <div class="mb-5 overflow-hidden rounded-xl shadow-sm">
        <div class="flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
            <h2 class="text-xl font-semibold text-white">
                <i class="fas fa-user-shield mr-2"></i>
                {{ $user ? 'Edit ' . $user->name : 'Add admin user' }}
            </h2>
            <a href="{{ route('role.user.index', ['role' => $roleSlug]) }}"
                class="rounded-lg bg-white/10 px-3 py-1.5 text-sm text-white transition hover:bg-white/20">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <strong>{{ $errors->count() }} {{ Str::plural('field', $errors->count()) }} need attention.</strong>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
        action="{{ $user
            ? route('role.user.update', ['role' => $roleSlug, 'user' => $user->id])
            : route('role.user.store', ['role' => $roleSlug]) }}">
        @csrf
        @if ($user)
            @method('PUT')
        @endif

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Account</h3>
                <p class="mt-1 text-xs text-gray-500">The name and email this person signs in with.</p>
            </div>

            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user?->email) }}" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $user?->phone) }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact person</label>
                    <input type="text" name="contact_person" id="contact_person"
                        value="{{ old('contact_person', $user?->contact_person) }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="2"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('address', $user?->address) }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Access</h3>
                <p class="mt-1 text-xs text-gray-500">
                    The role decides which screens they see. Manage what each role may do under
                    <a href="{{ route('role.roles.index', ['role' => $roleSlug]) }}" class="text-blue-600 hover:underline">Roles</a>.
                </p>
            </div>

            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="role" required
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected($currentRole === $role->name)>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="active" @selected(old('status', $user?->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $user?->status) === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password @unless ($user)<span class="text-red-500">*</span>@endunless
                    </label>
                    <input type="password" name="password" id="password" autocomplete="new-password"
                        @unless ($user) required @endunless
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $user ? 'Leave empty to keep the current password.' : 'At least 8 characters.' }}
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        autocomplete="new-password" @unless ($user) required @endunless
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="mt-4 flex items-center justify-end gap-2">
            <a href="{{ route('role.user.index', ['role' => $roleSlug]) }}"
                class="rounded-lg border border-gray-300 px-5 py-2 text-sm text-gray-700 transition hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                <i class="fas fa-floppy-disk mr-1"></i> {{ $user ? 'Save changes' : 'Create user' }}
            </button>
        </div>
    </form>
</div>
