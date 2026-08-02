<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\HomepageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $sectionId = $request->query('homepage_section_id');

        $datas = Banner::with('homepageSection')
            ->when($sectionId, fn ($q) => $q->where('homepage_section_id', $sectionId))
            ->orderBy('sort_order')
            ->paginate(20);

        $sections = HomepageSection::whereIn('type', ['split_banner', 'hero_slider'])->orderBy('title')->get();

        return view('banners.index', compact('datas', 'sections', 'sectionId'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'homepage_section_id' => 'required|exists:homepage_sections,id',
            'image_path' => 'required|image|max:2048',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $uploadPath = 'uploads/banners/';
            $image->move(public_path($uploadPath), $imageName);
            $imagePath = $uploadPath . $imageName;
        }

        $data = Banner::create([
            'homepage_section_id' => $request->homepage_section_id,
            'image_path' => $imagePath,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'link' => $request->link,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully.',
            'data' => $data,
        ]);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $data = Banner::find($request->id);

        if (! $data) {
            return response()->json([
                'success' => false,
                'message' => 'Banner not found!',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'homepage_section_id' => 'required|exists:homepage_sections,id',
            'image_path' => 'nullable|image|max:2048',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $imagePath = $data->image_path;
        if ($request->hasFile('image_path')) {
            $image = $request->file('image_path');
            $imageName = uniqid() . '.' . $image->getClientOriginalExtension();
            $uploadPath = 'uploads/banners/';
            $image->move(public_path($uploadPath), $imageName);
            $imagePath = $uploadPath . $imageName;
        }

        $data->update([
            'homepage_section_id' => $request->homepage_section_id,
            'image_path' => $imagePath,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'link' => $request->link,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully.',
            'data' => $data,
        ]);
    }

    public function destroy(Request $request, $role, string $id)
    {
        try {
            $data = Banner::find($request->item_id);
            if (! $data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Banner not found!',
                ]);
            }
            $data->delete();
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully.',
        ]);
    }
}
