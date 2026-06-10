<x-admin.card>
    <div class="py-8 text-center text-sm text-slate-500">
        <p class="font-medium text-slate-700">{{ $title ?? __('Coming soon') }}</p>
        <p class="mt-2">{{ $message ?? __('This section will populate as trusted cost data is connected. No artwork analysis or AI estimation runs in this phase.') }}</p>
    </div>
</x-admin.card>
