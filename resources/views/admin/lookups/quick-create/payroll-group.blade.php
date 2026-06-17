<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="md">
    <div class="space-y-4">
        <div>
            <label class="erp-label" for="payroll-group-name">{{ __('Name') }}</label>
            <input type="text" id="payroll-group-name" name="name" class="erp-input w-full" value="{{ old('name') }}" required>
        </div>
        <div>
            <label class="erp-label" for="payroll-group-code">{{ __('Code') }}</label>
            <input type="text" id="payroll-group-code" name="code" class="erp-input w-full" value="{{ old('code') }}" maxlength="30">
            <p class="mt-1 text-xs text-slate-500">{{ __('Optional. Auto-generated from the name if left blank.') }}</p>
        </div>
    </div>
</x-admin.lookup-nested-form>
