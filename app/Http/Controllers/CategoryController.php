<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Category::select('categories.*')->orderBy('categories.name', 'asc');

        if ($request->filled('name')) {
            $query->where('categories.name', $request->name);
        }

        if ($request->filled('is_active')) {
            $query->whereDate('categories.is_active', $request->is_active);
        }

        $datas = $query->paginate(20);

        return view('categories.index', compact('datas'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('categories.create-modal');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255|unique:categories,name',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }
        $simage = $request->file('image_path');
        if ($simage) {
            $image_name = uniqid();
            $ext = strtolower($simage->getClientOriginalExtension());
            $image_full_name = $image_name . '.' . $ext;
            $upload_path = 'images/category/';
            $image_url = $upload_path . $image_full_name;
            $success = $simage->move($upload_path, $image_full_name);
            if ($success) {
                $image_url = $image_url;
            } else {
                $image_url = null;
            }
        }

        $simage = $request->file('icon');
        $icon_url = null;
        $image_url = null;
        if ($simage) {
            $image_name = uniqid();
            $ext = strtolower($simage->getClientOriginalExtension());
            $image_full_name = $image_name . '.' . $ext;
            $upload_path = 'images/category/';
            $icon_url = $upload_path . $image_full_name;
            $success = $simage->move($upload_path, $image_full_name);
            if ($success) {
                $icon_url = $icon_url;
            } else {
                $icon_url = null;
            }
        }

        $data = Category::create([
            'name' => $request->name,
            'icon' => $icon_url,
            'image_path' => $image_url,
            'slug' => \Str::slug($request->name),
            'is_active' => $request->is_active ? 1 : 0
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data created successfully.',
                'data' => $data
            ]);
        }

        return redirect()->route('role.categories.index')->with('success', 'Data created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('categories.edit-modal', compact('id'));
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
        $id = $request->id;
        
        $data = Category::findOrFail($id);

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $data->id
        ]);

        $simage = $request->file('image_path');
        if ($simage) {
            $image_name = uniqid();
            $ext = strtolower($simage->getClientOriginalExtension());
            $image_full_name = $image_name . '.' . $ext;
            $upload_path = 'images/category/';
            $image_url = $upload_path . $image_full_name;
            $success = $simage->move($upload_path, $image_full_name);
            if ($success) {
                $image_url = $image_url;
            } else {
                $image_url = null;
            }
        }

        $icon_url = null;
        $image_url = null;

        $simage = $request->file('icon');
        if ($simage) {
            $image_name = uniqid();
            $ext = strtolower($simage->getClientOriginalExtension());
            $image_full_name = $image_name . '.' . $ext;
            $upload_path = 'images/category/';
            $icon_url = $upload_path . $image_full_name;
            $success = $simage->move($upload_path, $image_full_name);
            if ($success) {
                $icon_url = $icon_url;
            } else {
                $icon_url = null;
            }
        }

        $data->update([
            'name' => $request->name,
            'is_active' => $request->is_active ? 1 : 0,
            'icon' => $icon_url,
            'image_path' => $image_url,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully.',
            'data' => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $item = Category::find($request->item_id);
            if ($item) {
                $item->delete();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Info Not Found!'
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully.'
        ]);
    }
}
