<x-admin-layout :title="__('Third Party Integrations')">
    <x-admin.page-header :title="__('Third Party Integrations')" :description="__('Connect external business systems, payments, and messaging platforms.')" />

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($providers as $provider)
            @php
                $statusVariant = match ($provider->status->value) {
                    'connected' => 'success',
                    'error' => 'danger',
                    default => 'neutral',
                };
            @endphp
            <x-admin.card class="flex flex-col">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-semibold text-erp-primary">{{ $provider->name }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ ucfirst($provider->category) }}</p>
                    </div>
                    <x-admin.status-badge :variant="$statusVariant">{{ $provider->status->label() }}</x-admin.status-badge>
                </div>
                <dl class="mt-3 space-y-1 text-xs text-slate-600">
                    <div class="flex justify-between"><dt>{{ __('Last sync') }}</dt><dd>{{ $provider->last_sync_at?->diffForHumans() ?? '—' }}</dd></div>
                    @if ($provider->last_sync_error)
                        <div class="text-red-600">{{ Str::limit($provider->last_sync_error, 60) }}</div>
                    @endif
                </dl>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.integrations.providers.show', $provider) }}" class="erp-btn-secondary flex-1 text-center text-xs">{{ __('Manage') }}</a>
                </div>
            </x-admin.card>
        @endforeach
    </div>
    <div class="mt-4">{{ $providers->links() }}</div>
</x-admin-layout>
