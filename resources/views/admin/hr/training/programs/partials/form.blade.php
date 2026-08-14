@props(['program' => null, 'types', 'statuses' => null])

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="erp-label" for="type">{{ __('Type') }} <span class="text-red-500">*</span></label>
        <select id="type" name="type" class="erp-input w-full @error('type') border-red-500 @enderror" required>
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected(old('type', $program?->type?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="title">{{ __('Title') }} <span class="text-red-500">*</span></label>
        <input id="title" type="text" name="title" value="{{ old('title', $program?->title) }}" class="erp-input w-full @error('title') border-red-500 @enderror" required>
        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    @if ($statuses)
        <div>
            <label class="erp-label" for="status">{{ __('Status') }}</label>
            <select id="status" name="status" class="erp-input w-full @error('status') border-red-500 @enderror">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $program?->status?->value ?? \App\Enums\TrainingProgramStatus::Draft->value) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif
    <div>
        <label class="erp-label" for="duration_hours">{{ __('Duration (hours)') }}</label>
        <input id="duration_hours" type="number" step="0.5" name="duration_hours" value="{{ old('duration_hours', $program?->duration_hours ?? 8) }}" class="erp-input w-full @error('duration_hours') border-red-500 @enderror">
        @error('duration_hours')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="budget_amount">{{ __('Budget Amount') }}</label>
        <input id="budget_amount" type="number" step="0.01" name="budget_amount" value="{{ old('budget_amount', $program?->budget_amount) }}" class="erp-input w-full @error('budget_amount') border-red-500 @enderror">
        @error('budget_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="scheduled_start_date">{{ __('Scheduled Start') }}</label>
        <input id="scheduled_start_date" type="date" name="scheduled_start_date" value="{{ old('scheduled_start_date', $program?->scheduled_start_date?->format('Y-m-d')) }}" min="{{ now()->toDateString() }}" class="erp-input w-full @error('scheduled_start_date') border-red-500 @enderror">
        @error('scheduled_start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="scheduled_end_date">{{ __('Scheduled End') }}</label>
        <input id="scheduled_end_date" type="date" name="scheduled_end_date" value="{{ old('scheduled_end_date', $program?->scheduled_end_date?->format('Y-m-d')) }}" class="erp-input w-full @error('scheduled_end_date') border-red-500 @enderror">
        @error('scheduled_end_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="erp-label" for="certificate_validity_days">{{ __('Certificate Validity (days)') }}</label>
        <input id="certificate_validity_days" type="number" name="certificate_validity_days" value="{{ old('certificate_validity_days', $program?->certificate_validity_days) }}" class="erp-input w-full @error('certificate_validity_days') border-red-500 @enderror">
        @error('certificate_validity_days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="description">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="3" class="erp-input w-full @error('description') border-red-500 @enderror">{{ old('description', $program?->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="skill_tags">{{ __('Skills Covered (comma-separated)') }}</label>
        <input id="skill_tags" type="text" name="skill_tags" value="{{ old('skill_tags', collect($program?->skill_tags)->join(', ')) }}" class="erp-input w-full @error('skill_tags') border-red-500 @enderror" placeholder="{{ __('e.g. Screen Printing, Color Matching') }}">
        @error('skill_tags')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="evaluation_instructions">{{ __('Evaluation Instructions') }}</label>
        <textarea id="evaluation_instructions" name="evaluation_instructions" rows="2" class="erp-input w-full @error('evaluation_instructions') border-red-500 @enderror">{{ old('evaluation_instructions', $program?->evaluation_instructions) }}</textarea>
        @error('evaluation_instructions')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="requires_certification" value="1" @checked(old('requires_certification', $program?->requires_certification))>
            <span>{{ __('Certification Required') }}</span>
        </label>
    </div>
</div>
