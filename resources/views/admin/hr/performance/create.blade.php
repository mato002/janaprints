<x-admin-layout :title="__('New Appraisal')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Performance'), 'url' => route('admin.hr.performance.dashboard')], ['label' => __('New')]]">
    <x-admin.page-header :title="__('New Performance Appraisal')" :description="__('KPIs are calculated automatically from attendance, production, sales, and quality data.')" />

    <form method="POST" action="{{ route('admin.hr.performance.store') }}" class="erp-card max-w-3xl">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="erp-label" for="employee_id">{{ __('Employee') }}</label>
                <select id="employee_id" name="employee_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select employee') }}</option>
                    @foreach ($formData['employees'] as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_number }})</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="erp-label" for="cycle">{{ __('Appraisal Cycle') }}</label>
                <select id="cycle" name="cycle" class="erp-input w-full" required>
                    @foreach ($formData['cycles'] as $cycle)
                        <option value="{{ $cycle->value }}" @selected(old('cycle') === $cycle->value)>{{ $cycle->label() }}</option>
                    @endforeach
                </select>
                @error('cycle')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="erp-label" for="rating">{{ __('Rating (optional override)') }}</label>
                <select id="rating" name="rating" class="erp-input w-full">
                    <option value="">{{ __('Auto from KPI score') }}</option>
                    @foreach ($formData['ratings'] as $rating)
                        <option value="{{ $rating->value }}" @selected(old('rating') === $rating->value)>{{ $rating->label() }}</option>
                    @endforeach
                </select>
                @error('rating')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="erp-label" for="period_start">{{ __('Period Start') }}</label>
                <input id="period_start" type="date" name="period_start" value="{{ old('period_start', now()->startOfQuarter()->toDateString()) }}" class="erp-input w-full" required>
                @error('period_start')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="erp-label" for="period_end">{{ __('Period End') }}</label>
                <input id="period_end" type="date" name="period_end" value="{{ old('period_end', now()->endOfQuarter()->toDateString()) }}" class="erp-input w-full" required>
                @error('period_end')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="strengths">{{ __('Strengths') }}</label>
                <textarea id="strengths" name="strengths" rows="3" class="erp-input w-full">{{ old('strengths') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="improvements">{{ __('Areas for Improvement') }}</label>
                <textarea id="improvements" name="improvements" rows="3" class="erp-input w-full">{{ old('improvements') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label class="erp-label" for="manager_notes">{{ __('Manager Notes') }}</label>
                <textarea id="manager_notes" name="manager_notes" rows="3" class="erp-input w-full">{{ old('manager_notes') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Save draft') }}</button>
            <button type="submit" name="submit" value="1" class="erp-btn-secondary">{{ __('Save & submit') }}</button>
            <a href="{{ route('admin.hr.performance.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
