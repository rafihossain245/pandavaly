<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Your code to fetch and display sliders goes here
            $datas = Slider::paginate(10);
            return view('sliders.index', compact('datas'));
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
                'title' => 'required|string|max:255',
                'image_path' => 'required|image|max:2048', // Example validation for image
            ]);

            // Handle file upload
            $image = $request->file('image_path');
            if($image)
            {
                $image_full_name = uniqid() . '.' . $image->getClientOriginalExtension();
                $upload_path = 'uploads/sliders/';
                $image_url = $upload_path.$image_full_name;
                $success = $image->move(($upload_path), $image_full_name);
                // $img = Image::make($image_url)->resize(400, 200)->save();
                if($success)
                {
                    $imagePath = $image_url;
                }else{
                    $imagePath = null;
                }
            }

            $data = Slider::create([
                'title' => $request->title,
                'link' => $request->link,
                'image_path' => $imagePath,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Slider created successfully.',
                    'data' => $data
                ]);
            } 
        } catch (\Exception $e) {
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
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'image_path' => 'nullable|image|max:2048', // Example validation for image
            ]);

            $slider = Slider::findOrFail($request->id);

            // Handle file upload
            $image = $request->file('image_path');
            if($image)
            {
                $image_full_name = uniqid() . '.' . $image->getClientOriginalExtension();
                $upload_path = 'uploads/sliders/';
                $image_url = $upload_path.$image_full_name;
                $success = $image->move(($upload_path), $image_full_name);
                // $img = Image::make($image_url)->resize(400, 200)->save();
                if($success)
                {
                    $slider->image_path = $image_url;
                }
            }

            $slider->title = $request->title;
            $slider->link = $request->link;
            $slider->is_active = $request->has('is_active') ? true : false;
            $slider->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Slider updated successfully.',
                    'data' => $slider
                ]);
            } 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,$role, string $id)
    {
        try {
            $slider = Slider::findOrFail($request->item_id);
            $slider->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Slider deleted successfully.'
                ]);
            } 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
