<div class="grid gap-4">
    @include('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'selectClass' => 'erp-input w-full',
    ])
    <div>
        <label class="erp-label" for="training_program_id">{{ __('Training Program') }}</label>
        <select id="training_program_id" name="training_program_id" class="erp-input w-full" required>
            <option value="">{{ __('Select program') }}</option>
            @foreach ($formData['programs'] as $program)
                <option value="{{ $program->id }}" @selected(old('training_program_id', $selectedProgramId ?? null) == $program->id)>{{ $program->title }} ({{ $program->type->label() }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label" for="due_date">{{ __('Due Date') }}</label>
        <input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}" min="{{ now()->toDateString() }}" class="erp-input w-full">
    </div>
    <div>
        <label class="erp-label" for="notes">{{ __('Notes') }}</label>
        <textarea id="notes" name="notes" rows="3" class="erp-input w-full">{{ old('notes') }}</textarea>
    </div>
</div>
