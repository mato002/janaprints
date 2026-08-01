<?php

namespace App\Support\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EntityCodeGenerator
{
    public function normalize(string $value, int $maxLength = 50): string
    {
        $normalized = Str::upper(Str::slug(Str::limit(trim($value), $maxLength, ''), '_'));

        if ($normalized === '') {
            $normalized = 'CODE_'.Str::upper(Str::random(4));
        }

        return Str::limit($normalized, $maxLength, '');
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  callable(Builder<Model>): void  $scope
     */
    public function unique(
        string $modelClass,
        string $sourceLabel,
        callable $scope,
        int $maxLength = 50,
        ?int $ignoreId = null,
        ?string $submittedCode = null,
    ): string {
        $base = filled($submittedCode)
            ? $this->normalize($submittedCode, $maxLength)
            : $this->normalize($sourceLabel, $maxLength);

        $code = $base;
        $suffix = 2;

        while ($this->exists($modelClass, $code, $scope, $ignoreId)) {
            $suffixPart = '_'.$suffix;
            $code = Str::limit($base, max(1, $maxLength - strlen($suffixPart)), '').$suffixPart;
            $suffix++;
        }

        return $code;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  callable(Builder<Model>): void  $scope
     */
    protected function exists(string $modelClass, string $code, callable $scope, ?int $ignoreId): bool
    {
        /** @var Builder<Model> $query */
        $query = $modelClass::query()->where('code', $code);
        $scope($query);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
