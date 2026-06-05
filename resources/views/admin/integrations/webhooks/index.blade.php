<x-admin-layout :title="__('Webhooks')">
    <x-admin.page-header :title="__('Webhooks')" :description="__('Outbound event subscriptions and delivery monitoring.')">
        <x-slot name="actions">
            @can('create', App\Models\Integrations\IntegrationWebhook::class)
                <a href="{{ route('admin.integrations.webhooks.create') }}" class="erp-btn-primary">{{ __('Add webhook') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search webhooks...')" export-filename="webhooks">
        <x-slot name="head">
            <th>{{ __('Name') }}</th>
            <th>{{ __('Endpoint') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Last delivery') }}</th>
            <th>{{ __('Response') }}</th>
            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
        </x-slot>
        <x-slot name="body">
            @forelse ($webhooks as $webhook)
                <tr>
                    <td class="font-medium">{{ $webhook->name }}</td>
                    <td class="max-w-[10rem] truncate text-xs">{{ $webhook->endpoint_url }}</td>
                    <td><x-admin.status-badge :variant="$webhook->status->value === 'active' ? 'success' : 'neutral'">{{ $webhook->status->label() }}</x-admin.status-badge></td>
                    <td>{{ $webhook->last_delivery_at?->diffForHumans() ?? '—' }}</td>
                    <td>{{ $webhook->last_response_code ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.integrations.webhooks.show', $webhook)">{{ __('View') }}</x-admin.table-row-action>
                            @can('manage', $webhook)
                                <x-admin.table-row-action :action="route('admin.integrations.webhooks.test', $webhook)" method="POST">{{ __('Test') }}</x-admin.table-row-action>
                                @if ($webhook->status->value === 'active')
                                    <x-admin.table-row-action :action="route('admin.integrations.webhooks.disable', $webhook)" method="POST">{{ __('Disable') }}</x-admin.table-row-action>
                                @else
                                    <x-admin.table-row-action :action="route('admin.integrations.webhooks.enable', $webhook)" method="POST">{{ __('Enable') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No webhooks configured.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $webhooks->links() }}</div>
</x-admin-layout>
