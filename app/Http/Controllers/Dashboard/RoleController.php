<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Roles and what each one may do.
 *
 * Permissions are grouped by the module they belong to — the part before the
 * dot in "orders.view" — so the form reads as a list of modules rather than a
 * flat wall of a hundred checkboxes.
 *
 * As elsewhere in this admin group, route parameters arrive with the {role}
 * prefix slug first, so the record id is the second argument.
 */
class RoleController extends Controller
{
    /**
     * Roles that keep the shop running. They can be edited but never deleted:
     * "super admin" is the only role that can reach this screen, and the login
     * middleware lists the others by name.
     */
    private const PROTECTED_ROLES = ['super admin', 'admin', 'vendor', 'agent'];

    public function index()
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get();

        return view('panel.roles.index', [
            'roles' => $roles,
            'protectedRoles' => self::PROTECTED_ROLES,
            'permissionCount' => Permission::count(),
        ]);
    }

    public function create()
    {
        return view('panel.roles.create', [
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('role.roles.index', ['role' => $this->roleSlug()])
            ->with('success', 'Role "' . $role->name . '" created.');
    }

    public function edit(string $role, string $id)
    {
        $target = Role::with('permissions')->findOrFail($id);

        return view('panel.roles.edit', [
            'target' => $target,
            'permissionGroups' => $this->permissionGroups(),
            'assigned' => $target->permissions->pluck('name')->all(),
            'isProtected' => in_array($target->name, self::PROTECTED_ROLES, true),
        ]);
    }

    public function update(Request $request, string $role, string $id)
    {
        $target = Role::findOrFail($id);
        $data = $this->validated($request, $target);

        // Renaming a built-in role would break the login middleware, which
        // matches on the name. Permissions on it are still editable.
        if (! in_array($target->name, self::PROTECTED_ROLES, true)) {
            $target->name = $data['name'];
            $target->save();
        }

        $target->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('role.roles.index', ['role' => $this->roleSlug()])
            ->with('success', 'Role "' . $target->name . '" updated.');
    }

    public function destroy(string $role, string $id)
    {
        $target = Role::withCount('users')->findOrFail($id);
        $slug = $this->roleSlug();

        if (in_array($target->name, self::PROTECTED_ROLES, true)) {
            return redirect()->route('role.roles.index', ['role' => $slug])
                ->with('error', '"' . $target->name . '" is a built-in role and cannot be deleted.');
        }

        // Deleting a role in use would leave those people unable to open a
        // single screen, with no obvious sign of why.
        if ($target->users_count > 0) {
            return redirect()->route('role.roles.index', ['role' => $slug])
                ->with('error', $target->users_count . ' user(s) still have this role — move them first.');
        }

        $name = $target->name;
        $target->delete();

        return redirect()->route('role.roles.index', ['role' => $slug])
            ->with('success', 'Role "' . $name . '" deleted.');
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:60',
                'regex:/^[a-z][a-z0-9 ]*$/',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'name')],
        ], [
            'name.regex' => 'Use lower-case letters, numbers and spaces only, e.g. "stock manager".',
            'name.unique' => 'A role with that name already exists.',
        ]);
    }

    /**
     * Permissions keyed by module. "orders.view" and "orders.edit" group under
     * "orders"; a permission with no dot lands in "general".
     *
     * @return array<string, \Illuminate\Support\Collection>
     */
    private function permissionGroups(): array
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => Str::contains($permission->name, '.')
                ? Str::before($permission->name, '.')
                : 'general')
            ->all();
    }

    private function roleSlug(): string
    {
        return Str::slug(Auth::user()->getRoleNames()->first());
    }
}
