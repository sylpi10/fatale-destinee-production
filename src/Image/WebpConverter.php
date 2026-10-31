<?php

declare(strict_types=1);

namespace App\Image;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Re-encodes images to WebP with GD, shrinking those wider or taller than MAX_SIZE.
 *
 * Invokable with the signature of EasyAdmin's "upload_new" form option, so an
 * ImageField stores the converted file instead of the uploaded one.
 */
final class WebpConverter
{
    public const int MAX_SIZE = 2400;
    /** GD can't tune libwebp (no sharp YUV): below 95 it blurs dark areas, which stage photos are full of. */
    public const int QUALITY = 95;

    public function __invoke(UploadedFile $file, string $uploadDir, string $fileName): void
    {
        $this->convert($file->getPathname(), rtrim($uploadDir, '/').'/'.$fileName);
    }

    public function convert(string $source, string $target): void
    {
        $contents = file_get_contents($source);
        $image = false !== $contents ? @imagecreatefromstring($contents) : false;
        if (false === $image) {
            throw new \RuntimeException(\sprintf('"%s" is not an image GD can read.', $source));
        }

        if (!is_dir(\dirname($target))) {
            mkdir(\dirname($target), 0o775, true);
        }

        // A WebP that needs no resizing is kept as is: re-encoding a lossy file only loses quality.
        if (IMAGETYPE_WEBP === @exif_imagetype($source) && max(imagesx($image), imagesy($image)) <= self::MAX_SIZE) {
            if (!copy($source, $target)) {
                throw new \RuntimeException(\sprintf('Could not write "%s".', $target));
            }

            return;
        }

        // WebP has no palette mode.
        imagepalettetotruecolor($image);
        $image = $this->shrink($this->applyExifOrientation($image, $source));
        // Keeps the transparency of PNG and GIF files.
        imagealphablending($image, false);
        imagesavealpha($image, true);

        if (!imagewebp($image, $target, self::QUALITY)) {
            throw new \RuntimeException(\sprintf('Could not write "%s".', $target));
        }
    }

    /**
     * Phone photos are stored sideways with an EXIF flag telling viewers to rotate them;
     * GD ignores that flag and WebP drops it, so the rotation is applied to the pixels.
     */
    private function applyExifOrientation(\GdImage $image, string $source): \GdImage
    {
        if (!\function_exists('exif_read_data') || IMAGETYPE_JPEG !== @exif_imagetype($source)) {
            return $image;
        }

        $orientation = (int) (@exif_read_data($source)['Orientation'] ?? 1);
        if (\in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }
        $angle = match ($orientation) {
            3, 4 => 180,
            5, 6 => 270,
            7, 8 => 90,
            default => 0,
        };

        return 0 === $angle ? $image : imagerotate($image, $angle, 0);
    }

    private function shrink(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if (max($width, $height) <= self::MAX_SIZE) {
            return $image;
        }

        $ratio = self::MAX_SIZE / max($width, $height);
        $scaled = imagecreatetruecolor((int) round($width * $ratio), (int) round($height * $ratio));
        // Copies the alpha channel as is instead of blending it onto the black canvas.
        imagealphablending($scaled, false);
        // Resampling averages the source pixels: sharper than imagescale() when dividing the size by 2 or more.
        imagecopyresampled($scaled, $image, 0, 0, 0, 0, imagesx($scaled), imagesy($scaled), $width, $height);

        return $scaled;
    }
}
