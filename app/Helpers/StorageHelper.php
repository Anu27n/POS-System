<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class StorageHelper
{
    /**
     * Get the full URL for a storage file.
     * Works correctly in both development and production environments.
     * 
     * @param string|null $path The relative path within the public storage
     * @param string|null $default Optional default image path
     * @return string|null The full URL or null if path is empty
     */
    public static function url(?string $path, ?string $default = null): ?string
    {
        if (empty($path)) {
            return $default;
        }

        // If path is already a full URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Use Storage facade which respects APP_URL configuration
        return Storage::disk('public')->url($path);
    }

    /**
     * Get the full URL for a product image
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function productImage(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for a category image
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function categoryImage(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for a store logo
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function storeLogo(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for a QR code
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function qrCode(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for app branding images (logo, favicon)
     * 
     * @param string|null $path
     * @return string|null
     */
    public static function branding(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Check if a file exists in public storage
     * 
     * @param string|null $path
     * @return bool
     */
    public static function exists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }
}
