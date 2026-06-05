<x-admin-layout :title="__('SMS Provider')">
    <x-admin.page-header :title="$setting->provider->label()">
        <x-slot name="actions">
            @can('update', $setting)<a href="{{ route('admin.integrations.sms.edit', $setting) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>@endcan
        </x-slot>
    </x-admin.page-header>
    <div class="grid gap-4 md:grid-cols-2">
        <x-admin.card>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $setting->is_active ? __('Active') : __('Inactive') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Sender ID') }}</dt><dd>{{ $setting->sender_id ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('API URL') }}</dt><dd class="truncate max-w-[12rem]">{{ $setting->api_url ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('API Key') }}</dt><dd>{{ $setting->api_key ? '••••••••' : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">{{ __('Health') }}</dt><dd>{{ ucfirst($setting->health_status) }}</dd></div>
            </dl>
        </x-admin.card>
        @can('manage', $setting)
            <x-admin.card :title="__('Actions')">
                <form method="POST" action="{{ route('admin.integrations.sms.verify', $setting) }}" class="mb-3">@csrf<button class="erp-btn-secondary w-full">{{ __('Verify credentials') }}</button></form>
                <form method="POST" action="{{ route('admin.integrations.sms.send-test', $setting) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="phone" class="erp-input flex-1" placeholder="+254..." required>
                    <button class="erp-btn-primary">{{ __('Send test SMS') }}</button>
                </form>
            </x-admin.card>
        @endcan
    </div>
</x-admin-layout>
