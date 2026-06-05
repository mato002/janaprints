<x-admin-layout :title="__('New Program')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('New Program')]]">
    <x-admin.page-header :title="__('New Training Program')" />

    <form method="POST" action="{{ route('admin.hr.training.programs.store') }}" class="erp-card max-w-3xl">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="erp-label" for="type">{{ __('Type') }}</label>
                <select id="type" name="type" class="erp-input w-full" required>
                    @foreach ($types as $type)
                        <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="title">{{ __('Title') }}</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" class="erp-input w-full" required>
            </div>
            <div>
                <label class="erp-label" for="duration_hours">{{ __('Duration (hours)') }}</label>
                <input id="duration_hours" type="number" step="0.5" name="duration_hours" value="{{ old('duration_hours', 8) }}" class="erp-input w-full">
            </div>
            <div>
                <label class="erp-label" for="certificate_validity_days">{{ __('Certificate Validity (days)') }}</label>
                <input id="certificate_validity_days" type="number" name="certificate_validity_days" value="{{ old('certificate_validity_days') }}" class="erp-input w-full">
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="description">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="3" class="erp-input w-full">{{ old('description') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="skill_tags">{{ __('Skills (comma-separated)') }}</label>
                <input id="skill_tags" type="text" name="skill_tags" value="{{ old('skill_tags') }}" class="erp-input w-full" placeholder="{{ __('e.g. Screen Printing, Color Matching') }}">
            </div>
            <div class="md:col-span-2">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="requires_certification" value="1" @checked(old('requires_certification'))>
                    <span>{{ __('Requires certification') }}</span>
                </label>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Create program') }}</button>
            <a href="{{ route('admin.hr.training.programs.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
