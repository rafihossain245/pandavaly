<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first();

        return view('settings.website_settings', compact('settings'));
    }

    public function store(Request $request)
    {
        $socialRules = [];
        foreach (array_keys(Setting::SOCIAL_PLATFORMS) as $key) {
            // Validated after normalisation, so "facebook.com/x" is accepted too.
            $socialRules[$key] = 'nullable|string|max:255';
        }

        try {
            $request->validate(array_merge([
                'title' => 'required|string|max:255',
                'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'favicon_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
            ], $socialRules), [
                'title.required' => 'The website title is required — it appears in the browser tab.',
                'logo_path.max' => 'The logo must be 2 MB or smaller.',
                'favicon_path.max' => 'The favicon must be 2 MB or smaller.',
            ]);

            $settings = Setting::first();

            $data = $request->only(['title', 'contact_email', 'contact_phone', 'address']);

            foreach (array_keys(Setting::SOCIAL_PLATFORMS) as $key) {
                $data[$key] = $this->normaliseUrl($request->input($key));
            }

            if ($request->hasFile('logo_path')) {
                $data['logo_path'] = $this->storeImage($request->file('logo_path'), $settings?->logo_path);
            }

            if ($request->hasFile('favicon_path')) {
                $data['favicon_path'] = $this->storeImage($request->file('favicon_path'), $settings?->favicon_path);
            }

            if ($settings) {
                $settings->update($data);
            } else {
                Setting::create($data);
            }

            return redirect()->back()->with('success', 'Website settings updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Admins paste "facebook.com/goebazar" as often as a full URL; without a
     * scheme the browser treats it as a relative path and the link 404s on our
     * own domain. Prepend https:// when it is missing.
     */
    private function normaliseUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        return 'https://' . ltrim($value, '/');
    }

    /**
     * Stores under public/images/settings with a unique name, replacing the
     * previous file. The old code moved to a relative "images/" path, which
     * depended on the process working directory.
     */
    private function storeImage(UploadedFile $file, ?string $previousPath): string
    {
        if (! $file->isValid()) {
            throw new \RuntimeException('The image "' . $file->getClientOriginalName() . '" was not uploaded (' . $file->getErrorMessage() . ').');
        }

        $folder = 'images/settings';
        $directory = public_path($folder);

        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new \RuntimeException("Could not create the upload folder \"{$folder}\". Check its permissions on the server.");
        }

        if (! is_writable($directory)) {
            throw new \RuntimeException("The upload folder \"{$folder}\" is not writable. Set it to permission 755 on the server.");
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png');
        $filename = uniqid() . Str::random(6) . '.' . $extension;

        $file->move($directory, $filename);

        // Only remove the old file once the new one is safely in place.
        if ($previousPath && is_file(public_path($previousPath))) {
            @unlink(public_path($previousPath));
        }

        return $folder . '/' . $filename;
    }
}
