@php
    $fields = $formFields ?? [];
    $defaultPeriodStart = now()->startOfMonth()->toDateString();
    $defaultPeriodEnd = now()->endOfMonth()->toDateString();
@endphp
<x-admin-layout :title="__('New Payroll Run')" :breadcrumbs="[['label' => __('Payroll'), 'url' => route('admin.hr.payroll.dashboard')], ['label' => __('New Run')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-3xl">
        <x-admin.form-shell :action="route('admin.hr.payroll.store')">
            <div class="erp-form-grid">
                @if (($fields['branch_id']['visible'] ?? true))
                    <x-admin.form-field
                        name="branch_id"
                        :label="$fields['branch_id']['label'] ?? __('Branch')"
                        :required="($fields['branch_id']['required'] ?? false)"
                        :readonly="($fields['branch_id']['read_only'] ?? false)"
                        colSpan="2"
                    >
                        <select
                            name="branch_id"
                            class="erp-select w-full"
                            @required($fields['branch_id']['required'] ?? false)
                            @disabled($fields['branch_id']['read_only'] ?? false)
                        >
                            <option value="">{{ __('All branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $fields['branch_id']['default'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.form-field>
                @endif

                <x-admin.form-field
                    name="payroll_group"
                    :label="__('Payroll group')"
                    required
                    colSpan="2"
                >
                    <select name="payroll_group" class="erp-select w-full" required>
                        @foreach ($payrollGroups as $group)
                            <option value="{{ $group->code }}" @selected(old('payroll_group', 'main') === $group->code)>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </x-admin.form-field>

                @if (($fields['period_start']['visible'] ?? true))
                    <x-admin.input
                        name="period_start"
                        type="date"
                        :label="$fields['period_start']['label'] ?? __('Period start')"
                        :value="old('period_start', $fields['period_start']['default'] ?? $defaultPeriodStart)"
                        :required="($fields['period_start']['required'] ?? true)"
                        :readonly="($fields['period_start']['read_only'] ?? false)"
                    />
                @endif

                @if (($fields['period_end']['visible'] ?? true))
                    <x-admin.input
                        name="period_end"
                        type="date"
                        :label="$fields['period_end']['label'] ?? __('Period end')"
                        :value="old('period_end', $fields['period_end']['default'] ?? $defaultPeriodEnd)"
                        :required="($fields['period_end']['required'] ?? true)"
                        :readonly="($fields['period_end']['read_only'] ?? false)"
                    />
                @endif

                @if (($fields['pay_date']['visible'] ?? true))
                    <x-admin.input
                        name="pay_date"
                        type="date"
                        :label="$fields['pay_date']['label'] ?? __('Pay date')"
                        :value="old('pay_date', $fields['pay_date']['default'] ?? $defaultPeriodEnd)"
                        :required="($fields['pay_date']['required'] ?? true)"
                        :readonly="($fields['pay_date']['read_only'] ?? false)"
                    />
                @endif

                @if (($fields['notes']['visible'] ?? true))
                    <x-admin.textarea
                        name="notes"
                        :label="$fields['notes']['label'] ?? __('Notes')"
                        :value="old('notes', $fields['notes']['default'] ?? '')"
                        :required="($fields['notes']['required'] ?? false)"
                        :readonly="($fields['notes']['read_only'] ?? false)"
                        colSpan="2"
                        rows="2"
                    />
                @endif
            </div>

            <div class="mt-6">
                <x-primary-button>{{ __('Create payroll run') }}</x-primary-button>
            </div>
        </x-admin.form-shell>
    </div>
</x-admin-layout>
