<x-admin-layout :title="$employee ? __('Revise Compensation') : __('New Compensation')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')], ['label' => $employee ? $employee->full_name : __('New')]]">
    <x-admin.page-header :title="$employee ? __('Revise Compensation') : __('Assign Compensation')" :description="$employee ? __('Creates a new effective-dated record. Previous compensation is preserved in history.') : null" />

    <form method="POST" action="{{ $action }}" class="erp-card max-w-3xl space-y-4">
        @csrf
        @if ($employee)
            @method('PUT')
        @endif

        @if (! $employee)
            <div>
                <label class="erp-label">{{ __('Employee') }}</label>
                <select name="employee_id" class="erp-input w-full" required>
                    <option value="">{{ __('Select employee') }}</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>{{ $emp->employee_number }} — {{ $emp->full_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="erp-label">{{ __('Effective Date') }}</label>
                <input type="date" name="effective_from" class="erp-input w-full" value="{{ old('effective_from', now()->toDateString()) }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Basic Salary') }}</label>
                <input type="number" step="0.01" min="0" name="basic_salary" class="erp-input w-full" value="{{ old('basic_salary', $compensation?->basic_salary ?? '') }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('House Allowance') }}</label>
                <input type="number" step="0.01" min="0" name="house_allowance" class="erp-input w-full" value="{{ old('house_allowance', $compensation?->house_allowance ?? 0) }}">
            </div>
            <div>
                <label class="erp-label">{{ __('Transport Allowance') }}</label>
                <input type="number" step="0.01" min="0" name="transport_allowance" class="erp-input w-full" value="{{ old('transport_allowance', $compensation?->transport_allowance ?? 0) }}">
            </div>
            <div>
                <label class="erp-label">{{ __('Medical Allowance') }}</label>
                <input type="number" step="0.01" min="0" name="medical_allowance" class="erp-input w-full" value="{{ old('medical_allowance', $compensation?->medical_allowance ?? 0) }}">
            </div>
            <div>
                <label class="erp-label">{{ __('Risk Allowance') }}</label>
                <input type="number" step="0.01" min="0" name="risk_allowance" class="erp-input w-full" value="{{ old('risk_allowance', $compensation?->risk_allowance ?? 0) }}">
            </div>
            <div>
                <label class="erp-label">{{ __('Responsibility Allowance') }}</label>
                <input type="number" step="0.01" min="0" name="responsibility_allowance" class="erp-input w-full" value="{{ old('responsibility_allowance', $compensation?->responsibility_allowance ?? 0) }}">
            </div>
            <div>
                <label class="erp-label">{{ __('Payment Frequency') }}</label>
                <select name="payment_frequency" class="erp-input w-full" required>
                    @foreach ($paymentFrequencies as $freq)
                        <option value="{{ $freq->value }}" @selected(old('payment_frequency', $compensation?->payment_frequency?->value ?? 'monthly') === $freq->value)>{{ $freq->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Payroll Group') }}</label>
                <select name="payroll_group" class="erp-input w-full" required>
                    @foreach ($payrollGroups as $group)
                        <option value="{{ $group->value }}" @selected(old('payroll_group', $compensation?->payroll_group?->value ?? 'main') === $group->value)>{{ $group->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Currency') }}</label>
                <input type="text" name="currency" maxlength="3" class="erp-input w-full" value="{{ old('currency', $compensation?->currency ?? 'KES') }}" required>
            </div>
            <div>
                <label class="erp-label">{{ __('Salary Template') }}</label>
                <select name="salary_template_id" class="erp-input w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected((int) old('salary_template_id', $compensation?->salary_template_id) === $template->id)>{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($employee)
            <div>
                <label class="erp-label">{{ __('Change Reason') }}</label>
                <textarea name="change_reason" class="erp-input w-full" rows="2">{{ old('change_reason') }}</textarea>
            </div>
        @endif

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="require_approval" value="1" class="rounded border-slate-300">
            {{ __('Require approval before activation') }}
        </label>

        <div class="flex gap-2">
            <button type="submit" class="erp-btn-primary">{{ $employee ? __('Save revision') : __('Create compensation') }}</button>
            <a href="{{ route('admin.hr.compensation.register') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
