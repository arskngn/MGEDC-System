<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;

trait ResolvesCsvUploadPath
{
    protected function csvUploadPath(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if ($path !== false && $path !== '' && is_readable($path)) {
            return $path;
        }

        $fallback = $file->getPathname();

        return ($fallback !== '' && is_readable($fallback)) ? $fallback : ($path ?: $fallback);
    }
}
