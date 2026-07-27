<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Portal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Fetch portal management data from the database
            $portals = Portal::with('vendor')->latest()->paginate(10); // You can change 10 to any number per page

            // Return the view with portal management data
            return view('dashboard.portal_management.index', compact('portals'));
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return redirect()->back()->withErrors(['error' => 'Failed to fetch portal management data.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
             $vendors = User::whereHas('roles', fn($q) => $q->where('name', 'vendor'))->get();
            // Return the view for creating a new portal
            return view('dashboard.portal_management.create', compact('vendors'));
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return redirect()->back()->withErrors(['error' => 'Failed to load create form.']);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|unique:portals,name',
                'type' => 'required|in:flight,bus,train',
                'vendor_id' => 'nullable|exists:users,id',
            ]);

            Portal::create([
                'name' => $request->name,
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
                'base_url' => $request->base_url,
                'type' => $request->type,
                'vendor_id' => $request->vendor_id,
                'status' => $request->status ?? 'active',
                'created_by' => Auth::id(),
            ]);
            // Redirect to the index with success message
            return redirect()->back()->with('success', 'Portal created successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return redirect()->back()->withErrors(['error' => 'Failed to create portal.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($role, string $id)
    {
        try {
            $vendors = User::whereHas('roles', fn($q) => $q->where('name', 'vendor'))->get();
            // Find the portal by ID
            $portal = Portal::findOrFail($id);
            // Return the view for editing the portal
            return view('dashboard.portal_management.edit', compact('portal', 'vendors'));
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return redirect()->back()->withErrors(['error' => 'Failed to load edit form.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $role, string $id)
    {
        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|unique:portals,name,' . $id,
                'type' => 'required|in:flight,bus,train',
                'vendor_id' => 'nullable|exists:users,id',
            ]);

            $portal = Portal::findOrFail($id);
            $portal->update($request->only([
                'name', 'api_key', 'api_secret', 'base_url',
                'type', 'vendor_id', 'status'
            ]));

            // Redirect to the index with success message
            return redirect()->back()->with('success', 'Portal updated successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return redirect()->back()->withErrors(['error' => 'Failed to update portal.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Find the portal by ID and delete it
            $portal = \App\Models\Portal::findOrFail($id);
            $portal->delete();

            // Redirect to the index with success message
            return redirect()->route('dashboard.portal-management.index')->with('success', 'Portal deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur
            return redirect()->back()->withErrors(['error' => 'Failed to delete portal.']);
        }
    }
}
