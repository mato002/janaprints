@php
    $activity = $activity ?? null;
    $fields = $formFields ?? [];
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @if (($fields['customer_id']['visible'] ?? true))
        <x-admin.lookup-select
            name="customer_id"
            :label="$fields['customer_id']['label'] ?? __('Customer')"
            :options="$customers"
            :value="old('customer_id', $presetCustomerId ?? $activity?->customer_id)"
            :required="($fields['customer_id']['required'] ?? false)"
            :readonly="($fields['customer_id']['read_only'] ?? false)"
            create-route="admin.crm.customers.quick-create"
            refresh-route="admin.lookups.customers"
            permission="crm.customers.create"
            :modal-title="__('Create customer')"
            option-label-key="company_name"
            option-value-key="id"
            select-class="erp-input mt-1 w-full"
            :placeholder="__('— None —')"
        />
    @endif
    @if (($fields['lead_id']['visible'] ?? true))
        <x-admin.lookup-select
            name="lead_id"
            :label="$fields['lead_id']['label'] ?? __('Lead')"
            :options="$leads"
            :value="old('lead_id', $presetLeadId ?? $activity?->lead_id)"
            :required="($fields['lead_id']['required'] ?? false)"
            :readonly="($fields['lead_id']['read_only'] ?? false)"
            create-route="admin.crm.leads.quick-create"
            refresh-route="admin.lookups.leads"
            permission="crm.leads.create"
            :modal-title="__('Create lead')"
            option-label-key="lead_name"
            option-value-key="id"
            select-class="erp-input mt-1 w-full"
            :placeholder="__('— None —')"
        />
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
        <x-admin.form-status-select
            form-key="activity.create"
            :field="$fields['status']"
            :value="$activity?->status ?? ($fields['status']['default'] ?? 'completed')"
            :model="$activity"
        />
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
@include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $activity ?? null, 'formKey' => 'activity.create'])
