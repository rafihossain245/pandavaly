{{--
    Shared by create and edit. $target is null when creating.

    $permissionGroups is [module => Collection<Permission>], so each card is one
    module with its own tick-all box.
--}}
@php
    $target = $target ?? null;
    $assigned = $assigned ?? [];
    $isProtected = $isProtected ?? false;
    $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());
    $checked = old('permissions', $assigned);
@endphp

<div class="mx-auto max-w-4xl">
    <div class="mb-5 overflow-hidden rounded-xl shadow-sm">
        <div class="flex items-center justify-between bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
            <h2 class="text-xl font-semibold capitalize text-white">
                <i class="fas fa-shield-halved mr-2"></i>
                {{ $target ? 'Edit ' . $target->name : 'Add role' }}
            </h2>
            <a href="{{ route('role.roles.index', ['role' => $roleSlug]) }}"
                class="rounded-lg bg-white/10 px-3 py-1.5 text-sm text-white transition hover:bg-white/20">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
        action="{{ $target
            ? route('role.roles.update', ['role' => $roleSlug, 'roles' => $target->id])
            : route('role.roles.store', ['role' => $roleSlug]) }}">
        @csrf
        @if ($target)
            @method('PUT')
        @endif

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Name</h3>
            </div>
            <div class="p-5">
                <input type="text" name="name" id="name" value="{{ old('name', $target?->name) }}" required
                    placeholder="e.g. stock manager" @disabled($isProtected)
                    class="block w-full max-w-md rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500">
                @if ($isProtected)
                    {{-- Disabled inputs are not submitted, and the controller ignores
                         the name for built-ins anyway; this keeps the field present. --}}
                    <input type="hidden" name="name" value="{{ $target->name }}">
                    <p class="mt-1 text-xs text-gray-500">
                        Built-in role. The name is fixed because sign-in checks it, but its permissions can still change.
                    </p>
                @else
                    <p class="mt-1 text-xs text-gray-500">Lower-case letters, numbers and spaces.</p>
                @endif
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-base font-semibold text-gray-900">Permissions</h3>
                <p class="mt-1 text-xs text-gray-500">Tick what this role may do.</p>
            </div>

            <div class="p-5">
                @forelse ($permissionGroups as $module => $permissions)
                    <div class="mb-4 rounded-lg border border-gray-200 last:mb-0">
                        <label class="flex cursor-pointer items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-2.5">
                            <input type="checkbox" class="perm-group-toggle h-4 w-4 rounded border-gray-300 text-blue-600">
                            <span class="text-sm font-semibold capitalize text-gray-800">{{ str_replace('-', ' ', $module) }}</span>
                            <span class="text-xs text-gray-400">({{ $permissions->count() }})</span>
                        </label>
                        <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($permissions as $permission)
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                        class="perm-check h-4 w-4 rounded border-gray-300 text-blue-600"
                                        @checked(in_array($permission->name, $checked, true))>
                                    <span>{{ Str::contains($permission->name, '.') ? Str::after($permission->name, '.') : $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-gray-500">
                        No permissions exist in this installation yet, so there is nothing to grant.
                        The role can still be created and assigned to people.
                    </p>
                @endforelse
            </div>
        </div>

        <div class="mt-4 flex items-center justify-end gap-2">
            <a href="{{ route('role.roles.index', ['role' => $roleSlug]) }}"
                class="rounded-lg border border-gray-300 px-5 py-2 text-sm text-gray-700 transition hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                <i class="fas fa-floppy-disk mr-1"></i> {{ $target ? 'Save changes' : 'Create role' }}
            </button>
        </div>
    </form>
</div>

{{-- Inline rather than pushed to a stack: this layout has no stack, and the
     checkboxes above are already in the DOM by the time this runs. --}}
<script>
    (function () {
        document.querySelectorAll('.perm-group-toggle').forEach(function (toggle) {
            var boxes = toggle.closest('.rounded-lg').querySelectorAll('.perm-check');

            // Reflect the group's starting state, so a fully ticked module opens ticked.
            var allOn = boxes.length > 0 && Array.prototype.every.call(boxes, function (b) { return b.checked; });
            toggle.checked = allOn;

            toggle.addEventListener('change', function () {
                boxes.forEach(function (box) { box.checked = toggle.checked; });
            });

            boxes.forEach(function (box) {
                box.addEventListener('change', function () {
                    toggle.checked = Array.prototype.every.call(boxes, function (b) { return b.checked; });
                });
            });
        });
    })();
</script>
