<?php

namespace App\Support;

/**
 * Downscales and re-encodes an image in place, with GD.
 *
 * Phones photograph and product suppliers export at 1000–4000 px, but the
 * storefront never draws a product larger than about 900 px (the funnel's
 * viewer at full width on a retina screen). Shipping the original means a
 * shopper on mobile data downloads several hundred KB per card for pixels the
 * screen cannot show — the "improve image delivery" finding on PageSpeed.
 *
 * The format is kept as uploaded: changing it would change the file extension
 * and every path already stored in the database.
 */
class ImageOptimizer
{
    public const MAX_EDGE = 1000;
    public const QUALITY = 82;

    /**
     * Rewrites the file if it is larger than $maxEdge on its longest side, or
     * if re-encoding at $quality makes it meaningfully smaller.
     *
     * @return array{before:int, after:int, resized:bool}|null  null when the
     *         file cannot be handled (missing, SVG, animated GIF, unsupported).
     */
    public static function optimize(string $path, int $maxEdge = self::MAX_EDGE, int $quality = self::QUALITY): ?array
    {
        if (! is_file($path) || ! is_writable($path)) {
            return null;
        }

        $before = filesize($path);
        $info = @getimagesize($path);

        if (! $info || $info[0] < 1 || $info[1] < 1) {
            return null;
        }

        [$width, $height, $type] = $info;

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => null,
        };

        if (! $image) {
            return null;
        }

        $scale = min(1, $maxEdge / max($width, $height));
        $resized = $scale < 1;

        if ($resized) {
            $target = imagecreatetruecolor((int) round($width * $scale), (int) round($height * $scale));

            // PNG and WebP can carry transparency, which imagecopyresampled
            // would otherwise fill with black.
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
                imagealphablending($target, false);
                imagesavealpha($target, true);
                imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
            }

            imagecopyresampled(
                $target, $image,
                0, 0, 0, 0,
                imagesx($target), imagesy($target), $width, $height
            );

            imagedestroy($image);
            $image = $target;
        }

        // Written to a temporary file first: a failed encode must not leave a
        // truncated image where the original was.
        $temp = $path . '.opt';

        $ok = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $temp, $quality),
            IMAGETYPE_PNG => imagepng($image, $temp, 8),
            IMAGETYPE_WEBP => imagewebp($image, $temp, $quality),
            default => false,
        };

        imagedestroy($image);

        if (! $ok || ! is_file($temp)) {
            @unlink($temp);

            return null;
        }

        $after = filesize($temp);

        // Re-encoding a well-compressed file can make it bigger; keep the
        // original in that case, unless we actually needed the smaller pixels.
        if (! $resized && $after >= $before) {
            @unlink($temp);

            return ['before' => $before, 'after' => $before, 'resized' => false];
        }

        @rename($temp, $path);

        return ['before' => $before, 'after' => $after, 'resized' => $resized];
    }
}
