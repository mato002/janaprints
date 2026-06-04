<x-admin.card class="mt-4">
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Quick Action Center') }}</h2>
    <div class="flex flex-wrap gap-2">
        @forelse ($quickActions as $action)
            <a
                href="{{ route($action['route']) }}"
                class="{{ ! empty($action['primary']) ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-sm"
                data-turbo-frame="erp-main"
            >{{ $action['label'] }}</a>
        @empty
            <p class="text-sm text-slate-500">{{ __('No quick actions available for your role.') }}</p>
        @endforelse
    </div>
</x-admin.card>
