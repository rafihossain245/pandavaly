<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $attributes = Attribute::paginate(20);
            return view('attributes.index', compact('attributes'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:attributes,code',
            ]);

           $data = Attribute::create([
                'name' => $request->name,
                'code' => $request->code,
                'type' => $request->type,
            ]);

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data created successfully.',
                    'data' => $data
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $role, string $id)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:attributes,code,'.$request->id,
            ]);

            $attribute = Attribute::findOrFail($request->id);
            $attribute->update([
                'name' => $request->name,
                'code' => $request->code,
                'type' => $request->type,
            ]);

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data updated successfully.',
                    'data' => $attribute
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $role, string $id)
    {
        try {
            DB::beginTransaction();
            $attribute = Attribute::findOrFail($request->item_id);
            $attribute->delete();

            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data deleted successfully.'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
