@php
    use App\Support\Commercial\CommercialApprovalQueueService;
    use App\Support\Navigation\WorkspaceEmbed;
    use App\Support\Sales\SalesDeskViews;

    $workspace = $approvals ?? [];
    $filters = $workspace['filters'] ?? ['tab' => 'pending', 'type' => 'all', 'q' => '', 'branch_id' => null, 'requested_by' => null, 'date_from' => null, 'date_to' => null];
    $counts = $workspace['counts'] ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'all' => 0];
    $pendingSummary = $workspace['pending_summary'] ?? ['quotation' => 0, 'sales_order' => 0, 'artwork' => 0, 'total' => 0];
    $items = $workspace['items'] ?? null;
    $branches = $workspace['branches'] ?? collect();
    $requesters = $workspace['requesters'] ?? collect();
    $activeTab = $filters['tab'] ?? CommercialApprovalQueueService::TAB_PENDING;
    $deskFrame = WorkspaceEmbed::turboFrame();

    $tabUrl = function (string $tab) use ($filters): string {
        return WorkspaceEmbed::url(SalesDeskViews::deskUrl(SalesDeskViews::APPROVALS, array_filter([
            'tab' => $tab,
            'type' => ($filters['type'] ?? 'all') !== 'all' ? $filters['type'] : null,
            'q' => filled($filters['q'] ?? null) ? $filters['q'] : null,
            'branch_id' => $filters['branch_id'] ?? null,
            'requested_by' => $filters['requested_by'] ?? null,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
        ], fn ($value) => $value !== null && $value !== '')));
    };
@endphp

@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Sales approvals')" :description="__('Needs attention now — then searchable approval history.')" />
@else
    <div class="mb-3">
        <h2 class="text-sm font-semibold text-erp-primary">{{ $registerTitle ?? __('Sales approvals') }}</h2>
        @if (! empty($registerDescription))
            <p class="text-xs text-slate-600">{{ $registerDescription }}</p>
        @endif
    </div>
@endif

{{-- Status tabs + search --}}
<div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <nav class="flex flex-wrap gap-2" aria-label="{{ __('Approval status') }}">
        @foreach ([
            CommercialApprovalQueueService::TAB_PENDING => [__('Pending'), $counts['pending']],
            CommercialApprovalQueueService::TAB_APPROVED => [__('Approved'), $counts['approved']],
            CommercialApprovalQueueService::TAB_REJECTED => [__('Rejected'), $counts['rejected']],
            CommercialApprovalQueueService::TAB_ALL => [__('All'), $counts['all']],
        ] as $tab => [$label, $count])
            <a
                href="{{ $tabUrl($tab) }}"
                data-turbo-frame="{{ $deskFrame }}"
                data-turbo-action="advance"
                @class([
                    'inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition',
                    'border-erp-accent bg-erp-accent text-white' => $activeTab === $tab,
                    'border-slate-200 bg-white text-slate-700 hover:border-slate-300' => $activeTab !== $tab,
                ])
            >
                <span>{{ $label }}</span>
                <span @class([
                    'rounded-full px-1.5 py-0.5 text-[10px] tabular-nums',
                    'bg-white/20 text-white' => $activeTab === $tab,
                    'bg-slate-100 text-slate-600' => $activeTab !== $tab,
                ])>{{ number_format($count) }}</span>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('admin.sales.desk') }}" class="flex w-full max-w-md gap-2" data-turbo-frame="{{ $deskFrame }}" data-turbo-action="advance">
        <input type="hidden" name="view" value="approvals">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        @foreach (['type', 'branch_id', 'requested_by', 'date_from', 'date_to'] as $hidden)
            @if (filled($filters[$hidden] ?? null) && ($hidden !== 'type' || $filters[$hidden] !== 'all'))
                <input type="hidden" name="{{ $hidden }}" value="{{ $filters[$hidden] }}">
            @endif
        @endforeach
        <label class="sr-only" for="approvals-search">{{ __('Search approvals') }}</label>
        <input
            id="approvals-search"
            type="search"
            name="q"
            value="{{ $filters['q'] ?? '' }}"
            placeholder="{{ __('Search document, customer…') }}"
            class="erp-input w-full text-sm"
        >
        <button type="submit" class="erp-btn-secondary shrink-0 text-sm">{{ __('Search') }}</button>
    </form>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.sales.desk') }}" class="mb-4 rounded-lg border border-erp-border bg-white p-3" data-turbo-frame="{{ $deskFrame }}" data-turbo-action="advance">
    <input type="hidden" name="view" value="approvals">
    <input type="hidden" name="tab" value="{{ $activeTab }}">
    @if (filled($filters['q'] ?? null))
        <input type="hidden" name="q" value="{{ $filters['q'] }}">
    @endif

    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Filters') }}</div>
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
        <div>
            <label class="mb-1 block text-xs text-slate-500" for="approvals-type">{{ __('Type') }}</label>
            <select id="approvals-type" name="type" class="erp-select w-full text-sm">
                <option value="all" @selected(($filters['type'] ?? 'all') === 'all')>{{ __('All') }}</option>
                <option value="quotation" @selected(($filters['type'] ?? '') === 'quotation')>{{ __('Quote') }}</option>
                <option value="sales_order" @selected(($filters['type'] ?? '') === 'sales_order')>{{ __('Order') }}</option>
                <option value="artwork" @selected(($filters['type'] ?? '') === 'artwork')>{{ __('Artwork') }}</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500" for="approvals-branch">{{ __('Branch') }}</label>
            <select id="approvals-branch" name="branch_id" class="erp-select w-full text-sm">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected((int) ($filters['branch_id'] ?? 0) === (int) $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500" for="approvals-requester">{{ __('Requested by') }}</label>
            <select id="approvals-requester" name="requested_by" class="erp-select w-full text-sm">
                <option value="">{{ __('Anyone') }}</option>
                @foreach ($requesters as $requester)
                    <option value="{{ $requester->id }}" @selected((int) ($filters['requested_by'] ?? 0) === (int) $requester->id)>{{ $requester->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500" for="approvals-date-from">{{ __('From') }}</label>
            <input id="approvals-date-from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-input w-full text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500" for="approvals-date-to">{{ __('To') }}</label>
            <input id="approvals-date-to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-input w-full text-sm">
        </div>
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        <button type="submit" class="erp-btn-primary text-sm">{{ __('Apply filters') }}</button>
        <a
            href="{{ WorkspaceEmbed::url(SalesDeskViews::deskUrl(SalesDeskViews::APPROVALS, ['tab' => $activeTab])) }}"
            class="erp-btn-secondary text-sm"
            data-turbo-frame="{{ $deskFrame }}"
            data-turbo-action="advance"
        >{{ __('Clear') }}</a>
    </div>
</form>

@if ($activeTab === CommercialApprovalQueueService::TAB_PENDING)
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
        <p class="text-sm font-semibold text-amber-950">{{ __('Needs attention') }}</p>
        <p class="mt-1 text-sm text-amber-900">
            @if (($pendingSummary['total'] ?? 0) === 0)
                {{ __('Nothing currently requires your approval.') }}
            @else
                {{ __(':count awaiting approval', ['count' => number_format($pendingSummary['total'])]) }}
                —
                {{ __(':count quotations', ['count' => $pendingSummary['quotation']]) }},
                {{ __(':count sales orders', ['count' => $pendingSummary['sales_order']]) }},
                {{ __(':count artwork', ['count' => $pendingSummary['artwork']]) }}
            @endif
        </p>
    </div>
@endif

@include('admin.commercial.approvals.partials.workspace-table', [
    'items' => $items,
    'activeTab' => $activeTab,
    'canAction' => $canAction ?? false,
    'canApproveQuotations' => $canApproveQuotations ?? false,
    'canRejectQuotations' => $canRejectQuotations ?? false,
    'canConfirmOrders' => $canConfirmOrders ?? false,
    'canApproveArtwork' => $canApproveArtwork ?? false,
])
