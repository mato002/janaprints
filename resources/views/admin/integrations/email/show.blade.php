<x-admin-layout :title="__('Email Provider')" :breadcrumbs="[['label' => __('Integrations')], ['label' => __('Email Settings')], ['label' => $setting->provider->label()]]">
    <x-admin.page-header :title="$setting->provider->label()" :description="__('Outbound email configuration')">
        <x-slot name="actions">
            @can('update', $setting)
                <a href="{{ route('admin.integrations.email.edit', $setting) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 md:grid-cols-2">
        <x-admin.card>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd><x-admin.status-badge :variant="$setting->is_active ? 'success' : 'neutral'">{{ $setting->is_active ? __('Active') : __('Inactive') }}</x-admin.status-badge></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('From') }}</dt><dd>{{ $setting->from_name }} &lt;{{ $setting->from_email ?? '—' }}&gt;</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Reply-To') }}</dt><dd>{{ $setting->reply_to_email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Last tested') }}</dt><dd>{{ $setting->last_tested_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Last successful send') }}</dt><dd>{{ $setting->last_successful_send_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                @if ($setting->last_failure_at)
                    <div class="flex justify-between"><dt class="text-slate-500">{{ __('Last failure') }}</dt><dd class="text-red-600">{{ $setting->last_failure_message }}</dd></div>
                @endif
            </dl>
        </x-admin.card>

        @can('manage', $setting)
            <x-admin.card :title="__('Actions')">
                <div class="space-y-3">
                    <form method="POST" action="{{ route('admin.integrations.email.test-connection', $setting) }}">
                        @csrf
                        <button type="submit" class="erp-btn-secondary w-full">{{ __('Test connection') }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.integrations.email.send-test', $setting) }}" class="flex gap-2">
                        @csrf
                        <input type="email" name="recipient" class="erp-input flex-1" placeholder="{{ __('Test recipient email') }}" required>
                        <button type="submit" class="erp-btn-primary">{{ __('Send test') }}</button>
                    </form>
                    @if ($setting->is_active)
                        <form method="POST" action="{{ route('admin.integrations.email.deactivate', $setting) }}">
                            @csrf
                            <button type="submit" class="erp-btn-secondary w-full">{{ __('Deactivate') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.integrations.email.activate', $setting) }}">
                            @csrf
                            <button type="submit" class="erp-btn-primary w-full">{{ __('Activate provider') }}</button>
                        </form>
                    @endif
                </div>
            </x-admin.card>
        @endcan
    </div>
</x-admin-layout>
