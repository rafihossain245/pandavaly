<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Brand;
use App\Models\SalaryTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Brand::select('brands.*')->orderBy('brands.name', 'asc');

        if ($request->filled('name')) {
            $query->where('brands.name', $request->name);
        }

        if ($request->filled('is_active')) {
            $query->whereDate('brands.is_active', $request->is_active);
        }

        $datas = $query->paginate(20);        

        return view('brands.index', compact('datas'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('brands.create-modal');
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
            'name' => 'required',
            'image' => 'nullable|image|max:2048',
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

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $uploadPath = 'images/brands/';
            $image->move(public_path($uploadPath), $imageName);
            $imagePath = $uploadPath . $imageName;
        }

        $data = Brand::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'image' => $imagePath,
            'is_active' => $request->is_active ? 1 : 0
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data created successfully.',
                'data' => $data
            ]);
        }

        return redirect()->route('role.brands.index')->with('success', 'Data created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('brands.edit-modal', compact('id'));
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
        $data = Brand::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = $data->image;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $uploadPath = 'images/brands/';
            $image->move(public_path($uploadPath), $imageName);
            $imagePath = $uploadPath . $imageName;
        }

        $data->update([
            'name' => $request->name,
            'image' => $imagePath,
            'is_active' => $request->is_active ? 1 : 0
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
            $item = Brand::find($request->item_id);
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
