<p class="text-sm text-slate-600 mb-4">{{ __('Manual Attendance Entry') }}</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @include('admin.hr.partials.employee-lookup-select', [
        'employees' => $formData['employees'],
        'class' => 'md:col-span-2',
    ])
    <div>
        <x-input-label for="attendance_date" :value="__('Date')" />
        <x-text-input id="attendance_date" name="attendance_date" type="date" class="block mt-1 w-full" :value="old('attendance_date', now()->toDateString())" required />
    </div>
    <div>
        <x-input-label for="shift_id" :value="__('Shift')" />
        <select name="shift_id" id="shift_id" class="erp-select mt-1 w-full">
            <option value="">{{ __('Employee default / none') }}</option>
            @foreach ($formData['shifts'] as $shift)
                <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>{{ $shift->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="clock_in_at" :value="__('Clock In')" />
        <x-text-input id="clock_in_at" name="clock_in_at" type="datetime-local" class="block mt-1 w-full" :value="old('clock_in_at')" />
    </div>
    <div>
        <x-input-label for="clock_out_at" :value="__('Clock Out')" />
        <x-text-input id="clock_out_at" name="clock_out_at" type="datetime-local" class="block mt-1 w-full" :value="old('clock_out_at')" />
    </div>
    <div>
        <x-input-label for="status" :value="__('Status')" />
        <select name="status" id="status" class="erp-select mt-1 w-full" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', 'present') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-2">
        <x-input-label for="notes" :value="__('Notes')" />
        <textarea name="notes" id="notes" rows="3" class="erp-input mt-1 w-full">{{ old('notes') }}</textarea>
    </div>
</div>
