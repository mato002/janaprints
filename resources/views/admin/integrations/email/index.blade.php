<x-admin-layout :title="__('Email Settings')" :breadcrumbs="[['label' => __('Administration')], ['label' => __('Integrations')], ['label' => __('Email Settings')]]">
    <x-admin.page-header :title="__('Email Settings')" :description="__('Manage outbound email providers and delivery configuration.')">
        <x-slot name="actions">
            @can('create', App\Models\Integrations\IntegrationEmailSetting::class)
                <a href="{{ route('admin.integrations.email.create') }}" class="erp-btn-primary">{{ __('Add provider') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if ($activeProvider)
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ __('Active provider:') }} <strong>{{ $activeProvider->provider->label() }}</strong>
            @if ($activeProvider->last_tested_at)
                · {{ __('Last tested') }} {{ $activeProvider->last_tested_at->diffForHumans() }}
            @endif
        </div>
    @endif

    <x-admin.data-table
        :search-placeholder="__('Search email settings...')"
        export-filename="email-settings"
        export-route="admin.administration.exports"
        :export-route-params="['listing' => 'email-providers']"
        :export-query="request()->query()"
        :format-in-path="true"
        :chips="[['id' => 'all', 'label' => __('All')], ['id' => 'active', 'label' => __('Active')]]"
    >
        <x-slot name="filters">
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="provider" class="erp-select text-sm" onchange="this.form.submit()">
                    <option value="">{{ __('All providers') }}</option>
                    @foreach ($providers as $provider)
                        <option value="{{ $provider->value }}" @selected(($filters['provider'] ?? '') === $provider->value)>{{ $provider->label() }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot>
        <x-slot name="head">
            <th>{{ __('Provider') }}</th>
            <th>{{ __('From') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Last tested') }}</th>
            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
        </x-slot>
        <x-slot name="body">
            @forelse ($settings as $setting)
                <tr data-search="{{ strtolower($setting->provider->label().' '.$setting->from_email) }}" data-chip="{{ $setting->is_active ? 'active' : 'all' }}">
                    <td class="font-medium">{{ $setting->provider->label() }}</td>
                    <td>{{ $setting->from_email ?? '—' }}</td>
                    <td><x-admin.status-badge :variant="$setting->is_active ? 'success' : 'neutral'">{{ $setting->is_active ? __('Active') : __('Inactive') }}</x-admin.status-badge></td>
                    <td>{{ $setting->last_tested_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.integrations.email.show', $setting)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $setting)
                                <x-admin.table-row-action :href="route('admin.integrations.email.edit', $setting)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('manage', $setting)
                                <x-admin.table-row-action :action="route('admin.integrations.email.test-connection', $setting)" method="POST">{{ __('Test') }}</x-admin.table-row-action>
                                @if (! $setting->is_active)
                                    <x-admin.table-row-action :action="route('admin.integrations.email.activate', $setting)" method="POST">{{ __('Activate') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No email providers configured.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $settings->links() }}</div>
</x-admin-layout>
