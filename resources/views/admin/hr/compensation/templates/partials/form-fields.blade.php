<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-admin.entity-code-input :record="$template" erp maxlength="30" />
    </div>
    <div>
        <label class="erp-label" for="template-name">{{ __('Name') }}</label>
        <input type="text" id="template-name" name="name" class="erp-input w-full" value="{{ old('name', $template?->name) }}" required>
    </div>
    <div>
        <label class="erp-label" for="template-basic-salary">{{ __('Basic salary') }}</label>
        <input type="number" step="0.01" min="0" id="template-basic-salary" name="basic_salary" class="erp-input w-full" value="{{ old('basic_salary', $template?->basic_salary) }}" required>
    </div>
    <div>
        <label class="erp-label" for="template-house">{{ __('House allowance') }}</label>
        <input type="number" step="0.01" min="0" id="template-house" name="house_allowance" class="erp-input w-full" value="{{ old('house_allowance', $template?->house_allowance ?? 0) }}">
    </div>
    <div>
        <label class="erp-label" for="template-transport">{{ __('Transport allowance') }}</label>
        <input type="number" step="0.01" min="0" id="template-transport" name="transport_allowance" class="erp-input w-full" value="{{ old('transport_allowance', $template?->transport_allowance ?? 0) }}">
    </div>
    <div>
        <label class="erp-label" for="template-medical">{{ __('Medical allowance') }}</label>
        <input type="number" step="0.01" min="0" id="template-medical" name="medical_allowance" class="erp-input w-full" value="{{ old('medical_allowance', $template?->medical_allowance ?? 0) }}">
    </div>
    <div>
        <label class="erp-label" for="template-risk">{{ __('Risk allowance') }}</label>
        <input type="number" step="0.01" min="0" id="template-risk" name="risk_allowance" class="erp-input w-full" value="{{ old('risk_allowance', $template?->risk_allowance ?? 0) }}">
    </div>
    <div>
        <label class="erp-label" for="template-responsibility">{{ __('Responsibility allowance') }}</label>
        <input type="number" step="0.01" min="0" id="template-responsibility" name="responsibility_allowance" class="erp-input w-full" value="{{ old('responsibility_allowance', $template?->responsibility_allowance ?? 0) }}">
    </div>
    <div>
        <label class="erp-label" for="template-payment-frequency">{{ __('Payment frequency') }}</label>
        <select id="template-payment-frequency" name="payment_frequency" class="erp-input w-full" required>
            @foreach ($paymentFrequencies as $frequency)
                <option value="{{ $frequency->value }}" @selected(old('payment_frequency', $template?->payment_frequency?->value ?? 'monthly') === $frequency->value)>{{ $frequency->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        @include('admin.hr.compensation.partials.payroll-group-select', [
            'value' => old('payroll_group', $template?->payroll_group ?? 'main'),
            'groups' => $payrollGroups,
        ])
    </div>
    <div>
        <label class="erp-label" for="template-currency">{{ __('Currency') }}</label>
        <input type="text" id="template-currency" name="currency" maxlength="3" class="erp-input w-full" value="{{ old('currency', $template?->currency ?? 'KES') }}" required>
    </div>
</div>

@if ($template)
    <label class="mt-4 flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $template->is_active))>
        {{ __('Active — available for new employee assignments') }}
    </label>
@endif
