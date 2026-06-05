<x-admin-layout :title="__('API Key')">
    <x-admin.page-header :title="$apiKey->name" />
    @if ($generatedSecret)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm" x-data="{ copied: false }">
            <p class="font-medium text-amber-900">{{ __('Secret (shown once only)') }}</p>
            <div class="mt-2 flex items-center gap-2">
                <code class="flex-1 rounded bg-white px-2 py-1 font-mono text-xs">{{ $generatedSecret }}</code>
                <button type="button" class="erp-btn-secondary text-xs" @click="navigator.clipboard.writeText(@js($generatedSecret)); copied = true">{{ __('Copy') }}</button>
            </div>
            <p x-show="copied" class="mt-1 text-xs text-emerald-700">{{ __('Copied to clipboard.') }}</p>
        </div>
    @endif
    <x-admin.card>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Key') }}</dt><dd class="font-mono">{{ $apiKey->key }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Secret prefix') }}</dt><dd>{{ $apiKey->secret_prefix }}…</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Environment') }}</dt><dd>{{ $apiKey->environment->label() }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $apiKey->isRevoked() ? __('Revoked') : ($apiKey->is_active ? __('Active') : __('Disabled')) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Last used') }}</dt><dd>{{ $apiKey->last_used_at?->format('d M Y H:i') ?? '—' }}</dd></div>
        </dl>
    </x-admin.card>
</x-admin-layout>
