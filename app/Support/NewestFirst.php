<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

final class NewestFirst
{
    /**
     * Newest records first for operational registers.
     */
    public static function apply(Builder $query, ?string $table = null): Builder
    {
        $created = $table ? "{$table}.created_at" : 'created_at';
        $id = $table ? "{$table}.id" : 'id';

        return $query->reorder()->orderByDesc($created)->orderByDesc($id);
    }
}
