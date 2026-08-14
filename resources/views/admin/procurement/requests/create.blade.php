<x-admin.modal-form
    :title="__('Create Purchase Request')"
    :breadcrumbs="[
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')],
        ['label' => __('Purchase Requests'), 'url' => route('admin.procurement.requests.index')],
        ['label' => __('Create')],
    ]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.procurement.requests.store')" class="space-y-6">
        @if (request('from') === 'production-floor')
            <input type="hidden" name="from" value="production-floor">
        @endif
        @include('admin.procurement.requests.partials.header-form')
        @include('admin.procurement.partials.line-items-form', ['items' => $items, 'mode' => 'request'])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save request') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
