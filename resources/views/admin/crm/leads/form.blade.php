@php
    $fields = $formFields ?? [];

    $registryFields = collect($fields)
        ->filter(fn (array $field) => ! ($field['is_custom'] ?? false) && ($field['visible'] ?? true))
        ->sortBy('sort_order');
@endphp

<div class="erp-form-grid">
    @foreach ($registryFields as $fieldKey => $field)
        @include('admin.crm.leads.partials.registry-field', [
            'fieldKey' => $fieldKey,
            'field' => $field,
            'lead' => $lead ?? null,
        ])
    @endforeach

    @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => $lead ?? null, 'formKey' => 'lead'])
</div>
