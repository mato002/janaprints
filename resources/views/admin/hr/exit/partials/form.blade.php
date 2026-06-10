<p class="text-sm text-slate-600 mb-4">{{ __('Starts offboarding with clearance checklist and final dues calculation.') }}</p>
<div class="grid gap-4 md:grid-cols-2">
    @include('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'selectClass' => 'erp-input w-full',
        'class' => 'md:col-span-2',
    ])
    <div>
        <label class="erp-label" for="exit_type">{{ __('Exit Type') }}</label>
        <select id="exit_type" name="exit_type" class="erp-input w-full" required>
            @foreach ($formData['exitTypes'] as $type)
                <option value="{{ $type->value }}" @selected(old('exit_type') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="erp-label" for="last_working_date">{{ __('Last Working Date') }}</label>
        <input id="last_working_date" type="date" name="last_working_date" value="{{ old('last_working_date') }}" class="erp-input w-full" required>
    </div>
    <div>
        <label class="erp-label" for="exit_date">{{ __('Exit Date') }}</label>
        <input id="exit_date" type="date" name="exit_date" value="{{ old('exit_date') }}" class="erp-input w-full">
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="reason">{{ __('Reason') }}</label>
        <textarea id="reason" name="reason" rows="3" class="erp-input w-full">{{ old('reason') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label class="erp-label" for="notes">{{ __('Notes') }}</label>
        <textarea id="notes" name="notes" rows="2" class="erp-input w-full">{{ old('notes') }}</textarea>
    </div>
</div>
