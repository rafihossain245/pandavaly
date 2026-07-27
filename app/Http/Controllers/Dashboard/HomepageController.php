<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomePageSetting;

class HomepageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return view('dashboard.homepage.index');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
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
            // Validation can be added here
            $request->validate([
                'home_banner_one' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'home_banner_two' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'featured_title_one' => 'nullable|string|max:255',
                'featured_description_one' => 'nullable|string',
                'featured_icon_one' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'featured_title_two' => 'nullable|string|max:255',
                'featured_description_two' => 'nullable|string',
                'featured_icon_two' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'featured_title_three' => 'nullable|string|max:255',
                'featured_description_three' => 'nullable|string',
                'featured_icon_three' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            // Logic to store homepage settings
            $homePageSettings = new HomePageSetting();
            $simage = $request->file('home_banner_one');
            if ($simage) {
                $image_name = uniqid();
                $ext = strtolower($simage->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/home-page/';
                $image_url = $upload_path . $image_full_name;
                $success = $simage->move($upload_path, $image_full_name);
                if ($success) {
                    $homePageSettings->home_banner_one = $image_url;
                }
            }
            $simage2 = $request->file('home_banner_two');
            if ($simage2) {
                $image_name = uniqid();
                $ext = strtolower($simage2->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/home-page/';
                $image_url = $upload_path . $image_full_name;
                $success = $simage2->move($upload_path, $image_full_name);
                if ($success) {
                    $homePageSettings->home_banner_two = $image_url;
                }
            }

            $homePageSettings->featured_title_one = $request->input('featured_title_one');
            $homePageSettings->featured_description_one = $request->input('featured_description_one');
            $icon1 = $request->file('featured_icon_one');
            if ($icon1) {
                $image_name = uniqid();
                $ext = strtolower($icon1->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/home-page/';
                $image_url = $upload_path . $image_full_name;
                $success = $icon1->move($upload_path, $image_full_name);
                if ($success) {
                    $homePageSettings->featured_icon_one = $image_url;
                }
            }
            $homePageSettings->featured_title_two = $request->input('featured_title_two');
            $homePageSettings->featured_description_two = $request->input('featured_description_two');
            $icon2 = $request->file('featured_icon_two');
            if ($icon2) {
                $image_name = uniqid();
                $ext = strtolower($icon2->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/home-page/';
                $image_url = $upload_path . $image_full_name;
                $success = $icon2->move($upload_path, $image_full_name);
                if ($success) {
                    $homePageSettings->featured_icon_two = $image_url;
                }
            }
            $homePageSettings->featured_title_three = $request->input('featured_title_three');
            $homePageSettings->featured_description_three = $request->input('featured_description_three');
            $icon3 = $request->file('featured_icon_three');
            if ($icon3) {
                $image_name = uniqid();
                $ext = strtolower($icon3->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/home-page/';
                $image_url = $upload_path . $image_full_name;
                $success =  request()->file('featured_icon_three')->move($upload_path,  request()->file('featured_icon_three')->getClientOriginalName());
                if ($success) {
                    return redirect()->back()->with('success', 'Homepage settings updated successfully.');
                }
            }
            $homePageSettings->featured_title_four = $request->input('featured_title_four');
            $homePageSettings->featured_description_four = $request->input('featured_description_four');
            $icon4 = $request->file('featured_icon_four');
            if ($icon4) {
                $image_name = uniqid();
                $ext = strtolower($icon4->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/home-page/';
                $image_url = $upload_path . $image_full_name;
                $success =  request()->file('featured_icon_four')->move($upload_path,  request()->file('featured_icon_four')->getClientOriginalName());
                if ($success) {
                    return redirect()->back()->with('success', 'Homepage settings updated successfully.');
                }
            }
            
            $homePageSettings->save();

            return redirect()->back()->with('success', 'Homepage settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
