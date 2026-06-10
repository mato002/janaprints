<x-admin-layout :title="__('SMS Settings')" :breadcrumbs="[['label' => __('Administration')], ['label' => __('Integrations')], ['label' => __('SMS Settings')]]">
    <x-admin.page-header :title="__('SMS Settings')" :description="__('SMS provider credentials, routing, and health monitoring.')">
        <x-slot name="actions">
            @can('create', App\Models\Integrations\IntegrationSmsSetting::class)
                <a href="{{ route('admin.integrations.sms.create') }}" class="erp-btn-primary">{{ __('Add provider') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <section class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        @php $stats = $settings->firstWhere('is_active', true); @endphp
        <x-admin.stat-card :label="__('SMS today')" :value="(string) ($stats?->sms_sent_today ?? 0)" />
        <x-admin.stat-card :label="__('SMS this month')" :value="(string) ($stats?->sms_sent_month ?? 0)" />
        <x-admin.stat-card :label="__('Failed messages')" :value="(string) ($stats?->failed_count ?? 0)" />
        <x-admin.stat-card :label="__('Provider health')" :value="$stats?->health_status ?? __('Unknown')" />
    </section>

    <x-admin.data-table
        :search-placeholder="__('Search SMS settings...')"
        export-filename="sms-settings"
        export-route="admin.administration.exports"
        :export-route-params="['listing' => 'sms-providers']"
        :export-query="request()->query()"
        :format-in-path="true"
    >
        <x-slot name="head">
            <th>{{ __('Provider') }}</th>
            <th>{{ __('Sender ID') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Health') }}</th>
            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
        </x-slot>
        <x-slot name="body">
            @forelse ($settings as $setting)
                <tr>
                    <td class="font-medium">{{ $setting->provider->label() }}</td>
                    <td>{{ $setting->sender_id ?? '—' }}</td>
                    <td><x-admin.status-badge :variant="$setting->is_active ? 'success' : 'neutral'">{{ $setting->is_active ? __('Active') : __('Inactive') }}</x-admin.status-badge></td>
                    <td>{{ ucfirst($setting->health_status) }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.integrations.sms.show', $setting)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $setting)
                                <x-admin.table-row-action :href="route('admin.integrations.sms.edit', $setting)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('manage', $setting)
                                <x-admin.table-row-action :action="route('admin.integrations.sms.verify', $setting)" method="POST">{{ __('Test') }}</x-admin.table-row-action>
                                @if (! $setting->is_active)
                                    <x-admin.table-row-action :action="route('admin.integrations.sms.activate', $setting)" method="POST">{{ __('Activate') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No SMS providers configured.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $settings->links() }}</div>
</x-admin-layout>
