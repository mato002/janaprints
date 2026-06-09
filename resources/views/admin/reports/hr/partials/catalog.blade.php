@props(['catalog'])

<x-admin.card class="mb-6">
    <h2 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Reporting Catalog') }}</h2>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($catalog as $group)
            <div class="rounded-lg border border-erp-border/70 p-4">
                <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __($group['group']) }}</h3>
                <ul class="space-y-1 text-sm text-slate-700">
                    @foreach ($group['reports'] as $report)
                        <li>{{ __($report['label']) }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</x-admin.card>
