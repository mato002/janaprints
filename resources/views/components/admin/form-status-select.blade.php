@props([
    'formKey',
    'name' => 'status',
    'field' => [],
    'value' => null,
    'model' => null,
    'selectClass' => 'erp-input mt-1 w-full',
    'companyId' => null,
    'branchId' => null,
    'allowEmpty' => false,
    'emptyLabel' => null,
    'showLabel' => true,
])

@php
    use App\Support\Platform\FormStatusOptionService;

    $statusService = app(FormStatusOptionService::class);
    $companyId ??= old('company_id', $model?->company_id ?? tenant()->companyId() ?? auth()->user()?->company_id);
    $branchId ??= old('branch_id', $model?->branch_id ?? tenant()->branchId());
    $options = $statusService->optionsFor($formKey, $companyId, $branchId);
    $selectedValue = FormStatusOptionService::valueOf(old($name, $value ?? ($field['default'] ?? null)));
    $label = __($field['label'] ?? __('Status'));
    $required = (bool) ($field['required'] ?? false);
    $readOnly = (bool) ($field['read_only'] ?? false);
    $inputId = $attributes->get('id', $name);

    if ($selectedValue && ! $options->contains(fn ($option) => $option->value === $selectedValue)) {
        $options->push(new \App\Models\Platform\FormStatusOption([
            'value' => $selectedValue,
            'label' => $statusService->labelFor($selectedValue, $formKey, $companyId, $branchId),
            'is_active' => false,
        ]));
    }

    $normalizedOptions = $options->map(fn ($option) => [
        'value' => $option->value,
        'label' => $option->label,
    ])->values()->all();

    $canCreate = ! $allowEmpty
        && ! $readOnly
        && $statusService->formHasConfigurableStatus($formKey)
        && (auth()->user()?->can('settings.manage') || auth()->user()?->can('update', new \App\Models\Platform\SettingsGovernance()))
        && Route::has('admin.form-statuses.quick-create');

    $scopeQuery = array_filter([
        'form_key' => $formKey,
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ], fn ($value) => $value !== null && $value !== '');

    $createUrl = $canCreate
        ? route('admin.form-statuses.quick-create', $scopeQuery)
        : null;
    $refreshUrl = Route::has('admin.lookups.form_statuses')
        ? route('admin.lookups.form_statuses', $scopeQuery)
        : null;
@endphp

<div
    @if ($canCreate)
        x-data="erpLookupCreate({
            name: @js($name),
            selected: @js($selectedValue !== null && $selectedValue !== '' ? (string) $selectedValue : ''),
            options: @js($normalizedOptions),
            createUrl: @js($createUrl),
            refreshUrl: @js($refreshUrl),
            modalTitle: @js(__('Add status')),
            scopeCompanyField: @js('company_id'),
            scopeBranchField: @js('branch_id'),
            scopeFormKey: @js($formKey),
        })"
    @endif
    {{ $attributes->except(['id', 'class'])->merge(['class' => ($showLabel ? 'erp-form-field ' : '').($canCreate ? 'erp-lookup-select' : '')]) }}
>
    @if ($showLabel)
        <label
            for="{{ $inputId }}"
            @class([
                'text-sm font-medium text-slate-700',
                'required' => $required && ! $allowEmpty,
            ])
        >
            {{ $label }}<x-admin.required-star :required="$required && ! $allowEmpty" />
        </label>
    @endif
    <div @class(['erp-lookup-select__row mt-1 flex items-stretch gap-2' => $canCreate, 'mt-1' => ! $canCreate && $showLabel])>
        <select
            id="{{ $inputId }}"
            name="{{ $name }}"
            @class([
                $selectClass,
                'erp-lookup-select__input min-w-0 flex-1' => $canCreate,
            ])
            @if ($canCreate) x-model="selected" @endif
            @required($required && ! $allowEmpty)
            @disabled($readOnly)
        >
            @if ($allowEmpty)
                <option value="">{{ $emptyLabel ?? __('All') }}</option>
            @endif
            @foreach ($normalizedOptions as $option)
                <option value="{{ $option['value'] }}" @selected($selectedValue === $option['value'])>{{ $option['label'] }}</option>
            @endforeach
        </select>
        @if ($canCreate)
            <button
                type="button"
                class="erp-lookup-select__add"
                @click.stop.prevent="openCreate($event)"
                :aria-label="@js(__('Add status'))"
                :title="@js(__('Add status'))"
            >
                <span aria-hidden="true">+</span>
            </button>
        @endif
    </div>
</div>
