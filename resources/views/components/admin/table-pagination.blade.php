@props([
    'paginator',
    'turboFrame' => null,
])

@php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolvedTurboFrame = $turboFrame ?? WorkspaceEmbed::turboFrame();
@endphp

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator && $paginator->total() > 0)
    <div class="erp-table-footer flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-600">
            {{ __('Showing :from–:to of :total', [
                'from' => number_format($paginator->firstItem() ?? 0),
                'to' => number_format($paginator->lastItem() ?? 0),
                'total' => number_format($paginator->total()),
            ]) }}
        </p>
        <div class="erp-table-pagination">
            {{ $paginator->withQueryString()->links('pagination.turbo-tailwind', ['turboFrame' => $resolvedTurboFrame]) }}
        </div>
    </div>
@endif
