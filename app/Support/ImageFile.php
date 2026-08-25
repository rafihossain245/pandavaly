<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Pixel size of an image in public/, for width/height attributes.
 *
 * An <img> without them has no size until the file itself arrives, so the page
 * reflows around it on load — the layout shift PageSpeed reports. Reading the
 * header is cheap but it is still disk I/O on every request, so the answer is
 * cached under the file's modification time: replacing the image changes the
 * key, and nothing has to be cleared by hand.
 */
class ImageFile
{
    /**
     * @return array{0: int, 1: int}|null  [width, height], or null when the
     *         file is missing or is a format with no intrinsic size.
     */
    public static function dimensions(?string $relativePath): ?array
    {
        if (blank($relativePath)) {
            return null;
        }

        $path = public_path($relativePath);

        if (! is_file($path)) {
            return null;
        }

        return Cache::rememberForever(
            'image-dims:' . $relativePath . ':' . filemtime($path),
            function () use ($path) {
                // SVGs carry no reliable pixel size, and getimagesize() warns on
                // anything it cannot parse rather than returning cleanly.
                $size = @getimagesize($path);

                return $size && $size[0] > 0 && $size[1] > 0
                    ? [(int) $size[0], (int) $size[1]]
                    : null;
            }
        );
    }
}
