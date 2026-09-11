<?php

namespace App\Models\Concerns;

use App\Support\Pagination\PageSizeAwareBuilder;

trait UsesPreferredPageSize
{
    public function newEloquentBuilder($query): PageSizeAwareBuilder
    {
        return new PageSizeAwareBuilder($query);
    }
}
