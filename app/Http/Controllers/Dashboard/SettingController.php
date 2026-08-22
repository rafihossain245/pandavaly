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

        $trackingRules = [];
        $trackingMessages = [];
        foreach (Setting::TRACKING_FIELDS as $key => $field) {
            // Normalised before validation so a lowercased "gtm-abc123" or a
            // stray space from copy-paste is accepted rather than rejected.
            $request->merge([$key => $this->normaliseTrackingId($request->input($key))]);

            // Checked against the platform's own ID format. A pasted <script>
            // block fails here rather than reaching every storefront page.
            $trackingRules[$key] = ['nullable', 'string', 'max:32', 'regex:' . $field['pattern']];
            $trackingMessages[$key . '.regex'] = $field['error'];
        }

        // Funnel wording: every field optional, because blank means "use the
        // line the page shipped with" rather than an empty heading.
        $copyRules = [];
        foreach (array_keys(Setting::LANDING_COPY) as $key) {
            $copyRules[$key] = 'nullable|string|max:255';
        }

        try {
            $request->validate(array_merge([
                'title' => 'required|string|max:255',
                'tagline' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:300',
                'announcement' => 'nullable|string|max:255',
                'logo_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'favicon_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                // Normalised the same way as social links, so a pasted
                // "play.google.com/store/..." without a scheme is accepted.
                'play_store_url' => 'nullable|string|max:255',
                'app_store_url' => 'nullable|string|max:255',
            ], $socialRules, $trackingRules, $copyRules), array_merge([
                'title.required' => 'The website title is required — it appears in the browser tab.',
                'logo_path.max' => 'The logo must be 2 MB or smaller.',
                'favicon_path.max' => 'The favicon must be 2 MB or smaller.',
            ], $trackingMessages));

            $settings = Setting::first();

            $data = $request->only(['title', 'tagline', 'meta_description', 'announcement', 'contact_email', 'contact_phone', 'address']);

            // Stored as null rather than '', so Setting::copy() sees a cleared
            // field as "restore the default wording".
            foreach (array_keys(Setting::LANDING_COPY) as $key) {
                $data[$key] = trim((string) $request->input($key)) ?: null;
            }

            // Unchecked checkboxes are not posted at all, so absence is the value.
            $data['announcement_enabled'] = $request->boolean('announcement_enabled');

            foreach (array_keys(Setting::SOCIAL_PLATFORMS) as $key) {
                $data[$key] = $this->normaliseUrl($request->input($key));
            }

            foreach (['play_store_url', 'app_store_url'] as $key) {
                $data[$key] = $this->normaliseUrl($request->input($key));
            }

            foreach (array_keys(Setting::TRACKING_FIELDS) as $key) {
                // Already normalised above the validator; blank clears the pixel.
                $data[$key] = $request->input($key) ?: null;
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
     * Pixel IDs are pasted, so they arrive with stray whitespace and in mixed
     * case. Google's prefixes are upper case by definition; Meta's is digits.
     */
    private function normaliseTrackingId(mixed $value): ?string
    {
        $value = strtoupper(preg_replace('/\s+/', '', is_string($value) ? $value : ''));

        return $value === '' ? null : $value;
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
