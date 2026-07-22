@php
    $operatorMode = (bool) ($operatorMode ?? false);
    $hasPendingWork = $draftReceipts->isNotEmpty() || $draftIssues->isNotEmpty() || $openCounts->isNotEmpty();
@endphp

<x-admin-layout
    :title="__('Store Desk')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Store Desk')]]
        : [
            ['label' => __('Supply Chain'), 'url' => $fullSupplyChainDeskUrl],
            ['label' => __('Store Desk')],
        ]"
>
    <div class="store-desk-shell">
        <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-erp-primary">{{ __('Store desk') }}</p>
                <p class="text-xs text-slate-600">{{ __('Receive, issue, and post stock from one screen — forms open in modals.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if (! $operatorMode)
                    <a href="{{ $fullSupplyChainDeskUrl }}" class="erp-btn-secondary text-xs" data-turbo-frame="_top">{{ __('Full Supply Chain desk') }}</a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Summary strip --}}
        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Pending Receipts') }}</p>
                <p class="mt-1 text-2xl font-bold {{ $pendingReceipts > 0 ? 'text-amber-600' : 'text-erp-primary' }}">{{ $pendingReceipts }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Pending Issues') }}</p>
                <p class="mt-1 text-2xl font-bold {{ $pendingIssues > 0 ? 'text-amber-600' : 'text-erp-primary' }}">{{ $pendingIssues }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Low Stock Alerts') }}</p>
                <p class="mt-1 text-2xl font-bold {{ $lowStockAlerts > 0 ? 'text-amber-600' : 'text-erp-primary' }}">{{ $lowStockAlerts }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Open Counts') }}</p>
                <p class="mt-1 text-2xl font-bold text-erp-primary">{{ $openStockCounts }}</p>
            </div>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Catalogue Items') }}</p>
                <p class="mt-1 text-2xl font-bold text-erp-primary">{{ $totalItems }}</p>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="mb-4">
            <h2 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Quick actions') }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.inventory.receipts.create', ['from' => 'store-desk']) }}" class="erp-btn-primary text-sm" data-erp-modal-open>{{ __('Receive goods') }}</a>
                <a href="{{ route('admin.inventory.issues.create', ['from' => 'store-desk']) }}" class="erp-btn-primary text-sm" data-erp-modal-open>{{ __('Issue materials') }}</a>
                <a href="{{ route('admin.inventory.stock-counts.create', ['from' => 'store-desk']) }}" class="erp-btn-primary text-sm" data-erp-modal-open>{{ __('Stock count') }}</a>
                <a href="{{ $catalogueUrl }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('View catalogue') }}</a>
                <a href="{{ $reorderAlertsUrl }}" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Reorder alerts') }}</a>
            </div>
        </div>

        {{-- Pending drafts that need posting --}}
        @if ($hasPendingWork)
            <x-admin.card :padding="false" class="mb-4">
                <div class="border-b border-erp-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-slate-900">{{ __('Finish these drafts') }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">{{ __('Saved drafts and open counts stay here until you post them.') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Document') }}</th>
                                <th>{{ __('Warehouse') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($draftReceipts as $receipt)
                                <tr>
                                    <td>
                                        <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Receipt') }}</span>
                                    </td>
                                    <td class="font-mono text-xs font-medium">{{ $receipt->receipt_number }}</td>
                                    <td>{{ $receipt->warehouse?->name ?? '—' }}</td>
                                    <td class="text-xs text-slate-600">{{ $receipt->receipt_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('admin.inventory.receipts.show', [$receipt, 'from' => 'store-desk']) }}" class="erp-btn-secondary text-xs" data-erp-modal-open>{{ __('Review') }}</a>
                                            @can('post', $receipt)
                                                <form method="POST" action="{{ route('admin.inventory.receipts.post', $receipt) }}" class="inline" data-erp-desk-form>
                                                    @csrf
                                                    <input type="hidden" name="from" value="store-desk">
                                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Post to stock') }}</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach ($draftIssues as $issue)
                                <tr>
                                    <td>
                                        <span class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700">{{ __('Issue') }}</span>
                                    </td>
                                    <td class="font-mono text-xs font-medium">{{ $issue->issue_number }}</td>
                                    <td>{{ $issue->warehouse?->name ?? '—' }}</td>
                                    <td class="text-xs text-slate-600">{{ $issue->issue_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('admin.inventory.issues.show', [$issue, 'from' => 'store-desk']) }}" class="erp-btn-secondary text-xs" data-erp-modal-open>{{ __('Review') }}</a>
                                            @can('post', $issue)
                                                <form method="POST" action="{{ route('admin.inventory.issues.post', $issue) }}" class="inline" data-erp-desk-form>
                                                    @csrf
                                                    <input type="hidden" name="from" value="store-desk">
                                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Post to stock') }}</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            @foreach ($openCounts as $count)
                                <tr>
                                    <td>
                                        <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">{{ __('Stock count') }}</span>
                                    </td>
                                    <td class="font-mono text-xs font-medium">{{ $count->count_number }}</td>
                                    <td>{{ $count->warehouse?->name ?? '—' }}</td>
                                    <td class="text-xs text-slate-600">{{ $count->count_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('admin.inventory.stock-counts.worksheet', [$count, 'from' => 'store-desk']) }}" class="erp-btn-secondary text-xs" data-erp-modal-open>{{ __('Continue') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        @endif

        {{-- Recent movements table --}}
        <x-admin.card :padding="false">
            <div class="border-b border-erp-border px-4 py-3">
                <h2 class="text-sm font-semibold text-slate-900">{{ __('Recent stock movements') }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Posted receipts and issues appear here.') }}</p>
            </div>
            @if ($recentMovements->isEmpty())
                <div class="px-4 py-6 text-center text-sm text-slate-500">
                    @if ($hasPendingWork)
                        {{ __('No posted movements yet — post the drafts above to update stock.') }}
                    @else
                        {{ __('No stock movements recorded yet.') }}
                    @endif
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="erp-table w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Item') }}</th>
                                <th>{{ __('Warehouse') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="text-right">{{ __('Qty') }}</th>
                                <th>{{ __('By') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentMovements as $movement)
                                <tr>
                                    <td class="text-xs text-slate-600">{{ $movement->movement_date?->format('d M Y') ?? $movement->created_at?->format('d M Y') }}</td>
                                    <td class="font-medium">{{ $movement->item?->item_name ?? '—' }}</td>
                                    <td>{{ $movement->warehouse?->name ?? '—' }}</td>
                                    <td>
                                        <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-medium
                                            {{ in_array($movement->movement_type?->value, ['receipt', 'production_output', 'transfer_in', 'adjustment_in']) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                                            {{ str_replace('_', ' ', ucfirst($movement->movement_type?->value ?? '—')) }}
                                        </span>
                                    </td>
                                    <td class="text-right font-mono text-xs">{{ number_format((float) $movement->quantity, 2) }}</td>
                                    <td class="text-xs text-slate-600">{{ $movement->creator?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </div>
</x-admin-layout>
