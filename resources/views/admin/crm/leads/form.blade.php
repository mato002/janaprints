@php($fields = $formFields ?? [])
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @if(($fields['lead_name']['visible'] ?? true))
    <div><x-input-label for="lead_name" :value="__('Lead name')" /><x-text-input id="lead_name" name="lead_name" class="block mt-1 w-full" :value="old('lead_name', $lead?->lead_name ?? ($fields['lead_name']['default'] ?? ''))" :required="($fields['lead_name']['required'] ?? true)" :readonly="($fields['lead_name']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['company_name']['visible'] ?? true))
    <div><x-input-label for="company_name" :value="__('Company name')" /><x-text-input id="company_name" name="company_name" class="block mt-1 w-full" :value="old('company_name', $lead?->company_name ?? ($fields['company_name']['default'] ?? ''))" :required="($fields['company_name']['required'] ?? false)" :readonly="($fields['company_name']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['lead_source_id']['visible'] ?? true))
    <div><x-input-label for="lead_source_id" :value="__('Source')" />
        <select name="lead_source_id" class="block mt-1 w-full rounded-md border-gray-300" @required($fields['lead_source_id']['required'] ?? false) @disabled($fields['lead_source_id']['read_only'] ?? false)>
            <option value="">{{ __('None') }}</option>
            @foreach ($sources as $s)<option value="{{ $s->id }}" @selected(old('lead_source_id', $lead?->lead_source_id) == $s->id)>{{ $s->name }}</option>@endforeach
        </select></div>
    @endif
    @if(($fields['stage_id']['visible'] ?? true))
    <div><x-input-label for="stage_id" :value="__('Stage')" />
        <select name="stage_id" class="block mt-1 w-full rounded-md border-gray-300" @required($fields['stage_id']['required'] ?? false) @disabled($fields['stage_id']['read_only'] ?? false)>
            @foreach ($stages as $st)<option value="{{ $st->id }}" @selected(old('stage_id', $lead?->stage_id) == $st->id)>{{ $st->name }}</option>@endforeach
        </select></div>
    @endif
    @if(($fields['estimated_value']['visible'] ?? true))
    <div><x-input-label for="estimated_value" :value="__('Estimated value')" /><x-text-input id="estimated_value" name="estimated_value" type="number" step="0.01" class="block mt-1 w-full" :value="old('estimated_value', $lead?->estimated_value ?? ($fields['estimated_value']['default'] ?? ''))" :required="($fields['estimated_value']['required'] ?? false)" :readonly="($fields['estimated_value']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['probability']['visible'] ?? true))
    <div><x-input-label for="probability" :value="__('Probability %')" /><x-text-input id="probability" name="probability" type="number" min="0" max="100" class="block mt-1 w-full" :value="old('probability', $lead?->probability ?? ($fields['probability']['default'] ?? ''))" :required="($fields['probability']['required'] ?? false)" :readonly="($fields['probability']['read_only'] ?? false)" /></div>
    @endif
    @if(($fields['status']['visible'] ?? true))
    <div><x-input-label for="status" :value="__('Status')" />
        <select name="status" class="block mt-1 w-full rounded-md border-gray-300" @required($fields['status']['required'] ?? true) @disabled($fields['status']['read_only'] ?? false)>
            @foreach ($statuses as $s)<option value="{{ $s->value }}" @selected(old('status', $lead?->status?->value) === $s->value)>{{ $s->name }}</option>@endforeach
        </select></div>
    @endif
    @if(($fields['assigned_to']['visible'] ?? true))
    <div><x-input-label for="assigned_to" :value="__('Assigned to')" />
        <select name="assigned_to" class="block mt-1 w-full rounded-md border-gray-300" @required($fields['assigned_to']['required'] ?? false) @disabled($fields['assigned_to']['read_only'] ?? false)>
            <option value="">{{ __('Unassigned') }}</option>
            @foreach ($users as $u)<option value="{{ $u->id }}" @selected(old('assigned_to', $lead?->assigned_to) == $u->id)>{{ $u->name }}</option>@endforeach
        </select></div>
    @endif
    @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $lead ?? null])
</div>
