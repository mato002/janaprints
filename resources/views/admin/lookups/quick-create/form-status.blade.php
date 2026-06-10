<x-admin.lookup-nested-form :title="$title" :action="$action" max-width="md">
    <input type="hidden" name="form_key" value="{{ $formKey }}">
    <input type="hidden" name="company_id" value="{{ $companyId }}">
    @if ($branchId)
        <input type="hidden" name="branch_id" value="{{ $branchId }}">
    @endif

    <p class="text-sm text-slate-600">
        {{ __('Add a new status option for :form.', ['form' => $formLabel]) }}
    </p>

    <div class="space-y-4">
        <div>
            <x-input-label for="value" :value="__('Value (code)')" />
            <x-text-input
                id="value"
                name="value"
                class="block mt-1 w-full font-mono"
                :value="old('value')"
                pattern="[a-z0-9_]+"
                placeholder="on_hold"
                required
            />
            <p class="mt-1 text-xs text-slate-500">{{ __('Lowercase letters, numbers, and underscores only.') }}</p>
        </div>
        <div>
            <x-input-label for="label" :value="__('Display label')" />
            <x-text-input id="label" name="label" class="block mt-1 w-full" :value="old('label')" required />
        </div>
    </div>
</x-admin.lookup-nested-form>
