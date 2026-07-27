<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('settings.website_settings', compact('settings'));
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'favicon_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
            ]);

            $settings = Setting::first();

            $data = $request->only(['title', 'contact_email', 'contact_phone', 'address']);

            $simage = $request->file('logo_path');
            if ($simage) {
                $image_name = uniqid();
                $ext = strtolower($simage->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/';
                $image_url = $upload_path . $image_full_name;
                $success = $simage->move($upload_path, $image_full_name);
                $data['logo_path'] = $image_url;
            }

            $simage = $request->file('favicon_path');
            if ($simage) {
                $image_name = uniqid();
                $ext = strtolower($simage->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'images/';
                $image_url = $upload_path . $image_full_name;
                $success = $simage->move($upload_path, $image_full_name);
                $data['favicon_path'] = $image_url;
            }

            if ($settings) {
                $settings->update($data);
            } else {
                Setting::create($data);
            }

            return redirect()->back()->with('success', 'Website settings updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        
    }
}
