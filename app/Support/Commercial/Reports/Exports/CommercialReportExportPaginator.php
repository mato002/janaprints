<?php

namespace App\Support\Commercial\Reports\Exports;

use Generator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommercialReportExportPaginator
{
    /**
     * Stream all pages from a paginated report query without loading the full dataset at once.
     *
     * @param  callable(int): LengthAwarePaginator  $paginatorFactory
     * @return Generator<int, array<string, string>>
     */
    public static function yieldPages(callable $paginatorFactory): Generator
    {
        $page = 1;

        do {
            $paginator = $paginatorFactory($page);

            foreach ($paginator->items() as $item) {
                yield is_array($item) ? $item : (array) $item;
            }

            $lastPage = $paginator->lastPage();
            $page++;
        } while ($page <= $lastPage);
    }

    /**
     * @param  iterable<int, array<string, string>>  $rows
     */
    public static function yieldArray(iterable $rows): Generator
    {
        foreach ($rows as $row) {
            yield is_array($row) ? $row : (array) $row;
        }
    }
}
