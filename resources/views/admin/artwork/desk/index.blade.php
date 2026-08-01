@php
    use App\Support\Navigation\WorkspaceEmbed;

    $operatorMode = (bool) ($operatorMode ?? false);
    $greeting = $greeting ?? ['title' => __('Designer Desk'), 'facts' => []];
    $filters = $filters ?? [];
@endphp

<x-admin-layout
    :title="$operatorMode ? __('Designer Desk') : __('Artwork Desk')"
    :breadcrumbs="$operatorMode
        ? [['label' => __('Designer Desk')]]
        : [
            ['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')],
            ['label' => __('Designer Desk')],
        ]"
>
    <div
        class="designer-desk-shell designer-desk-command"
        x-data="designerDesk(@js([
            'panelBase' => url('admin/artwork/desk/requests'),
            'initialRequestKey' => request('request'),
            'autoSelectFirst' => collect($rows)->isNotEmpty(),
            'firstKey' => data_get(collect($rows)->first(), 'key'),
        ]))"
        x-cloak
    >
        @if (session('status'))
            <div class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif

        {{-- Smart banner --}}
        <section class="mb-3 flex flex-wrap items-start justify-between gap-3 rounded-xl border border-erp-border bg-white px-4 py-3 shadow-sm">
            <div class="min-w-0">
                <p class="text-base font-semibold text-erp-primary">{{ $greeting['title'] }}</p>
                <p class="mt-0.5 text-xs text-slate-600">{{ implode(' · ', $greeting['facts'] ?? []) }}</p>
            </div>
            @unless ($operatorMode || WorkspaceEmbed::inWorkspaceContext())
                <a href="{{ route('admin.artwork.dashboard') }}" class="erp-btn-secondary shrink-0 text-xs" data-turbo-frame="erp-main">{{ __('Full dashboard') }}</a>
            @endunless
        </section>

        {{-- Compact TODAY strip --}}
        @include('admin.artwork.desk.partials.summary-strip', ['summary' => $summary])

        {{-- Quick filters --}}
        @include('admin.artwork.desk.partials.queue-filters', ['filters' => $filters])

        {{-- Split: Queue | Selected job --}}
        <div class="designer-desk-split grid gap-3 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-5 xl:col-span-4">
                @include('admin.artwork.desk.partials.queue-cards', [
                    'rows' => $rows,
                    'availableRows' => $available_rows ?? [],
                    'requests' => $requests,
                    'has_assignments' => $has_assignments,
                ])
            </div>

            <div class="lg:col-span-7 xl:col-span-8">
                @include('admin.artwork.desk.partials.workspace', ['operatorMode' => $operatorMode])
                @include('admin.artwork.desk.partials.idle-panel', [
                    'today_activity' => $today_activity,
                    'has_assignments' => $has_assignments,
                ])
            </div>
        </div>
    </div>
</x-admin-layout>
