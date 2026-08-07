<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->paginate(20);

        // Head-count per role for the summary strip. Roles with nobody in them still
        // show, so an empty "accountant" reads as a fact rather than a missing row.
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('panel.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Logic to show user creation form
        return view('panel.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        dd($request->all());
        // Logic to store a new user
        // Validate and save user data here
        return redirect()->route('role.user.index')->with('success', 'User created successfully.');
    }

    // Other methods like show, edit, update, destroy can be added similarly
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Logic to show a specific user
        return view('panel.users.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Logic to show user edit form
        return view('panel.users.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Logic to update a user
        // Validate and update user data here
        return redirect()->route('role.user.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $role = \Illuminate\Support\Str::slug(Auth::user()->getRoleNames()->first());

        // Two ways this locks everyone out of the admin, so both are refused.
        if ((int) $id === (int) Auth::id()) {
            return redirect()->route('role.user.index', ['role' => $role])
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->hasRole('super admin') && User::role('super admin')->count() <= 1) {
            return redirect()->route('role.user.index', ['role' => $role])
                ->with('error', 'This is the only super admin — promote someone else first.');
        }

        $user->delete();

        return redirect()->route('role.user.index', ['role' => $role])
            ->with('success', 'User deleted successfully.');
    }

}
