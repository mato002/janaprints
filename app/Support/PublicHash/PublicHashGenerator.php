<?php

namespace App\Support\PublicHash;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class PublicHashGenerator
{
    private const BASE62_CHARSET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function generate(?int $length = null): string
    {
        $length ??= (int) config('public_hashes.length', 16);

        if ($length < 8) {
            throw new PublicHashValidationException('Public hash length must be at least 8 characters.');
        }

        $charset = $this->charset();
        $charsetLength = strlen($charset);
        $bytes = random_bytes($length);
        $hash = '';

        for ($i = 0; $i < $length; $i++) {
            $hash .= $charset[ord($bytes[$i]) % $charsetLength];
        }

        $this->assertValid($hash);

        return $hash;
    }

    public function isValid(string $hash): bool
    {
        $length = (int) config('public_hashes.length', 16);

        if (strlen($hash) !== $length) {
            return false;
        }

        if (str_contains($hash, '_')) {
            return false;
        }

        if (preg_match('/^[A-Za-z]{2,}_/', $hash) === 1) {
            return false;
        }

        return preg_match($this->pattern($length), $hash) === 1;
    }

    public function assertValid(string $hash): void
    {
        if ($this->isValid($hash)) {
            return;
        }

        $length = (int) config('public_hashes.length', 16);

        if (str_contains($hash, '_') || preg_match('/^[A-Za-z]{2,}_/', $hash) === 1) {
            throw new PublicHashValidationException(
                'Public hash must be a plain token without prefixes or underscores.',
            );
        }

        if (strlen($hash) !== $length) {
            throw new PublicHashValidationException(
                "Public hash must be exactly {$length} characters.",
            );
        }

        throw new PublicHashValidationException(
            'Public hash contains invalid characters. Only base62 is permitted.',
        );
    }

    /**
     * @param  class-string<Model>|Model  $modelClass
     */
    public function generateUnique(Model|string $modelClass, ?string $column = null): string
    {
        $column ??= (string) config('public_hashes.column', 'public_id');
        $modelClass = $modelClass instanceof Model ? $modelClass::class : $modelClass;
        $attempts = (int) config('public_hashes.max_generation_attempts', 5);

        for ($i = 0; $i < $attempts; $i++) {
            $hash = $this->generate();

            $exists = $modelClass::query()->where($column, $hash)->exists();

            if (! $exists) {
                return $hash;
            }
        }

        throw new RuntimeException(
            "Unable to generate a unique public hash for [{$modelClass}] after {$attempts} attempt(s).",
        );
    }

    protected function charset(): string
    {
        return match (config('public_hashes.charset', 'base62')) {
            'base62' => self::BASE62_CHARSET,
            default => throw new PublicHashValidationException(
                'Unsupported public hash charset: '.config('public_hashes.charset'),
            ),
        };
    }

    protected function pattern(int $length): string
    {
        return '/^[0-9A-Za-z]{'.$length.'}$/';
    }
}
