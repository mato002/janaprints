<x-admin-layout :title="__('Webhook')">
    <x-admin.page-header :title="$webhook->name">
        <x-slot name="actions">
            @can('update', $webhook)<a href="{{ route('admin.integrations.webhooks.edit', $webhook) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>@endcan
            @can('manage', $webhook)
                <form method="POST" action="{{ route('admin.integrations.webhooks.test', $webhook) }}" class="inline">@csrf<button class="erp-btn-primary">{{ __('Test') }}</button></form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <section class="mb-4 grid grid-cols-3 gap-3">
        <x-admin.stat-card :label="__('Success rate')" :value="$stats['success_rate'].'%'" />
        <x-admin.stat-card :label="__('Failures (last 100)')" :value="(string) $stats['failed']" />
        <x-admin.stat-card :label="__('Deliveries (last 100)')" :value="(string) $stats['total']" />
    </section>

    <x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
        <x-slot name="head">
            <th>{{ __('Event') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Attempts') }}</th>
            <th>{{ __('Delivered') }}</th>
            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
        </x-slot>
        <x-slot name="body">
            @forelse ($deliveries as $delivery)
                <tr>
                    <td>{{ $delivery->event_type }}</td>
                    <td>{{ ucfirst($delivery->status) }}</td>
                    <td>{{ $delivery->response_code ?? '—' }}</td>
                    <td>{{ $delivery->attempt_count }}</td>
                    <td>{{ $delivery->delivered_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        @can('manage', $webhook)
                            @if ($delivery->status === 'failed')
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :action="route('admin.integrations.webhooks.retry', [$webhook, $delivery])" method="POST">{{ __('Retry') }}</x-admin.table-row-action>
                                </x-admin.table-row-actions>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No deliveries yet.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
