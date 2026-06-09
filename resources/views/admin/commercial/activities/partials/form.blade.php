@php
    $activity = $activity ?? null;
    $fields = $formFields ?? [];
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @if (($fields['customer_id']['visible'] ?? true))
        <div>
            <label class="text-sm font-medium text-slate-700">{{ $fields['customer_id']['label'] ?? __('Customer') }}</label>
            <select name="customer_id" class="erp-input mt-1 w-full" @required($fields['customer_id']['required'] ?? false) @disabled($fields['customer_id']['read_only'] ?? false)>
                <option value="">{{ __('— None —') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $presetCustomerId ?? $activity?->customer_id) == $customer->id)>{{ $customer->company_name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['lead_id']['visible'] ?? true))
        <div>
            <label class="text-sm font-medium text-slate-700">{{ $fields['lead_id']['label'] ?? __('Lead') }}</label>
            <select name="lead_id" class="erp-input mt-1 w-full" @required($fields['lead_id']['required'] ?? false) @disabled($fields['lead_id']['read_only'] ?? false)>
                <option value="">{{ __('— None —') }}</option>
                @foreach ($leads as $lead)
                    <option value="{{ $lead->id }}" @selected(old('lead_id', $presetLeadId ?? $activity?->lead_id) == $lead->id)>{{ $lead->lead_name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['activity_type']['visible'] ?? true))
        <div>
            <label class="text-sm font-medium text-slate-700">{{ $fields['activity_type']['label'] ?? __('Activity type') }}</label>
            <select name="activity_type" class="erp-input mt-1 w-full" @required($fields['activity_type']['required'] ?? true) @disabled($fields['activity_type']['read_only'] ?? false)>
                @foreach ($activityTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('activity_type', $activity?->activity_type?->value) === $type->value)>{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['status']['visible'] ?? true))
        <div>
            <label class="text-sm font-medium text-slate-700">{{ $fields['status']['label'] ?? __('Status') }}</label>
            <select name="status" class="erp-input mt-1 w-full" @required($fields['status']['required'] ?? true) @disabled($fields['status']['read_only'] ?? false)>
                @foreach ($activityStatuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $activity?->status?->value ?? 'completed') === $status->value)>{{ ucfirst($status->value) }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['user_id']['visible'] ?? true))
        <div>
            <label class="text-sm font-medium text-slate-700">{{ $fields['user_id']['label'] ?? __('Assigned to') }}</label>
            <select name="user_id" class="erp-input mt-1 w-full" @required($fields['user_id']['required'] ?? false) @disabled($fields['user_id']['read_only'] ?? false)>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id', $activity?->user_id ?? auth()->id()) == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
    @endif
    @if (($fields['activity_at']['visible'] ?? true))
        <div>
            <label class="text-sm font-medium text-slate-700">{{ $fields['activity_at']['label'] ?? __('When') }}</label>
            <input type="datetime-local" name="activity_at" class="erp-input mt-1 w-full" @required($fields['activity_at']['required'] ?? true) @readonly($fields['activity_at']['read_only'] ?? false) value="{{ old('activity_at', ($activity?->activity_at ?? now())->format('Y-m-d\TH:i')) }}">
        </div>
    @endif
    @if (($fields['subject']['visible'] ?? true))
        <div class="md:col-span-2">
            <label class="text-sm font-medium text-slate-700">{{ $fields['subject']['label'] ?? __('Subject') }}</label>
            <input type="text" name="subject" class="erp-input mt-1 w-full" maxlength="255" @required($fields['subject']['required'] ?? true) @readonly($fields['subject']['read_only'] ?? false) value="{{ old('subject', $activity?->subject) }}">
        </div>
    @endif
    @if (($fields['description']['visible'] ?? true))
        <div class="md:col-span-2">
            <label class="text-sm font-medium text-slate-700">{{ $fields['description']['label'] ?? __('Description') }}</label>
            <textarea name="description" rows="4" class="erp-input mt-1 w-full" @required($fields['description']['required'] ?? false) @readonly($fields['description']['read_only'] ?? false)>{{ old('description', $activity?->description) }}</textarea>
        </div>
    @endif
</div>
@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $activity ?? null])
