<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class StorageHelper
{
    /**
     * Store a file in both storage and public directories (dual-write)
     * This eliminates the need for symlinks on shared hosting.
     *
     * @param UploadedFile $file The uploaded file
     * @param string $folder The folder name (e.g., 'products', 'stores')
     * @param string|null $filename Optional custom filename
     * @return string|false The relative path to the file, or false on failure
     */
    public static function store(UploadedFile $file, string $folder, ?string $filename = null): string|false
    {
        // Generate filename if not provided
        if (!$filename) {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        }

        $relativePath = $folder . '/' . $filename;

        try {
            // 1. Store in Laravel's storage/app/public (standard location)
            $storagePath = Storage::disk('public')->putFileAs($folder, $file, $filename);

            if (!$storagePath) {
                return false;
            }

            // 2. Also copy to public/storage for direct web access (no symlink needed)
            self::copyToPublic($relativePath);

            return $relativePath;
        } catch (\Exception $e) {
            Log::error('StorageHelper::store failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Copy a file from storage/app/public to public/storage
     */
    public static function copyToPublic(string $relativePath): bool
    {
        try {
            $sourcePath = storage_path('app/public/' . $relativePath);
            $destPath = public_path('storage/' . $relativePath);
            $destDir = dirname($destPath);

            // Create directory if it doesn't exist
            if (!File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0755, true, true);
            }

            // Copy the file
            if (File::exists($sourcePath)) {
                return copy($sourcePath, $destPath);
            }

            return false;
        } catch (\Exception $e) {
            Log::warning('StorageHelper::copyToPublic failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a file from both storage and public directories
     *
     * @param string $path The relative path to the file
     * @return bool
     */
    public static function delete(string $path): bool
    {
        $success = true;

        // Delete from storage/app/public
        if (Storage::disk('public')->exists($path)) {
            $success = Storage::disk('public')->delete($path) && $success;
        }

        // Delete from public/storage
        $publicPath = public_path('storage/' . $path);
        if (File::exists($publicPath)) {
            $success = File::delete($publicPath) && $success;
        }

        return $success;
    }

    /**
     * Sync all files from storage/app/public to public/storage
     * Useful for initial setup or after restoring backup
     *
     * @return array Statistics about synced files
     */
    public static function syncAll(): array
    {
        $sourcePath = storage_path('app/public');
        $destPath = public_path('storage');
        
        $stats = [
            'copied' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        if (!File::isDirectory($sourcePath)) {
            return $stats;
        }

        // Create destination if it doesn't exist
        if (!File::isDirectory($destPath)) {
            File::makeDirectory($destPath, 0755, true, true);
        }

        // Get all files recursively
        $files = File::allFiles($sourcePath);
        
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $destFile = $destPath . '/' . $relativePath;
            $destDir = dirname($destFile);

            try {
                // Create directory if needed
                if (!File::isDirectory($destDir)) {
                    File::makeDirectory($destDir, 0755, true, true);
                }

                // Skip if destination is newer or same
                if (File::exists($destFile) && filemtime($destFile) >= filemtime($file->getRealPath())) {
                    $stats['skipped']++;
                    continue;
                }

                // Copy file
                if (copy($file->getRealPath(), $destFile)) {
                    $stats['copied']++;
                } else {
                    $stats['errors']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::warning('StorageHelper sync error: ' . $e->getMessage());
            }
        }

        return $stats;
    }

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
     */
    public static function productImage(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for a category image
     */
    public static function categoryImage(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for a store logo
     */
    public static function storeLogo(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for a QR code
     */
    public static function qrCode(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Get the full URL for app branding images (logo, favicon)
     */
    public static function branding(?string $path): ?string
    {
        return self::url($path);
    }

    /**
     * Check if a file exists in public storage
     */
    public static function exists(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return Storage::disk('public')->exists($path);
    }

    /**
     * Check if the storage symlink exists and works
     */
    public static function hasWorkingSymlink(): bool
    {
        $link = public_path('storage');
        return is_link($link) && is_dir($link);
    }
}

