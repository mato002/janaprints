<?php

namespace App\Support;

use App\Enums\ArtworkFileType;

class ArtworkFileHelper
{
    /**
     * @var list<string>
     */
    private const ALLOWED_EXTENSIONS = ['pdf', 'ai', 'psd', 'cdr', 'svg', 'png', 'jpg', 'jpeg'];

    public static function typeFromExtension(string $extension): ?ArtworkFileType
    {
        $extension = strtolower($extension);

        return match ($extension) {
            'pdf' => ArtworkFileType::Pdf,
            'ai' => ArtworkFileType::Ai,
            'psd' => ArtworkFileType::Psd,
            'cdr' => ArtworkFileType::Cdr,
            'svg' => ArtworkFileType::Svg,
            'png' => ArtworkFileType::Png,
            'jpg', 'jpeg' => ArtworkFileType::Jpg,
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    public static function allowedExtensions(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }

    public static function mimeRule(): string
    {
        return 'mimes:'.implode(',', self::ALLOWED_EXTENSIONS);
    }
}
