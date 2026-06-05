<x-admin-layout :title="__('Edit job card')" :breadcrumbs="[
    ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
    ['label' => __('Job Cards'), 'url' => route('admin.production.job-cards.index')],
    ['label' => $jobCard->job_card_number],
]">
    <x-admin.page-header :title="$jobCard->job_card_number" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.production.job-cards.update', $jobCard) }}" class="space-y-4 max-w-xl">
            @csrf
            @method('PUT')
            <div>
                <label class="erp-label">{{ __('Production type') }}</label>
                <select name="production_type" class="erp-input w-full" required>
                    @foreach ($productionTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('production_type', $jobCard->production_type->value) === $type->value)>{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Priority') }}</label>
                <select name="priority" class="erp-input w-full" required>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(old('priority', $jobCard->priority->value) === $priority->value)>{{ ucfirst($priority->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="erp-label">{{ __('Planned start') }}</label>
                    <input type="date" name="planned_start_date" class="erp-input w-full" value="{{ old('planned_start_date', $jobCard->planned_start_date?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label class="erp-label">{{ __('Planned end') }}</label>
                    <input type="date" name="planned_end_date" class="erp-input w-full" value="{{ old('planned_end_date', $jobCard->planned_end_date?->format('Y-m-d')) }}">
                </div>
            </div>
            <button type="submit" class="erp-btn-primary">{{ __('Save') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
