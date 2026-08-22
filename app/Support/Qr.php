<?php

namespace App\Support;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Inline QR codes for printed documents.
 *
 * SVG rather than PNG so the code stays sharp at any print DPI and needs no
 * image extension (GD/Imagick) on the server. Returned as a data: URI so the
 * invoice is a single self-contained file — printing or saving it as PDF never
 * depends on a second request succeeding.
 */
class Qr
{
    public static function svgDataUri(string $text, int $size = 140): string
    {
        $writer = new Writer(new ImageRenderer(
            new RendererStyle($size, 0),
            new SvgImageBackEnd()
        ));

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($text));
    }
}
