<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageCategory;

class PageCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
     try {
         $datas = PageCategory::paginate(10);
         return view('page_categories.index', compact('datas'));
     } catch (\Exception $e) {
         return redirect()->back()->with('error', $e->getMessage());
     }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            PageCategory::create([
                'name' => $request->name,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return response()->json(['success' => true, 'message' => 'Page Category created successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
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
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $pageCategory = PageCategory::findOrFail($request->id);
            $pageCategory->update([
                'name' => $request->name,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return response()->json(['success' => true, 'message' => 'Page Category updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $pageCategory = PageCategory::findOrFail($request->item_id);
            $pageCategory->delete();

            return response()->json(['success' => true, 'message' => 'Page Category deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
