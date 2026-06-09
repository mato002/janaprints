<x-admin-layout :title="__('Executive Approval Queue')" :breadcrumbs="[['label' => __('Command Center'), 'url' => route('admin.dashboard')], ['label' => __('Approvals')]]">
    <x-admin.page-header
        :title="__('Executive Approval Queue')"
        :description="__('Unified approvals across commercial, HR, procurement, inventory, and finance.')"
    />

    <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <x-admin.card class="p-4">
            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('Approvals Waiting') }}</div>
            <div class="mt-1 text-2xl font-semibold">{{ $queue['summary']['waiting'] }}</div>
        </x-admin.card>
        <x-admin.card class="p-4">
            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('Critical Approvals') }}</div>
            <div class="mt-1 text-2xl font-semibold text-red-600">{{ $queue['summary']['critical'] }}</div>
        </x-admin.card>
        <x-admin.card class="p-4">
            <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('Aging Approvals') }}</div>
            <div class="mt-1 text-2xl font-semibold text-amber-600">{{ $queue['summary']['aging'] }}</div>
        </x-admin.card>
    </div>

    <section class="exec-panel">
        <div class="exec-panel__head">
            <h2 class="exec-panel__title">{{ __('Pending Approvals') }}</h2>
            <span class="exec-panel__meta">{{ __('Cross-module queue') }}</span>
        </div>
        @include('admin.executive.approvals.partials.table', [
            'rows' => $queue['items'],
            'canAction' => $queue['can_action'],
        ])
    </section>
</x-admin-layout>
