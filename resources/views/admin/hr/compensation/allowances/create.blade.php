<x-admin.modal-form
    :title="__('New allowance type')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')],
        ['label' => __('Allowances'), 'url' => route('admin.hr.compensation.allowances')],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.compensation.allowances.store')">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-admin.entity-code-input erp maxlength="30" />
            </div>
            <div>
                <label class="erp-label" for="allowance-name">{{ __('Name') }}</label>
                <input type="text" id="allowance-name" name="name" class="erp-input w-full" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="erp-label" for="allowance-calculation-type">{{ __('Calculation type') }}</label>
                <select id="allowance-calculation-type" name="calculation_type" class="erp-input w-full" required>
                    @foreach ($calculationTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('calculation_type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="allowance-frequency">{{ __('Frequency') }}</label>
                <select id="allowance-frequency" name="frequency" class="erp-input w-full" required>
                    @foreach ($frequencies as $freq)
                        <option value="{{ $freq->value }}" @selected(old('frequency', 'recurring') === $freq->value)>{{ $freq->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label" for="allowance-default-amount">{{ __('Default amount') }}</label>
                <input type="number" step="0.01" min="0" id="allowance-default-amount" name="default_amount" class="erp-input w-full" value="{{ old('default_amount', 0) }}">
            </div>
            <div>
                <label class="erp-label" for="allowance-percentage-rate">{{ __('Percentage %') }}</label>
                <input type="number" step="0.01" min="0" max="100" id="allowance-percentage-rate" name="percentage_rate" class="erp-input w-full" value="{{ old('percentage_rate') }}">
            </div>
        </div>

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Add allowance') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
