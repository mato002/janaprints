<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @include('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'value' => $defaultEmployeeId ?? null,
        'class' => 'md:col-span-2',
    ])
    <div class="md:col-span-2">
        <x-input-label for="leave_type_id" :value="__('Leave Type')" />
        <select name="leave_type_id" id="leave_type_id" class="erp-select mt-1 w-full" required>
            @foreach ($formData['leaveTypes'] as $type)
                <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="start_date" :value="__('Start Date')" />
        <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="old('start_date')" required />
    </div>
    <div>
        <x-input-label for="end_date" :value="__('End Date')" />
        <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full" :value="old('end_date')" required />
    </div>
    <label class="flex gap-2 items-center">
        <input type="hidden" name="is_half_day_start" value="0">
        <input type="checkbox" name="is_half_day_start" value="1" @checked(old('is_half_day_start'))>
        {{ __('Half day (start)') }}
    </label>
    <label class="flex gap-2 items-center">
        <input type="hidden" name="is_half_day_end" value="0">
        <input type="checkbox" name="is_half_day_end" value="1" @checked(old('is_half_day_end'))>
        {{ __('Half day (end)') }}
    </label>
    <div class="md:col-span-2">
        <x-input-label for="reason" :value="__('Reason')" />
        <textarea name="reason" id="reason" rows="3" class="erp-input mt-1 w-full" required>{{ old('reason') }}</textarea>
    </div>
</div>
