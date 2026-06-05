<x-admin-layout :title="__('API Keys')">
    <x-admin.page-header :title="__('API Keys')" :description="__('Developer access keys for programmatic ERP integration.')">
        <x-slot name="actions">
            @can('create', App\Models\Integrations\IntegrationApiKey::class)
                <a href="{{ route('admin.integrations.api-keys.create') }}" class="erp-btn-primary">{{ __('Generate key') }}</a>
            @endcan
            <a href="{{ route('admin.integrations.api-keys.export') }}" class="erp-btn-secondary">{{ __('Export CSV') }}</a>
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search API keys...')" export-filename="api-keys" :chips="[['id' => 'all', 'label' => __('All')], ['id' => 'active', 'label' => __('Active')], ['id' => 'disabled', 'label' => __('Disabled')]]">
        <x-slot name="filters">
            <form method="GET" class="flex gap-2">
                <select name="environment" class="erp-select text-sm" onchange="this.form.submit()">
                    <option value="">{{ __('All environments') }}</option>
                    @foreach ($environments as $env)
                        <option value="{{ $env->value }}" @selected(($filters['environment'] ?? '') === $env->value)>{{ $env->label() }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot>
        <x-slot name="head">
            <th>{{ __('Name') }}</th>
            <th>{{ __('Key') }}</th>
            <th>{{ __('Environment') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Last used') }}</th>
            <th class="erp-table-actions-col">{{ __('Actions') }}</th>
        </x-slot>
        <x-slot name="body">
            @forelse ($apiKeys as $apiKey)
                <tr data-chip="{{ $apiKey->isRevoked() ? 'disabled' : ($apiKey->is_active ? 'active' : 'disabled') }}">
                    <td class="font-medium">{{ $apiKey->name }}</td>
                    <td class="font-mono text-xs">{{ $apiKey->key }}</td>
                    <td>{{ $apiKey->environment->label() }}</td>
                    <td>
                        @if ($apiKey->isRevoked())
                            <x-admin.status-badge variant="danger">{{ __('Revoked') }}</x-admin.status-badge>
                        @elseif ($apiKey->is_active)
                            <x-admin.status-badge variant="success">{{ __('Active') }}</x-admin.status-badge>
                        @else
                            <x-admin.status-badge variant="neutral">{{ __('Disabled') }}</x-admin.status-badge>
                        @endif
                    </td>
                    <td>{{ $apiKey->last_used_at?->diffForHumans() ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.integrations.api-keys.show', $apiKey)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $apiKey)
                                @if (! $apiKey->isRevoked())
                                    <x-admin.table-row-action :action="route('admin.integrations.api-keys.regenerate', $apiKey)" method="POST" :confirm="__('Regenerate secret? The old secret will stop working.')">{{ __('Regenerate') }}</x-admin.table-row-action>
                                    @if ($apiKey->is_active)
                                        <x-admin.table-row-action :action="route('admin.integrations.api-keys.disable', $apiKey)" method="POST">{{ __('Disable') }}</x-admin.table-row-action>
                                    @else
                                        <x-admin.table-row-action :action="route('admin.integrations.api-keys.enable', $apiKey)" method="POST">{{ __('Enable') }}</x-admin.table-row-action>
                                    @endif
                                    <x-admin.table-row-action :action="route('admin.integrations.api-keys.revoke', $apiKey)" method="DELETE" variant="danger" :confirm="__('Revoke this API key permanently?')">{{ __('Delete') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No API keys yet.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $apiKeys->links() }}</div>
</x-admin-layout>
