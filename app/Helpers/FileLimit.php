<?php

namespace App\Helpers;

/**
 * Exposes PHP's upload ceilings so forms can reject oversized files in the browser
 * instead of letting PHP discard them and leaving the admin staring at a form that
 * did nothing.
 */
class FileLimit
{
    public static function uploadMaxBytes(): int
    {
        return static::toBytes(ini_get('upload_max_filesize')) ?: 2 * 1024 * 1024;
    }

    public static function postMaxBytes(): int
    {
        return static::toBytes(ini_get('post_max_size')) ?: 8 * 1024 * 1024;
    }

    /**
     * The smaller of the two, which is the real per-file ceiling.
     */
    public static function effectiveMaxBytes(): int
    {
        return min(static::uploadMaxBytes(), static::postMaxBytes());
    }

    public static function uploadMaxKilobytes(): int
    {
        return (int) floor(static::effectiveMaxBytes() / 1024);
    }

    public static function humanUploadMax(): string
    {
        return round(static::effectiveMaxBytes() / 1048576, 1) . ' MB';
    }

    /**
     * Converts PHP's shorthand notation ("2M", "8G", "512K") into bytes.
     */
    public static function toBytes(string|false|null $value): int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
