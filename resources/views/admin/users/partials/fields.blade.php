@php($user = $user ?? null)

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

        <x-admin.form-field name="employee_id" :label="__('Linked employee')">
            <select id="employee_id" name="employee_id" class="erp-select w-full">
                <option value="">{{ __('None') }}</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('employee_id', $user?->employee_id) == $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_number }})</option>
                @endforeach
            </select>
        </x-admin.form-field>

        <x-admin.form-field name="role" :label="__('Role')" :required="true">
            <select id="role" name="role" class="erp-select w-full" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role', $user?->getRoleNames()->first()) === $role->name)>{{ $role->name }}</option>
                @endforeach
            </select>
        </x-admin.form-field>

        <x-admin.form-field name="is_active" :label="__('Active')" :colSpan="2">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-erp-border text-erp-accent focus:ring-erp-accent" @checked(old('is_active', $user?->is_active ?? true))>
                <span>{{ __('Active user account') }}</span>
            </label>
        </x-admin.form-field>
    </div>
</x-admin.form-section>
