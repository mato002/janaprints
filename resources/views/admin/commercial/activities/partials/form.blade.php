@php
    $activity = $activity ?? null;
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('Customer') }}</label>
        <select name="customer_id" class="erp-input mt-1 w-full">
            <option value="">{{ __('— None —') }}</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $presetCustomerId ?? $activity?->customer_id) == $customer->id)>{{ $customer->company_name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('Lead') }}</label>
        <select name="lead_id" class="erp-input mt-1 w-full">
            <option value="">{{ __('— None —') }}</option>
            @foreach ($leads as $lead)
                <option value="{{ $lead->id }}" @selected(old('lead_id', $presetLeadId ?? $activity?->lead_id) == $lead->id)>{{ $lead->lead_name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('Activity type') }}</label>
        <select name="activity_type" class="erp-input mt-1 w-full" required>
            @foreach ($activityTypes as $type)
                <option value="{{ $type->value }}" @selected(old('activity_type', $activity?->activity_type?->value) === $type->value)>{{ ucfirst(str_replace('_', ' ', $type->value)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('Status') }}</label>
        <select name="status" class="erp-input mt-1 w-full" required>
            @foreach ($activityStatuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $activity?->status?->value ?? 'completed') === $status->value)>{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('Assigned to') }}</label>
        <select name="user_id" class="erp-input mt-1 w-full">
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $activity?->user_id ?? auth()->id()) == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm font-medium text-slate-700">{{ __('When') }}</label>
        <input type="datetime-local" name="activity_at" class="erp-input mt-1 w-full" required value="{{ old('activity_at', ($activity?->activity_at ?? now())->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700">{{ __('Subject') }}</label>
        <input type="text" name="subject" class="erp-input mt-1 w-full" required maxlength="255" value="{{ old('subject', $activity?->subject) }}">
    </div>
    <div class="md:col-span-2">
        <label class="text-sm font-medium text-slate-700">{{ __('Description') }}</label>
        <textarea name="description" rows="4" class="erp-input mt-1 w-full">{{ old('description', $activity?->description) }}</textarea>
    </div>
</div>
