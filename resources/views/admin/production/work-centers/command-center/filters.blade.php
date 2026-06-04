@php
    $indexUrl = route('admin.production.work-centers.index');
@endphp

<x-admin.card :padding="false">
    <form method="GET" action="{{ $indexUrl }}" class="border-b border-erp-border px-4 py-3">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Search') }}</label>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="erp-input w-full text-sm" placeholder="{{ __('Work center name or code…') }}" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Status') }}</label>
                <select name="status" class="erp-input w-full text-sm">
                    <option value="">{{ __('All statuses') }}</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Stage / process area') }}</label>
                <select name="stage_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All stages') }}</option>
                    @foreach ($filterOptions['stages'] ?? [] as $stage)
                        <option value="{{ $stage->id }}" @selected(($filters['stage_id'] ?? null) == $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('Load') }}</label>
                <select name="load" class="erp-input w-full text-sm">
                    <option value="">{{ __('All load levels') }}</option>
                    @foreach ($filterOptions['load_options'] ?? [] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['load'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Apply filters') }}</button>
            <a href="{{ $indexUrl }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
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
