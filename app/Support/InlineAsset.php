<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Reads a public asset so a page can inline it instead of linking it.
 *
 * The funnel's stylesheet is small (~19 KB, ~5 KB over the wire) and is the
 * only CSS the first paint needs, so a separate request for it buys nothing
 * but a round trip on the critical path. Inlined, the first response paints
 * the page on its own.
 *
 * Cached under the file's modification time, so an edit invalidates the entry
 * by changing the key rather than needing a cache clear, and a production
 * request never hits the filesystem twice for the same content.
 */
class InlineAsset
{
    public static function css(string $relativePath): string
    {
        $path = public_path($relativePath);

        if (! is_file($path)) {
            return '';
        }

        return Cache::rememberForever(
            'inline-asset:' . $relativePath . ':' . filemtime($path),
            fn () => self::minify((string) file_get_contents($path))
        );
    }

    /**
     * Comments and the whitespace between rules, which is the bulk of what a
     * hand-written stylesheet carries. Deliberately conservative: it does not
     * touch anything inside a value, so it cannot break a rule the way a
     * regex-based full minifier can.
     */
    private static function minify(string $css): string
    {
        $css = preg_replace('#/\*(?!!).*?\*/#s', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace([' {', '{ ', ' }', '} ', '; ', ' : ', ', '], ['{', '{', '}', '}', ';', ':', ','], $css);

        return trim($css);
    }
}
