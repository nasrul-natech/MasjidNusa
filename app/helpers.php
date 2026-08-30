<?php

if (! function_exists('storage_url')) {
    /**
     * Build a public URL for a file stored on the public disk.
     */
    function storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
