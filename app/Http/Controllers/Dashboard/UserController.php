<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Admin accounts — the people who can sign in to this dashboard. Storefront
 * shoppers are Buyers and are managed under Customers.
 *
 * Note the method signatures: the whole admin group is prefixed with {role},
 * so route parameters arrive positionally with the role slug first. Typing the
 * second parameter as $id (and resolving the model by hand) is what the sibling
 * controllers in this group do; taking a single $id silently receives the role
 * slug instead of the record id.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q'));

        $users = User::with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Head-count per role for the summary strip. Roles with nobody in them still
        // show, so an empty "accountant" reads as a fact rather than a missing row.
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('panel.users.index', compact('users', 'roles', 'search'));
    }

    public function create()
    {
        return view('panel.users.create', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'status' => $data['status'],
            // The model's creating hook fills a unique username from the name,
            // so the form does not have to ask for one.
            'password' => $data['password'],
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('role.user.index', ['role' => $this->roleSlug()])
            ->with('success', $user->name . ' can now sign in to the dashboard.');
    }

    public function edit(string $role, string $id)
    {
        return view('panel.users.edit', [
            'user' => User::with('roles')->findOrFail($id),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $role, string $id)
    {
        $user = User::findOrFail($id);
        $data = $this->validated($request, $user);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'status' => $data['status'],
        ]);

        // Blank means "leave the password alone" — an edit form that cleared it
        // would lock the person out every time someone fixed their phone number.
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        // Demoting the last super admin would leave nobody able to manage roles.
        if ($this->wouldOrphanSuperAdmin($user, $data['role'])) {
            return redirect()->route('role.user.index', ['role' => $this->roleSlug()])
                ->with('error', 'Saved, but the role was left unchanged — this is the only super admin.');
        }

        $user->syncRoles([$data['role']]);

        return redirect()->route('role.user.index', ['role' => $this->roleSlug()])
            ->with('success', $user->name . ' updated.');
    }

    public function destroy(string $role, string $id)
    {
        $user = User::findOrFail($id);
        $slug = $this->roleSlug();

        // Two ways this locks everyone out of the admin, so both are refused.
        if ((int) $id === (int) Auth::id()) {
            return redirect()->route('role.user.index', ['role' => $slug])
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->hasRole('super admin') && User::role('super admin')->count() <= 1) {
            return redirect()->route('role.user.index', ['role' => $slug])
                ->with('error', 'This is the only super admin — promote someone else first.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('role.user.index', ['role' => $slug])
            ->with('success', $name . ' has been removed.');
    }

    /**
     * Rules shared by create and edit. On edit the password is optional and the
     * uniqueness checks ignore the record being saved.
     */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role' => ['required', Rule::exists('roles', 'name')],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', 'min:8'],
        ], [
            'role.required' => 'Pick a role — a user with no role cannot reach any screen.',
            'role.exists' => 'That role no longer exists.',
            'password.confirmed' => 'The two passwords do not match.',
        ]);
    }

    /** True when this change would remove the last remaining super admin. */
    private function wouldOrphanSuperAdmin(User $user, string $newRole): bool
    {
        return $user->hasRole('super admin')
            && $newRole !== 'super admin'
            && User::role('super admin')->count() <= 1;
    }

    private function roleSlug(): string
    {
        return Str::slug(Auth::user()->getRoleNames()->first());
    }
}
