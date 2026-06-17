<x-admin.modal-form
    :title="__('New deduction type')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')],
        ['label' => __('Deductions'), 'url' => route('admin.hr.compensation.deductions')],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.compensation.deductions.store')">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label" for="deduction-code">{{ __('Code') }}</label>
                <input type="text" id="deduction-code" name="code" class="erp-input w-full" value="{{ old('code') }}" required>
            </div>
            <div>
                <label class="erp-label" for="deduction-name">{{ __('Name') }}</label>
                <input type="text" id="deduction-name" name="name" class="erp-input w-full" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="erp-label" for="deduction-category">{{ __('Category') }}</label>
                <input type="text" id="deduction-category" name="category" class="erp-input w-full" value="{{ old('category', 'custom') }}">
            </div>
            <div>
                <label class="erp-label" for="deduction-calculation-type">{{ __('Calculation type') }}</label>
                <select id="deduction-calculation-type" name="calculation_type" class="erp-input w-full" required>
                    @foreach ($calculationTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('calculation_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="deduction-frequency">{{ __('Frequency') }}</label>
                <select id="deduction-frequency" name="frequency" class="erp-input w-full" required>
                    @foreach ($frequencies as $freq)
                        <option value="{{ $freq->value }}" @selected(old('frequency', 'recurring') === $freq->value)>{{ $freq->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="deduction-default-amount">{{ __('Default amount') }}</label>
                <input type="number" step="0.01" min="0" id="deduction-default-amount" name="default_amount" class="erp-input w-full" value="{{ old('default_amount', 0) }}">
            </div>
        </div>

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Add deduction') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
