@php
    $d = $dashboard;
@endphp
<x-admin-layout :title="__('Dispatch')" :breadcrumbs="[['label' => __('Dispatch')]]">
    <x-admin.page-header :title="__('Dispatch Dashboard')" :description="__('Delivery notes are the operational source of truth for dispatch and delivery.')">
        <x-slot name="actions">
            @can('viewAny', App\Models\Dispatch\DeliveryNote::class)
                <a href="{{ route('admin.dispatch.delivery-notes.index') }}" class="erp-btn-primary">{{ __('Delivery notes') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Jobs ready'), 'value' => $d['ready_jobs'], 'icon' => 'cog'],
            ['label' => __('Draft notes'), 'value' => $d['draft_notes'], 'icon' => 'document-text'],
            ['label' => __('Dispatched'), 'value' => $d['dispatched_notes'], 'icon' => 'truck'],
            ['label' => __('Delivered'), 'value' => $d['delivered_notes'], 'icon' => 'check-circle'],
            ['label' => __('Delivered today'), 'value' => $d['delivered_today'], 'icon' => 'calendar'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="(string) $card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold">{{ __('Inventory ownership') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Finished goods (est.)') }}</dt><dd class="tabular-nums">{{ number_format($d['ownership']['finished_goods'] ?? 0, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('In transit (est.)') }}</dt><dd class="tabular-nums">{{ number_format($d['ownership']['in_transit'] ?? 0, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Delivered COGS') }}</dt><dd class="tabular-nums">{{ number_format($d['ownership']['delivered_value'] ?? 0, 2) }}</dd></div>
            </dl>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.dispatch.reports.transit-inventory') }}" class="erp-link text-sm">{{ __('Transit inventory') }}</a>
                <a href="{{ route('admin.dispatch.reports.cogs-postings') }}" class="erp-link text-sm">{{ __('COGS postings') }}</a>
            </div>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Invoice readiness (Phase 3F)') }}</h3>
            <p class="text-sm text-slate-600">{{ __('Delivered notes marked invoice-ready: :count', ['count' => $d['invoice_ready']]) }}</p>
        </x-admin.card>

        <x-admin.card class="border-dashed">
            <h3 class="mb-2 text-sm font-semibold">{{ __('Delivery calendar') }}</h3>
            <p class="text-sm text-slate-500">{{ __('Calendar view coming in a future release.') }}</p>
        </x-admin.card>
    </div>

    <x-admin.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold">{{ __('Recent delivery notes') }}</h3>
        <ul class="divide-y divide-slate-100 text-sm">
            @forelse ($d['recent_notes'] as $note)
                <li class="flex justify-between py-2">
                    <span>
                        <a href="{{ route('admin.dispatch.delivery-notes.show', $note) }}" class="font-mono text-indigo-600">{{ $note->delivery_note_number }}</a>
                        — {{ $note->customer?->company_name }}
                    </span>
                    <x-admin.enum-status-badge :status="$note->status->value" />
                </li>
            @empty
                <li class="py-4 text-slate-500">{{ __('No delivery notes yet.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</x-admin-layout>
