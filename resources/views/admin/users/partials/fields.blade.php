@php($user = $user ?? null)

@if (! $user)
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-medium">{{ __('Staff must be created via HR') }}</p>
        <p class="mt-1">{{ __('Use HR → People → Create employee for new hires. This form is only for system accounts or linking login to an existing employee without a user account.') }}</p>
    </div>
@endif

<x-admin.form-section :title="__('Account details')">
    <div class="erp-form-grid">
        <x-admin.input
            name="name"
            :label="__('Name')"
            :value="old('name', $user?->name)"
            :required="true"
        />

        <x-admin.input
            name="email"
            type="email"
            :label="__('Email')"
            :value="old('email', $user?->email)"
            :required="true"
        />

        @if (! $user)
            <x-admin.input
                name="password"
                type="password"
                :label="__('Password')"
                :required="true"
            />

            <x-admin.input
                name="password_confirmation"
                type="password"
                :label="__('Confirm password')"
                :required="true"
            />
        @endif

        @if (auth()->user()->hasRole('Super Admin'))
            <x-admin.form-field name="company_id" :label="__('Company')" :required="true">
                <select id="company_id" name="company_id" class="erp-select w-full" required>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $user?->company_id) == $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </x-admin.form-field>
        @else
            <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
        @endif

        <x-admin.form-field name="default_branch_id" :label="__('Default branch')" :required="true">
            <select id="default_branch_id" name="default_branch_id" class="erp-select w-full" required>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('default_branch_id', $user?->default_branch_id) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>

        @if (! $user || ! $user->employee_id)
            <x-admin.form-field name="employee_id" :label="__('Linked employee')">
                <select id="employee_id" name="employee_id" class="erp-select w-full">
                    <option value="">{{ __('None') }}</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id', $user?->employee_id) == $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_number }})</option>
                    @endforeach
                </select>
                @if (! $user)
                    <p class="mt-1 text-xs text-gray-500">{{ __('Only employees without an existing login account are listed.') }}</p>
                @endif
            </x-admin.form-field>
        @else
            <x-admin.form-field name="employee_id" :label="__('Linked employee')">
                <input type="hidden" name="employee_id" value="{{ $user->employee_id }}">
                <p class="text-sm text-gray-900">
                    {{ $user->employee?->full_name }} ({{ $user->employee?->employee_number }})
                </p>
                <p class="mt-1 text-xs text-gray-500">{{ __('Staff accounts stay linked to their HR employee record.') }}</p>
            </x-admin.form-field>
        @endif

        <x-admin.form-field name="role" :label="__('Role')" :required="true">
            <select id="role" name="role" class="erp-select w-full" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role', $user?->getRoleNames()->first()) === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>

        @if (! $user)
            <x-admin.form-field name="system_account" :label="__('System account')" :colSpan="2">
                <input type="hidden" name="system_account" value="0">
                <label class="inline-flex items-start gap-2 text-sm">
                    <input
                        type="checkbox"
                        id="system_account"
                        name="system_account"
                        value="1"
                        class="mt-0.5 rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                        @checked(old('system_account'))
                    >
                    <span>
                        {{ __('This login is not a staff member (no employee record).') }}
                        <span class="mt-1 block text-xs text-gray-500">{{ __('Required when no employee is linked. Do not use for hires — use HR → Create employee instead.') }}</span>
                    </span>
                </label>
            </x-admin.form-field>
        @endif

        <x-admin.form-field name="is_active" :label="__('Active')" :colSpan="2">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-erp-border text-erp-accent focus:ring-erp-accent" @checked(old('is_active', $user?->is_active ?? true))>
                <span>{{ __('Active user account') }}</span>
            </label>
        </x-admin.form-field>
    </div>
</x-admin.form-section>
