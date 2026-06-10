@props(['fields' => [], 'model' => null, 'formKey' => null])

@php
    $customFields = collect($fields)->filter(
        fn (array $field) => ($field['is_custom'] ?? false)
            && ! ($field['registry_required'] ?? false)
            && ($field['visible'] ?? true),
    );
@endphp

@if ($customFields->isNotEmpty())
    <div class="md:col-span-2 border-t border-erp-border pt-4 mt-2">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-violet-700">{{ __('Custom fields') }}</p>
        <div class="erp-form-grid">
            @foreach ($customFields as $fieldKey => $field)
                @php
                    $inputId = 'custom_'.$fieldKey;
                    $value = old($fieldKey, $field['default'] ?? '');
                    $required = $field['required'] ?? false;
                    $readOnly = $field['read_only'] ?? false;
                @endphp

                @if (($field['type'] ?? 'text') === 'textarea')
                    <div class="erp-form-field md:col-span-2">
                        <x-input-label :for="$inputId" :value="__($field['label'])" :required="$required" />
                        <textarea
                            id="{{ $inputId }}"
                            name="{{ $fieldKey }}"
                            class="erp-input mt-1 w-full"
                            rows="3"
                            @required($required)
                            @readonly($readOnly)
                        >{{ $value }}</textarea>
                        <x-admin.field-error :name="$fieldKey" />
                    </div>
                @elseif (($field['is_status_field'] ?? false) && $formKey)
                    <x-admin.form-status-select
                        :form-key="$formKey"
                        :name="$fieldKey"
                        :field="$field"
                        :value="old($fieldKey, $model?->{$fieldKey} ?? ($field['default'] ?? null))"
                        :model="$model"
                        select-class="erp-input mt-1 w-full"
                    />
                @elseif (($field['type'] ?? 'text') === 'checkbox')
                    <div class="flex items-center gap-2">
                        <input
                            type="hidden"
                            name="{{ $fieldKey }}"
                            value="0"
                            @disabled($readOnly)
                        >
                        <input
                            type="checkbox"
                            id="{{ $inputId }}"
                            name="{{ $fieldKey }}"
                            value="1"
                            class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                            @checked(filter_var($value, FILTER_VALIDATE_BOOLEAN))
                            @disabled($readOnly)
                        >
                        <x-input-label :for="$inputId" :value="__($field['label'])" class="!mb-0" />
                        <x-admin.field-error :name="$fieldKey" />
                    </div>
                @else
                    <div class="erp-form-field">
                        <x-input-label :for="$inputId" :value="__($field['label'])" :required="$required" />
                        <x-text-input
                            :id="$inputId"
                            :name="$fieldKey"
                            class="block mt-1 w-full"
                            :type="match ($field['type'] ?? 'text') {
                                'email' => 'email',
                                'number' => 'number',
                                'date' => 'date',
                                default => 'text',
                            }"
                            :value="$value"
                            :required="$required"
                            :readonly="$readOnly"
                        />
                        <x-admin.field-error :name="$fieldKey" />
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
