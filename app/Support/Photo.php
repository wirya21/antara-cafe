<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Simpan foto unggahan dalam ukuran yang wajar untuk web. Foto HP biasanya 3-5 MB;
 * setelah diperkecil cukup ratusan KB sehingga menu tetap ringan di sinyal lemah.
 */
class Photo
{
    public static function store(UploadedFile $file, string $directory, int $maxWidth = 1200): string
    {
        $image = extension_loaded('gd') ? self::load($file) : null;

        if (! $image) {
            return $file->store($directory, 'public');
        }

        $image = self::orient($image, $file);
        if (imagesx($image) > $maxWidth) {
            $image = imagescale($image, $maxWidth) ?: $image;
        }

        $webp = function_exists('imagewebp');
        $path = $directory.'/'.Str::random(40).($webp ? '.webp' : '.jpg');

        ob_start();
        $webp ? imagewebp($image, null, 80) : imagejpeg($image, null, 82);
        Storage::disk('public')->put($path, ob_get_clean());
        imagedestroy($image);

        return $path;
    }

    private static function load(UploadedFile $file): ?\GdImage
    {
        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $image) {
            return null;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /** Foto HP sering tersimpan miring; putar sesuai data EXIF. */
    private static function orient(\GdImage $image, UploadedFile $file): \GdImage
    {
        $exif = function_exists('exif_read_data') ? @exif_read_data($file->getRealPath()) : false;

        return match ((int) ($exif['Orientation'] ?? 1)) {
            3 => imagerotate($image, 180, 0) ?: $image,
            6 => imagerotate($image, -90, 0) ?: $image,
            8 => imagerotate($image, 90, 0) ?: $image,
            default => $image,
        };
    }
}
