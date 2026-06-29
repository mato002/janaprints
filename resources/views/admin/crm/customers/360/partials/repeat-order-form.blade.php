@props(['customer', 'orderId', 'orderNumber'])

@can('create', App\Models\Sales\SalesOrder::class)
    <form
        method="POST"
        action="{{ route('admin.crm.customers.repeat-order', [$customer, $orderId]) }}"
        class="inline"
        onsubmit="return confirm(@js(__('Create a repeat order from :number?', ['number' => $orderNumber])))"
    >
        @csrf
        <x-admin.crm-btn variant="ghost" size="sm" type="submit" class="min-h-[2.25rem]">
            {{ __('Repeat Order') }}
        </x-admin.crm-btn>
    </form>
@endcan
