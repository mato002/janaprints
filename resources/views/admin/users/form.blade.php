<x-admin-layout :title="$user ? __('Edit user') : __('Create user')" :breadcrumbs="[['label' => __('Users'), 'url' => route('admin.users.index')], ['label' => $user ? __('Edit') : __('Create')]]">
    <x-admin.card class="max-w-4xl">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            <x-admin.form-section :title="__('Account details')">
                <div class="erp-form-field">
                    <x-input-label for="name" :value="__('Name')" :required="true" />
                    <x-text-input id="name" name="name" class="mt-1 w-full" :value="old('name', $user?->name)" required />
                </div>
                <div class="erp-form-field">
                    <x-input-label for="email" :value="__('Email')" :required="true" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 w-full" :value="old('email', $user?->email)" required />
                </div>
                @if (! $user)
                    <div class="erp-form-field">
                        <x-input-label for="password" :value="__('Password')" :required="true" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 w-full" required />
                    </div>
                    <div class="erp-form-field">
                        <x-input-label for="password_confirmation" :value="__('Confirm password')" :required="true" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 w-full" required />
                    </div>
                @endif
                @if (auth()->user()->hasRole('Super Admin'))
                    <div class="erp-form-field">
                        <x-admin.lookup-company-select :companies="$companies" :value="old('company_id', $user?->company_id)" />
                    </div>
                @else
                    <input type="hidden" name="company_id" value="{{ auth()->user()->company_id }}">
                @endif
                <div class="erp-form-field">
                    <x-admin.lookup-select
                        name="default_branch_id"
                        :label="__('Default branch')"
                        :options="$branches"
                        :value="old('default_branch_id', $user?->default_branch_id)"
                        :required="true"
                        create-route="admin.branches.quick-create"
                        refresh-route="admin.lookups.branches"
                        permission="branches.manage"
                        :modal-title="__('Create branch')"
                        option-label-key="name"
                        select-class="erp-select mt-1"
                        scope-company-field="company_id"
                        :empty-option="false"
                    />
                </div>
                <div class="erp-form-field">
                    <x-input-label for="employee_id" :value="__('Linked employee')" />
                    <select id="employee_id" name="employee_id" class="erp-select mt-1">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id', $user?->employee_id) == $employee->id)>{{ $employee->full_name }} ({{ $employee->employee_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="erp-form-field">
                    <x-input-label for="role" :value="__('Role')" :required="true" />
                    <select id="role" name="role" class="erp-select mt-1" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role', $user?->getRoleNames()->first()) === $role->name)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="erp-form-field flex items-center gap-2 md:col-span-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="rounded border-erp-border text-erp-accent focus:ring-erp-accent" @checked(old('is_active', $user?->is_active ?? true))>
                    <x-input-label for="is_active" :value="__('Active')" class="!mb-0" />
                </div>
            </x-admin.form-section>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-erp-border pt-6">
                <x-primary-button>{{ $user ? __('Update') : __('Create') }}</x-primary-button>
                <a href="{{ route('admin.users.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>

        @if ($user)
            @can('resetPassword', $user)
                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" id="reset-password" class="mt-8 pt-8 border-t">
                    @csrf @method('PATCH')
                    <h3 class="font-medium text-gray-800 mb-3">{{ __('Reset password') }}</h3>
                    <div class="space-y-3">
                        <x-text-input name="password" type="password" class="block w-full" placeholder="{{ __('New password') }}" required />
                        <x-text-input name="password_confirmation" type="password" class="block w-full" placeholder="{{ __('Confirm') }}" required />
                        <x-primary-button>{{ __('Reset password') }}</x-primary-button>
                    </div>
                </form>
            @endcan
            @can('toggleActive', $user)
                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="mt-4">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-sm text-amber-700 hover:underline">
                        {{ $user->is_active ? __('Deactivate user') : __('Activate user') }}
                    </button>
                </form>
            @endcan
            @can('delete', $user)
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-4" onsubmit="return confirm('{{ __('Delete this user?') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete user') }}</button>
                </form>
            @endcan
        @endif
    </x-admin.card>
</x-admin-layout>
