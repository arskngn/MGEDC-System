<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesFileUploads
{
    /**
     * Upload a file and return the stored path.
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Delete a file if it exists.
     */
    public function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Get the full URL for a stored file.
     */
    public function getFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        
        return $disk->url($path);
    }
}
