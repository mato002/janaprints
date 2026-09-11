<?php

namespace App\Support\Pagination;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class PageSizeAwareBuilder extends Builder
{
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
    {
        return parent::paginate(
            $this->preferredPerPage($perPage),
            $columns,
            $pageName,
            $page,
            $total,
        );
    }

    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return parent::simplePaginate(
            $this->preferredPerPage($perPage),
            $columns,
            $pageName,
            $page,
        );
    }

    protected function preferredPerPage(mixed $perPage): mixed
    {
        if ($perPage instanceof Closure) {
            return $perPage;
        }

        if (is_numeric($perPage) && (int) $perPage > 100) {
            return $perPage;
        }

        return PreferredPageSize::current() ?? $perPage;
    }
}
