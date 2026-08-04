<x-admin.modal-form
    :title="__('Edit print specification')"
    :breadcrumbs="[
        ['label' => __('Customers'), 'url' => route('admin.crm.customers.index')],
        ['label' => $customer->company_name, 'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications'])],
        ['label' => $specification->name],
    ]"
    maxWidth="3xl"
>
    <x-admin.artwork-preview-lightbox>
        <x-admin.form-shell :action="route('admin.crm.customers.print-specifications.update', [$customer, $specification])" method="PUT">
            @if (request('from') === 'sales-desk')
                <input type="hidden" name="from" value="sales-desk">
            @endif
            @include('admin.crm.customers.print-specifications.partials.form', [
                'customer' => $customer,
                'specification' => $specification,
                'serialProfile' => $serialProfile,
                'serialSummary' => $serialSummary,
                'liveReferenceWarnings' => $liveReferenceWarnings ?? [],
                'hasOperationalUsage' => $hasOperationalUsage ?? false,
                'statuses' => $statuses,
                'billingTypes' => $billingTypes,
                'fulfilmentMethods' => $fulfilmentMethods,
                'artworkTypes' => $artworkTypes,
                'showArtworkUpload' => false,
            ])
            <x-admin.form-modal-actions class="erp-form-modal__actions--sticky">
                <x-primary-button class="min-h-[2.75rem]">{{ __('Save changes') }}</x-primary-button>
            </x-admin.form-modal-actions>
        </x-admin.form-shell>

        <section class="mt-6 rounded-lg border border-erp-border p-4">
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Upload new artwork version') }}</h3>
            <form method="POST" action="{{ route('admin.crm.customers.print-specifications.artworks.store', [$customer, $specification]) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                @if (request('from') === 'sales-desk')
                    <input type="hidden" name="from" value="sales-desk">
                @endif
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="erp-label">{{ __('File') }}</label>
                        <input type="file" name="file" class="erp-input w-full min-h-[2.75rem]" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Type') }}</label>
                        <x-admin.lookup-select
                            name="artwork_type"
                            :options="$artworkTypes"
                            :value="app(\App\Support\Crm\CustomerArtworkTypeCatalog::class)->defaultCode()"
                            create-route="admin.crm.artwork-types.quick-create"
                            refresh-route="admin.lookups.artwork_types"
                            permission="crm.customers.edit"
                            :modal-title="__('Create artwork type')"
                            select-class="erp-input w-full"
                            :empty-option="false"
                        />
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label">{{ __('Change notes') }}</label>
                        <input name="change_notes" class="erp-input w-full" maxlength="2000">
                    </div>
                </div>
                <button type="submit" class="erp-btn-secondary min-h-[2.75rem] w-full sm:w-auto">{{ __('Upload version') }}</button>
                <p class="text-xs text-slate-500">{{ __('Versions are never overwritten.') }}</p>
            </form>
        </section>
    </x-admin.artwork-preview-lightbox>
</x-admin.modal-form>
