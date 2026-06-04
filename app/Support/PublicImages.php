<?php

namespace App\Support;

class PublicImages
{
    public static function get(string $key): string
    {
        $images = config('public-images', []);

        return $images[$key] ?? $images['default'] ?? 'https://picsum.photos/seed/jp-default/1200/800';
    }
}
