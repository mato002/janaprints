@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $indexUrl = route('admin.settings.company-email.index', $scopeQuery);
    $domain = $connection['domain'] ?? '';
@endphp

<x-admin.modal-form
    :title="__('Create Company Mailbox')"
    :breadcrumbs="[
        ['label' => __('Company Email'), 'url' => $indexUrl],
        ['label' => __('Create')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.settings.company-email.store', $scopeQuery)">
        <div class="space-y-5">
            <p class="text-sm text-slate-500">
                {{ __('Provision a new mailbox on :domain through cPanel.', ['domain' => $domain ?: __('your domain')]) }}
            </p>

            <div>
                <label for="local_part" class="erp-label">{{ __('Mailbox name') }}</label>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <input
                        type="text"
                        name="local_part"
                        id="local_part"
                        value="{{ old('local_part') }}"
                        class="erp-input min-w-0 flex-1 rounded-r-none"
                        placeholder="sales"
                        required
                        autocomplete="off"
                    >
                    <span class="inline-flex items-center rounded-r-md border border-l-0 border-erp-border bg-erp-page px-3 text-sm text-slate-600">
                        {{ '@' . ($domain ?: 'domain.com') }}
                    </span>
                </div>
                @error('local_part')
                    <p class="mt-1 text-sm text-red-600" data-erp-field-error="local_part">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-slate-500">{{ __('Use letters, numbers, dots, dashes, or underscores.') }}</p>
            </div>

            <x-admin.password-input
                id="password"
                name="password"
                :label="__('Password')"
                required
            />

            <x-admin.password-input
                id="password_confirmation"
                name="password_confirmation"
                :label="__('Confirm password')"
                required
            />

            <div>
                <label for="quota_mb" class="erp-label">{{ __('Quota (MB)') }}</label>
                <input
                    type="number"
                    name="quota_mb"
                    id="quota_mb"
                    value="{{ old('quota_mb', $defaultQuotaMb) }}"
                    min="0"
                    max="10240"
                    class="erp-input mt-1 w-full"
                >
                @error('quota_mb')
                    <p class="mt-1 text-sm text-red-600" data-erp-field-error="quota_mb">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-slate-500">{{ __('Use 0 for unlimited quota, if supported by your hosting plan.') }}</p>
            </div>
        </div>

        <x-admin.form-actions>
            <x-primary-button>{{ __('Create mailbox') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
