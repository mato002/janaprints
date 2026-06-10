@props(['fieldKey', 'field', 'lead' => null])

@php
    $required = (bool) ($field['required'] ?? false);
    $readOnly = (bool) ($field['read_only'] ?? false);
    $label = __($field['label'] ?? $fieldKey);
    $type = $field['type'] ?? 'text';

    $modelValue = match ($fieldKey) {
        'status' => $lead?->status?->value,
        'expected_close_date' => $lead?->expected_close_date?->format('Y-m-d'),
        default => $lead?->{$fieldKey} ?? null,
    };

    $value = old($fieldKey, $modelValue ?? ($field['default'] ?? ''));
@endphp

<div @class([
    'erp-form-field',
    'md:col-span-2' => $type === 'textarea',
])>
    @switch($fieldKey)
        @case('lead_source_id')
<<<<<<< Updated upstream
            <x-input-label for="{{ $fieldKey }}" :value="$label" :required="$required" />
            <select
                id="{{ $fieldKey }}"
                name="{{ $fieldKey }}"
                class="erp-select mt-1 w-full"
                @required($required)
                @disabled($readOnly)
            >
                <option value="">{{ __('None') }}</option>
                @foreach ($sources as $source)
                    <option value="{{ $source->id }}" @selected((string) $value === (string) $source->id)>{{ $source->name }}</option>
                @endforeach
            </select>
            <x-admin.field-error :name="$fieldKey" />
=======
            <x-admin.lookup-select
                :name="$fieldKey"
                :label="$label"
                :options="$sources"
                :value="$value"
                :required="$required"
                :readonly="$readOnly"
                create-route="admin.crm.lead-sources.quick-create"
                refresh-route="admin.lookups.lead_sources"
                permission="crm.leads.create"
                :modal-title="__('Create lead source')"
                option-label-key="name"
                option-value-key="id"
                select-class="erp-select mt-1 w-full"
                :empty-label="__('None')"
            />
>>>>>>> Stashed changes
            @break

        @case('stage_id')
            <x-input-label for="{{ $fieldKey }}" :value="$label" :required="$required" />
            <select
                id="{{ $fieldKey }}"
                name="{{ $fieldKey }}"
                class="erp-select mt-1 w-full"
                @required($required)
                @disabled($readOnly)
            >
                @foreach ($stages as $stage)
                    <option value="{{ $stage->id }}" @selected((string) $value === (string) $stage->id)>{{ $stage->name }}</option>
                @endforeach
            </select>
            <x-admin.field-error :name="$fieldKey" />
            @break

        @case('status')
<<<<<<< Updated upstream
            <x-input-label for="{{ $fieldKey }}" :value="$label" :required="$required" />
            <select
                id="{{ $fieldKey }}"
                name="{{ $fieldKey }}"
                class="erp-select mt-1 w-full"
                @required($required)
                @disabled($readOnly)
            >
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected((string) $value === (string) $status->value)>{{ $status->name }}</option>
                @endforeach
            </select>
            <x-admin.field-error :name="$fieldKey" />
=======
            <x-admin.form-status-select
                form-key="lead"
                :name="$fieldKey"
                :field="$field"
                :value="$lead?->status ?? ($field['default'] ?? null)"
                :model="$lead"
                select-class="erp-select mt-1 w-full"
            />
>>>>>>> Stashed changes
            @break

        @case('assigned_to')
            <x-input-label for="{{ $fieldKey }}" :value="$label" :required="$required" />
            <select
                id="{{ $fieldKey }}"
                name="{{ $fieldKey }}"
                class="erp-select mt-1 w-full"
                @required($required)
                @disabled($readOnly)
            >
                <option value="">{{ __('Unassigned') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) $value === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <x-admin.field-error :name="$fieldKey" />
            @break

        @case('notes')
            <x-input-label for="{{ $fieldKey }}" :value="$label" :required="$required" />
            <textarea
                id="{{ $fieldKey }}"
                name="{{ $fieldKey }}"
                class="erp-input mt-1 w-full"
                rows="3"
                @required($required)
                @readonly($readOnly)
            >{{ $value }}</textarea>
            <x-admin.field-error :name="$fieldKey" />
            @break

        @default
            @php
                $inputType = match ($type) {
                    'email' => 'email',
                    'number' => 'number',
                    'date' => 'date',
                    default => 'text',
                };
            @endphp
            <x-input-label for="{{ $fieldKey }}" :value="$label" :required="$required" />
            @if ($fieldKey === 'estimated_value')
                <x-text-input
                    :id="$fieldKey"
                    :name="$fieldKey"
                    type="number"
                    step="0.01"
                    class="block mt-1 w-full"
                    :value="$value"
                    :required="$required"
                    :readonly="$readOnly"
                />
            @elseif ($fieldKey === 'probability')
                <x-text-input
                    :id="$fieldKey"
                    :name="$fieldKey"
                    type="number"
                    min="0"
                    max="100"
                    class="block mt-1 w-full"
                    :value="$value"
                    :required="$required"
                    :readonly="$readOnly"
                />
            @else
                <x-text-input
                    :id="$fieldKey"
                    :name="$fieldKey"
                    :type="$inputType"
                    class="block mt-1 w-full"
                    :value="$value"
                    :required="$required"
                    :readonly="$readOnly"
                />
            @endif
            <x-admin.field-error :name="$fieldKey" />
    @endswitch
</div>
