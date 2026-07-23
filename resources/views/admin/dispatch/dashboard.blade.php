@php
    use App\Support\Navigation\WorkspaceEmbed;

    $d = $dashboard;
    $operatorMode = (bool) ($operatorMode ?? false);
    $focus = request('focus', 'ready');
    $listFrame = WorkspaceEmbed::turboFrame();
@endphp
<x-admin-layout
    :title="__('Dispatch Desk')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Dispatch Desk')]]
        : [
            ['label' => __('Production'), 'url' => route('admin.workspaces.production.section', ['section' => 'dispatch'])],
            ['label' => __('Dispatch Desk')],
        ]"
    >
        @if ($operatorMode)
            <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-erp-primary">{{ __('Dispatch desk') }}</p>
                    <p class="text-xs text-slate-600">{{ __('Package, dispatch, collect, and confirm delivery — stay on this desk.') }}</p>
                </div>
            </div>
        @endif

    <x-admin.page-header
        :title="__('Dispatch Desk')"
        :description="__('Track dispatch readiness, create delivery notes, and confirm delivery — actions follow job state.')"
    >
        <x-slot name="secondary">
            <a href="{{ WorkspaceEmbed::url(route('admin.dispatch.calendar')) }}" class="erp-btn-secondary" data-turbo-frame="{{ $listFrame }}" data-turbo-action="advance">{{ __('Calendar') }}</a>
        </x-slot>
        <x-slot name="actions">
            @can('viewAny', App\Models\Dispatch\DeliveryNote::class)
                <a href="{{ WorkspaceEmbed::url(route('admin.dispatch.delivery-notes.index')) }}" class="erp-btn-primary" data-turbo-frame="{{ $listFrame }}" data-turbo-action="advance">{{ __('All delivery notes') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($d['summary'] as $card)
            <a
                href="{{ WorkspaceEmbed::url(route('admin.dispatch.dashboard', $card['filter'] ?? [])) }}"
                class="block transition-opacity hover:opacity-90"
                data-turbo-frame="{{ $listFrame }}"
                data-turbo-action="advance"
            >
                <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" />
            </a>
        @endforeach
    </div>

  @if ($focus !== 'notes')
    @if ($focus === 'ready' || $focus === '')
        <x-admin.card :padding="false" class="mb-6" id="ready-jobs">
            <div class="border-b border-erp-border px-4 py-3">
                <h2 class="text-sm font-semibold text-erp-primary">
                    {{ __('Ready for delivery note') }}
                    <span class="ml-1 font-normal text-slate-500">({{ $d['ready_jobs_count'] }})</span>
                </h2>
                <p class="mt-1 text-xs text-slate-500">{{ __('Jobs that passed all dispatch checks — safe to create a delivery note.') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Job') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Due') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="erp-table-actions-col">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($d['ready_jobs'] as $row)
                            @include('admin.dispatch.partials.desk-job-row', ['row' => $row, 'canCreateNote' => $d['can_create_note']])
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500">{{ __('No jobs are ready for a delivery note yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif

    @if (($d['blocked_jobs_count'] ?? 0) > 0 && ($focus === 'blocked' || $focus === ''))
        <x-admin.card :padding="false" class="mb-6" id="blocked-jobs">
            <div class="border-b border-erp-border px-4 py-3">
                <h2 class="text-sm font-semibold text-erp-primary">
                    {{ __('Needs attention before dispatch') }}
                    <span class="ml-1 font-normal text-slate-500">({{ $d['blocked_jobs_count'] }})</span>
                </h2>
                <p class="mt-1 text-xs text-slate-500">{{ __('These jobs are marked ready for dispatch but still need a prerequisite step. Complete the next action in order.') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Job') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Due') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="erp-table-actions-col">{{ __('Next step') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($d['blocked_jobs'] as $row)
                            @include('admin.dispatch.partials.desk-job-row', ['row' => $row, 'canCreateNote' => false])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif
  @endif

    <x-admin.card :padding="false" id="delivery-notes">
        <div class="border-b border-erp-border px-4 py-3">
            <x-admin.index-toolbar :action="route('admin.dispatch.dashboard')" :reset-url="route('admin.dispatch.dashboard', request()->only('embedded'))" :show-reset="($d['filter_status'] ?? '') !== ''">
                <input type="hidden" name="embedded" value="{{ request('embedded') }}">
                <input type="hidden" name="focus" value="notes">
                <x-admin.status-pills
                    :options="collect(App\Enums\Dispatch\DeliveryNoteStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => __('All')])->all()"
                    param="status"
                    :current="$d['filter_status'] ?? ''"
                />
            </x-admin.index-toolbar>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('DN number') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Delivery date') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($d['notes'] as $note)
                        <tr>
                            <td class="font-mono text-xs">
                                <a href="{{ route('admin.dispatch.delivery-notes.show', $note) }}" class="text-erp-accent hover:underline">{{ $note->delivery_note_number }}</a>
                            </td>
                            <td>{{ $note->customer?->company_name ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $note->productionJobCard?->job_card_number ?? '—' }}</td>
                            <td class="whitespace-nowrap text-xs">{{ $note->delivery_date?->format('Y-m-d') ?? '—' }}</td>
                            <td><x-admin.enum-status-badge :status="$note->status->value" /></td>
                            <td class="erp-table-actions-col">
                                <a href="{{ route('admin.dispatch.delivery-notes.show', $note) }}" class="text-sm text-erp-accent hover:underline">{{ __('Open') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">{{ __('No delivery notes yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($d['notes']->hasPages())
            <div class="border-t border-erp-border px-4 py-3">
                <x-admin.table-pagination :paginator="$d['notes']" />
            </div>
        @endif
    </x-admin.card>

    <p class="mt-4 text-xs text-slate-500">
        {{ __('Invoice-ready delivered notes: :count', ['count' => $d['invoice_ready']]) }}
        ·
        <a href="{{ WorkspaceEmbed::url(route('admin.dispatch.reports.transit-inventory')) }}" class="erp-link" data-turbo-frame="{{ $listFrame }}" data-turbo-action="advance">{{ __('Transit inventory') }}</a>
        ·
        <a href="{{ WorkspaceEmbed::url(route('admin.dispatch.reports.cogs-postings')) }}" class="erp-link" data-turbo-frame="{{ $listFrame }}" data-turbo-action="advance">{{ __('COGS postings') }}</a>
    </p>
</x-admin-layout>
