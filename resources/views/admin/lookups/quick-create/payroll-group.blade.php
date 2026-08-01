<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="md">
    <div class="space-y-4">
        <div>
            <label class="erp-label" for="payroll-group-name">{{ __('Name') }}</label>
            <input type="text" id="payroll-group-name" name="name" class="erp-input w-full" value="{{ old('name') }}" required>
        </div>
        <div>
            <label class="erp-label" for="payroll-group-code">{{ __('Code') }}</label>
            <input type="text" id="payroll-group-code" name="code" class="erp-input w-full" value="{{ old('code') }}" maxlength="30" placeholder="{{ __('Auto-generated') }}">
        </div>
    </div>
</x-admin.lookup-nested-form>
