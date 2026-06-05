<x-admin-layout :title="$provider->name">
    <x-admin.page-header :title="$provider->name" :description="$definition['description'] ?? ''" />

    <div class="grid gap-4 md:grid-cols-2">
        <x-admin.card>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $provider->status->label() }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Connected') }}</dt><dd>{{ $provider->connected_at?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Last sync') }}</dt><dd>{{ $provider->last_sync_at?->diffForHumans() ?? '—' }}</dd></div>
                @if ($provider->last_sync_error)
                    <div class="text-red-600 text-xs">{{ $provider->last_sync_error }}</div>
                @endif
            </dl>
        </x-admin.card>

        @can('manage', $provider)
            <x-admin.card :title="__('Connection')">
                @if ($provider->status->value !== 'connected')
                    <form method="POST" action="{{ route('admin.integrations.providers.connect', $provider) }}" class="space-y-3">
                        @csrf
                        <div><label class="erp-label">{{ __('Client ID') }}</label><input type="text" name="client_id" class="erp-input w-full" autocomplete="off"></div>
                        <div><label class="erp-label">{{ __('Client Secret') }}</label><input type="password" name="client_secret" class="erp-input w-full" autocomplete="off"></div>
                        <div><label class="erp-label">{{ __('API Key') }}</label><input type="password" name="api_key" class="erp-input w-full" autocomplete="off"></div>
                        <button type="submit" class="erp-btn-primary w-full">{{ __('Connect') }}</button>
                    </form>
                @else
                    <div class="space-y-2">
                        <p class="text-sm text-slate-600">{{ __('Credentials stored securely (encrypted).') }}</p>
                        <form method="POST" action="{{ route('admin.integrations.providers.health-check', $provider) }}">@csrf<button class="erp-btn-secondary w-full">{{ __('Health check') }}</button></form>
                        <form method="POST" action="{{ route('admin.integrations.providers.sync', $provider) }}">@csrf<button class="erp-btn-secondary w-full">{{ __('Sync now') }}</button></form>
                        <form method="POST" action="{{ route('admin.integrations.providers.disconnect', $provider) }}">@csrf<button class="erp-btn-secondary w-full text-red-700">{{ __('Disconnect') }}</button></form>
                    </div>
                @endif
            </x-admin.card>
        @endcan
    </div>

    @if ($provider->logs->isNotEmpty())
        <x-admin.card :title="__('Activity log')" class="mt-4">
            <ul class="space-y-2 text-sm">
                @foreach ($provider->logs as $log)
                    <li class="flex justify-between border-b border-erp-border pb-2">
                        <span>{{ $log->action }} — {{ $log->message }}</span>
                        <span class="text-slate-400">{{ $log->created_at?->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>
