@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<x-admin-layout
    :title="$operatorMode ? __('Designer Desk') : __('Artwork Desk')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Designer Desk')]]
        : [
            ['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')],
            ['label' => __('Designer Desk')],
        ]"
    :compact-page="false"
>
    <div
        class="designer-desk-shell"
        x-data="designerDesk(@js([
            'panelBase' => url('admin/artwork/desk/requests'),
            'initialRequestKey' => request('request'),
        ]))"
        x-cloak
    >
        @if ($operatorMode)
            <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-erp-primary">{{ __('Designer desk') }}</p>
                    <p class="text-xs text-slate-600">{{ __('Select a job to work inline — files, specs, and submit actions stay here.') }}</p>
                </div>
            </div>
        @else
            <x-admin.page-header
                :title="__('Designer Desk')"
                :description="__('Your operational workspace — accept jobs, upload, and submit without leaving the desk.')"
            >
                <x-slot name="actions">
                    <a href="{{ route('admin.artwork.dashboard') }}" class="erp-btn-secondary" data-turbo-frame="_top">{{ __('Full Artwork dashboard') }}</a>
                </x-slot>
            </x-admin.page-header>
        @endif

        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        @include('admin.artwork.desk.partials.summary-strip', ['summary' => $summary])
        @include('admin.artwork.desk.partials.urgent-queue', ['urgent' => $urgent])

        <div :class="selectedKey ? 'opacity-100' : ''">
            @include('admin.artwork.desk.partials.table', ['rows' => $rows, 'operatorMode' => $operatorMode])
            <div class="mt-4 pb-2" x-show="!selectedKey">{{ $requests->links() }}</div>
        </div>

        @include('admin.artwork.desk.partials.workspace', ['operatorMode' => $operatorMode])
        @include('admin.artwork.desk.partials.idle-panel', [
            'today_activity' => $today_activity,
            'has_assignments' => $has_assignments,
        ])
    </div>
</x-admin-layout>
