<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Rebuilds an uploaded picture from its pixels and saves it as a fresh PNG. Anything hidden in the
 * original file (scripts disguised as images, camera location data, other metadata) is left behind.
 */
class ImageSanitizer
{
    /** Pictures larger than this (in pixels on the longest side) are scaled down. */
    private const MAX_SIZE = 1024;

    public function store(UploadedFile $file, string $directory, string $field = 'image'): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if ($source === false) {
            throw ValidationException::withMessages([$field => 'This picture could not be read. Try saving it again as PNG or JPG.']);
        }

        [$width, $height] = [imagesx($source), imagesy($source)];
        $scale = min(1, self::MAX_SIZE / max($width, $height));
        [$newWidth, $newHeight] = [max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale))];

        $clean = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($clean, false);
        imagesavealpha($clean, true);
        imagefill($clean, 0, 0, imagecolorallocatealpha($clean, 0, 0, 0, 127));
        imagecopyresampled($clean, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagepng($clean, null, 6);
        $png = (string) ob_get_clean();
        imagedestroy($source);
        imagedestroy($clean);

        $path = trim($directory, '/').'/'.Str::random(40).'.png';
        Storage::disk('public')->put($path, $png);

        return $path;
    }
}
