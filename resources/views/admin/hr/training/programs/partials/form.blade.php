@props(['program' => null, 'types'])

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="erp-label" for="type">{{ __('Type') }}</label>
        <select id="type" name="type" class="erp-input w-full" required>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', $program?->type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label" for="title">{{ __('Title') }}</label>
        <input id="title" type="text" name="title" value="{{ old('title', $program?->title) }}" class="erp-input w-full" required>
    </div>
    <div>
        <label class="erp-label" for="duration_hours">{{ __('Duration (hours)') }}</label>
        <input id="duration_hours" type="number" step="0.5" name="duration_hours" value="{{ old('duration_hours', $program?->duration_hours ?? 8) }}" class="erp-input w-full">
    </div>
    <div>
        <label class="erp-label" for="budget_amount">{{ __('Training Budget') }}</label>
        <input id="budget_amount" type="number" step="0.01" name="budget_amount" value="{{ old('budget_amount', $program?->budget_amount) }}" class="erp-input w-full">
    </div>
    <div>
        <label class="erp-label" for="scheduled_start_date">{{ __('Scheduled Start') }}</label>
        <input id="scheduled_start_date" type="date" name="scheduled_start_date" value="{{ old('scheduled_start_date', $program?->scheduled_start_date?->format('Y-m-d')) }}" class="erp-input w-full">
    </div>
    <div>
        <label class="erp-label" for="scheduled_end_date">{{ __('Scheduled End') }}</label>
        <input id="scheduled_end_date" type="date" name="scheduled_end_date" value="{{ old('scheduled_end_date', $program?->scheduled_end_date?->format('Y-m-d')) }}" class="erp-input w-full">
    </div>
    <div>
        <label class="erp-label" for="certificate_validity_days">{{ __('Certificate Validity (days)') }}</label>
        <input id="certificate_validity_days" type="number" name="certificate_validity_days" value="{{ old('certificate_validity_days', $program?->certificate_validity_days) }}" class="erp-input w-full">
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="description">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3" class="erp-input w-full">{{ old('description', $program?->description) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="skill_tags">{{ __('Skills (comma-separated)') }}</label>
        <input id="skill_tags" type="text" name="skill_tags" value="{{ old('skill_tags', collect($program?->skill_tags)->join(', ')) }}" class="erp-input w-full">
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="evaluation_instructions">{{ __('Evaluation Instructions') }}</label>
        <textarea id="evaluation_instructions" name="evaluation_instructions" rows="2" class="erp-input w-full">{{ old('evaluation_instructions', $program?->evaluation_instructions) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="requires_certification" value="1" @checked(old('requires_certification', $program?->requires_certification))>
            <span>{{ __('Requires certification') }}</span>
        </label>
    </div>
</div>
