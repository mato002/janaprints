@php
    $indexUrl = route('admin.production.work-centers.index');
@endphp

<x-admin.card :padding="false">
    <form method="GET" action="{{ $indexUrl }}" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="border-b border-erp-border px-4 py-3" data-turbo-frame="erp-main">
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Work center name or code…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <x-admin.status-pills
                :options="[['value' => '', 'label' => __('All statuses')], ['value' => 'active', 'label' => __('Active')], ['value' => 'inactive', 'label' => __('Inactive')]]"
                param="status"
                :current="$filters['status'] ?? ''"
            />
            <select name="stage_id" class="erp-toolbar-select" aria-label="{{ __('Stage / process area') }}">
                <option value="">{{ __('All stages') }}</option>
                @foreach ($filterOptions['stages'] ?? [] as $stage)
                    <option value="{{ $stage->id }}" @selected(($filters['stage_id'] ?? null) == $stage->id)>{{ $stage->name }}</option>
                @endforeach
            </select>
            <select name="load" class="erp-toolbar-select" aria-label="{{ __('Load') }}">
                <option value="">{{ __('All load levels') }}</option>
                @foreach ($filterOptions['load_options'] ?? [] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['load'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ $indexUrl }}" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
        </div>
    </form>

    @if (count($activeChips) > 0)
        <div class="flex flex-wrap items-center gap-2 border-t border-erp-border px-4 py-2">
            <span class="text-xs font-medium text-slate-500">{{ __('Active filters') }}:</span>
            @foreach ($activeChips as $chip)
                <a href="{{ $chip['url'] }}" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-200" data-turbo-frame="erp-main">
                    {{ $chip['label'] }}
                    <span aria-hidden="true">×</span>
                </a>
            @endforeach
        </div>
    @endif
</x-admin.card>
