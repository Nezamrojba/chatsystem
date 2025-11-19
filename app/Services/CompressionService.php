<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Compression service for optimizing file uploads
 * Reduces bandwidth and storage costs
 */
class CompressionService
{
    /**
     * Maximum file size before compression (in bytes)
     */
    private const MAX_SIZE_BEFORE_COMPRESSION = 5 * 1024 * 1024; // 5MB

    /**
     * Compress voice note if needed
     * Returns optimized file path
     */
    public static function compressVoiceNote(UploadedFile $file, string $storagePath): string
    {
        // For now, just store as-is
        // In production, you could use FFmpeg to compress audio
        // Example: ffmpeg -i input.webm -acodec libopus -b:a 32k output.opus
        
        // Check file size
        if ($file->getSize() > self::MAX_SIZE_BEFORE_COMPRESSION) {
            // Log for monitoring - compression can be added later
            \Log::info('Large voice note detected', [
                'size' => $file->getSize(),
                'path' => $storagePath
            ]);
        }

        return $storagePath;
    }

    /**
     * Get optimized file URL
     * Can be used for CDN or compressed versions
     */
    public static function getOptimizedUrl(string $path): string
    {
        // Return standard storage URL
        // In production, could return CDN URL or compressed version URL
        return Storage::disk('public')->url($path);
    }

    /**
     * Validate and optimize image (for future use)
     */
    public static function optimizeImage(UploadedFile $file, string $storagePath): string
    {
        // Future: Use Intervention Image or similar to:
        // - Resize large images
        // - Convert to WebP format
        // - Compress JPEG/PNG
        
        return $storagePath;
    }
}

