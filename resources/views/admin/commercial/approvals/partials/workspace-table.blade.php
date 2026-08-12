@props([
    'items',
    'activeTab' => 'pending',
    'canAction' => false,
    'canApproveQuotations' => false,
    'canRejectQuotations' => false,
    'canConfirmOrders' => false,
    'canApproveArtwork' => false,
])

@php
    use App\Support\Commercial\CommercialApprovalQueueService;

    $rows = $items instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($items->items())
        : collect($items ?? []);
    $isPending = $activeTab === CommercialApprovalQueueService::TAB_PENDING;
@endphp

<section class="rounded-lg border border-erp-border bg-white">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-erp-border px-4 py-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-900">
                {{ $isPending ? __('Pending') : __('Approval history') }}
            </h3>
            <p class="text-xs text-slate-500">
                @if ($items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    {{ __(':count items', ['count' => number_format($items->total())]) }}
                    @unless ($isPending)
                        · {{ __('Newest first') }}
                    @else
                        · {{ __('Oldest / most urgent first') }}
                    @endunless
                @else
                    {{ __(':count items', ['count' => number_format($rows->count())]) }}
                @endif
            </p>
        </div>
    </div>

    <div class="divide-y divide-slate-100">
        @forelse ($rows as $row)
            <article class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-slate-600">
                            {{ $row['type_label'] ?? str($row['type'])->headline() }}
                        </span>
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $row['document'] }}</p>
                        <span class="text-xs text-slate-500">{{ $row['status_label'] }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-600">
                        <span>{{ $row['customer'] }}</span>
                        <span class="tabular-nums">{{ $row['amount'] !== '—' ? 'KES '.$row['amount'] : '—' }}</span>
                        <span>{{ __('Submitted by :name', ['name' => $row['requested_by']]) }}</span>
                        <span>{{ $row['submitted_at']?->format('d M Y') ?? '—' }}</span>
                        <span class="text-slate-500">{{ $row['age_label'] ?? (($row['age_days'] ?? 0).'d') }}</span>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ $row['view_url'] }}" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main" data-turbo-action="advance">{{ __('View') }}</a>
                    @if ($canAction && $isPending && $row['approve_url'])
                        @if (($row['type'] === 'quotation' && $canApproveQuotations) || ($row['type'] === 'sales_order' && $canConfirmOrders) || ($row['type'] === 'artwork' && $canApproveArtwork))
                            <form method="POST" action="{{ $row['approve_url'] }}" class="inline">
                                @csrf
                                <button type="submit" class="erp-btn-primary text-xs">
                                    {{ $row['type'] === 'sales_order' ? __('Confirm') : __('Approve') }}
                                </button>
                            </form>
                        @endif
                    @endif
                    @if ($canAction && $isPending && $row['reject_url'] && $row['type'] === 'quotation' && $canRejectQuotations)
                        <form method="POST" action="{{ $row['reject_url'] }}" class="inline">
                            @csrf
                            <button type="submit" class="erp-btn-secondary text-xs text-red-600">{{ __('Reject') }}</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="px-4 py-10 text-center">
                @if ($isPending)
                    <p class="text-sm font-medium text-slate-900">{{ __('Nothing currently requires your approval.') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('New quotations, draft orders, and submitted artwork will appear here.') }}</p>
                @else
                    <p class="text-sm font-medium text-slate-900">{{ __('No matching approval history.') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Try another tab, search term, or filter.') }}</p>
                @endif
            </div>
        @endforelse
    </div>

    @if ($items instanceof \Illuminate\Contracts\Pagination\Paginator)
        <x-admin.table-pagination :paginator="$items" />
    @endif
</section>
