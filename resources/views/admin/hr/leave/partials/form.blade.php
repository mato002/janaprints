@php
    $fields = $formFields ?? [];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @if (($fields['employee_id']['visible'] ?? true))
        @include('admin.hr.partials.employee-lookup-select', [
            'employees' => $formData['employees'],
            'value' => $defaultEmployeeId ?? null,
            'class' => 'md:col-span-2',
            'required' => ($fields['employee_id']['required'] ?? true),
        ])
    @endif

    @if (($fields['leave_type_id']['visible'] ?? true))
        <div class="md:col-span-2">
            <x-input-label for="leave_type_id" :value="$fields['leave_type_id']['label'] ?? __('Leave Type')" />
            <select
                name="leave_type_id"
                id="leave_type_id"
                class="erp-select mt-1 w-full"
                @required($fields['leave_type_id']['required'] ?? true)
                @disabled($fields['leave_type_id']['read_only'] ?? false)
            >
                @foreach ($formData['leaveTypes'] as $type)
                    <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if (($fields['start_date']['visible'] ?? true))
        <div>
            <x-input-label for="start_date" :value="$fields['start_date']['label'] ?? __('Start Date')" />
            <x-text-input
                id="start_date"
                name="start_date"
                type="date"
                class="block mt-1 w-full"
                :value="old('start_date', $fields['start_date']['default'] ?? null)"
                :required="$fields['start_date']['required'] ?? true"
                :disabled="$fields['start_date']['read_only'] ?? false"
            />
        </div>
    @endif

    @if (($fields['end_date']['visible'] ?? true))
        <div>
            <x-input-label for="end_date" :value="$fields['end_date']['label'] ?? __('End Date')" />
            <x-text-input
                id="end_date"
                name="end_date"
                type="date"
                class="block mt-1 w-full"
                :value="old('end_date', $fields['end_date']['default'] ?? null)"
                :required="$fields['end_date']['required'] ?? true"
                :disabled="$fields['end_date']['read_only'] ?? false"
            />
        </div>
    @endif

    @if (($fields['is_half_day_start']['visible'] ?? true))
        <label class="flex gap-2 items-center">
            <input type="hidden" name="is_half_day_start" value="0">
            <input type="checkbox" name="is_half_day_start" value="1" @checked(old('is_half_day_start')) @disabled($fields['is_half_day_start']['read_only'] ?? false)>
            {{ $fields['is_half_day_start']['label'] ?? __('Half day (start)') }}
        </label>
    @endif

    @if (($fields['is_half_day_end']['visible'] ?? true))
        <label class="flex gap-2 items-center">
            <input type="hidden" name="is_half_day_end" value="0">
            <input type="checkbox" name="is_half_day_end" value="1" @checked(old('is_half_day_end')) @disabled($fields['is_half_day_end']['read_only'] ?? false)>
            {{ $fields['is_half_day_end']['label'] ?? __('Half day (end)') }}
        </label>
    @endif

    @if (($fields['reason']['visible'] ?? true))
        <div class="md:col-span-2">
            <x-input-label for="reason" :value="$fields['reason']['label'] ?? __('Reason')" />
            <textarea
                name="reason"
                id="reason"
                rows="3"
                class="erp-input mt-1 w-full"
                @required($fields['reason']['required'] ?? true)
                @disabled($fields['reason']['read_only'] ?? false)
            >{{ old('reason', $fields['reason']['default'] ?? null) }}</textarea>
        </div>
    @endif

    @if (($fields['notes']['visible'] ?? false))
        <div class="md:col-span-2">
            <x-input-label for="notes" :value="$fields['notes']['label'] ?? __('Notes')" />
            <textarea
                name="notes"
                id="notes"
                rows="2"
                class="erp-input mt-1 w-full"
                @required($fields['notes']['required'] ?? false)
                @disabled($fields['notes']['read_only'] ?? false)
            >{{ old('notes', $fields['notes']['default'] ?? null) }}</textarea>
        </div>
    @endif
</div>
