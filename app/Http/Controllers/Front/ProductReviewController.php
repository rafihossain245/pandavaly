<?php

namespace App\Http\Controllers\Front;

use App\Helpers\FileLimit;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductReviewController extends Controller
{
    /**
     * Photos one review may carry. The product page prints this number in the
     * drop zone and enforces it in the browser, so keep the two in step.
     */
    public const MAX_IMAGES = 3;

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:2000',
            'images' => 'nullable|array|max:' . self::MAX_IMAGES,
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:' . FileLimit::uploadMaxKilobytes(),
        ], [
            'rating.required' => 'Please pick a rating.',
            'images.max' => 'You can attach at most ' . self::MAX_IMAGES . ' images.',
            // Without these the shopper is told about "the images.0 field".
            'images.*.image' => 'Each attachment must be an image.',
            'images.*.mimes' => 'Images must be a JPG, PNG, WEBP or GIF.',
            'images.*.max' => 'Each image must be smaller than ' . FileLimit::humanUploadMax() . '.',
        ]);

        $attributes = [
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'is_approved' => false,
        ];

        // Only write the column when files actually arrived: a buyer editing their
        // review without re-picking photos should keep the ones already attached.
        if ($request->hasFile('images')) {
            $attributes['images'] = collect($request->file('images'))
                ->map(fn (UploadedFile $image) => $this->storeReviewImage($image))
                ->all();
        }

        ProductReview::updateOrCreate(
            [
                'product_id' => $product->id,
                'buyer_id' => Auth::guard('buyer')->id(),
            ],
            $attributes
        );

        Cache::forget("product_details_{$product->slug}");

        return redirect()
            ->route('product.details', $product->slug)
            ->with('success', 'Thank you for your review! It will be visible after moderation.');
    }

    private function storeReviewImage(UploadedFile $file): string
    {
        $folder = 'images/reviews';
        $directory = public_path($folder);

        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException("Could not create the upload folder \"{$folder}\". Check the folder permissions on the server.");
        }

        if (!is_writable($directory)) {
            throw new \RuntimeException("The upload folder \"{$folder}\" is not writable. Set it to permission 755 on the server.");
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
        $filename = uniqid() . Str::random(6) . '.' . $extension;

        $file->move($directory, $filename);

        return $folder . '/' . $filename;
    }
}
