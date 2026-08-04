@php
    use App\Support\Crm\CustomerArtworkTypeCatalog;
    use App\Enums\CustomerPrintSpecificationStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $artworkTypeCatalog = app(CustomerArtworkTypeCatalog::class);
    $storeUrl = route('admin.crm.print-specifications.quick-store');
    $continueUrl = WorkspaceEmbed::url(route('admin.sales.desk', [
        'customer' => $customer->getRouteKey(),
        'step' => 3,
        'specification' => '__SPEC__',
    ]));
@endphp

<div
    class="mt-4 space-y-4 rounded-lg border border-erp-border bg-slate-50/80 p-4"
    x-data="{
        storeUrl: @js($storeUrl),
        continueUrl: @js($continueUrl),
        customerId: @js($customer->id),
        csrf: @js(csrf_token()),
        saving: false,
        error: '',
        async submit(form) {
            if (! form || this.saving) return;
            this.saving = true;
            this.error = '';
            try {
                const body = new FormData(form);
                if (! body.get('customer_id') && this.customerId) {
                    body.set('customer_id', String(this.customerId));
                }
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Erp-Lookup-Create': '1',
                    },
                    body,
                });
                const payload = await response.json().catch(() => ({}));
                if (! response.ok) {
                    const messages = payload.errors
                        ? Object.values(payload.errors).flat()
                        : [payload.message || 'Unable to save specification.'];
                    this.error = messages.join(' ');
                    return;
                }
                const specId = payload.value ?? payload.id ?? null;
                if (! specId) {
                    this.error = 'Specification saved but no id was returned.';
                    return;
                }
                window.location.href = this.continueUrl.replace('__SPEC__', encodeURIComponent(String(specId)));
            } catch (e) {
                this.error = 'Unable to save specification.';
            } finally {
                this.saving = false;
            }
        },
    }"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('Create new specification') }}</h3>
        <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800" x-on:click="$dispatch('desk-spec-mode', { mode: 'existing' })">{{ __('Cancel') }}</button>
    </div>

    <p class="text-xs text-slate-600">
        <span class="font-medium text-slate-800">{{ __('Customer') }}:</span>
        {{ $customer->company_name ?? $customer->name }}
    </p>
    <p class="text-xs text-slate-500">{{ __('Quantity and price are set on the next step for this order. Spec defaults can still be edited later in Customer 360.') }}</p>

    <form class="space-y-3" x-on:submit.prevent="submit($el)" enctype="multipart/form-data">
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">

        <div>
            <label class="erp-label" for="desk-spec-name">{{ __('Specification name') }}</label>
            <input id="desk-spec-name" type="text" name="name" class="erp-input w-full" maxlength="255" placeholder="{{ __('e.g. Fortress Receipt Book') }}" required>
        </div>

        <x-admin.lookup-select
            name="inventory_item_id"
            :label="__('Product / inventory item')"
            :options="$inventoryItemOptions ?? []"
            :value="null"
            :required="true"
            create-route="admin.inventory.items.quick-create"
            refresh-route="admin.lookups.items"
            permission="catalogue.create"
            :modal-title="__('Create product')"
            select-class="erp-input w-full"
            :empty-option="true"
            :placeholder="__('Select product')"
        />

        <input type="hidden" name="default_quantity" value="1">

        <div>
            <label class="erp-label" for="desk-spec-status">{{ __('Status') }}</label>
            <select id="desk-spec-status" name="status" class="erp-input w-full" required>
                @foreach (CustomerPrintSpecificationStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($status === CustomerPrintSpecificationStatus::Active)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">{{ __('Use Active so the specification is available for orders immediately.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <x-admin.lookup-select
                name="artwork_type"
                :label="__('Artwork type')"
                :options="$artworkTypeCatalog->optionsForCompany((int) $customer->company_id)"
                :value="$artworkTypeCatalog->defaultCode()"
                create-route="admin.crm.artwork-types.quick-create"
                refresh-route="admin.lookups.artwork_types"
                permission="crm.customers.edit"
                :modal-title="__('Create artwork type')"
                select-class="erp-input w-full"
                :empty-option="false"
            />
            <div>
                <label class="erp-label" for="desk-spec-artwork-file">{{ __('Initial artwork file') }}</label>
                <input id="desk-spec-artwork-file" type="file" name="artwork_file" class="erp-input w-full" accept=".jpg,.jpeg,.png,.webp,.pdf">
            </div>
        </div>

        <p class="text-xs text-rose-600" x-show="error" x-text="error" style="display: none"></p>

        <div class="flex flex-wrap justify-end gap-2">
            <button type="submit" class="erp-btn-primary text-sm" :disabled="saving">
                <span x-show="!saving">{{ __('Save specification') }}</span>
                <span x-show="saving" style="display: none">{{ __('Saving…') }}</span>
            </button>
        </div>
    </form>
</div>
