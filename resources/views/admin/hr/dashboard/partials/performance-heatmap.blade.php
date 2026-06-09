@props(['heatmap'])

@if (! empty($heatmap))
    <x-admin.card class="mt-6" :title="__('Performance Heatmap')">
        <p class="mb-4 text-xs text-slate-500">{{ __('Department intensity from attendance and performance scores.') }}</p>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($heatmap as $cell)
                @php
                    $opacity = max(0.15, $cell['intensity'] / 100);
                @endphp
                <div class="rounded-lg border border-erp-border/70 p-4" style="background-color: rgba(79, 70, 229, {{ number_format($opacity, 2) }})">
                    <p class="font-medium text-erp-primary">{{ $cell['department'] }}</p>
                    <dl class="mt-2 grid grid-cols-3 gap-2 text-xs">
                        <div>
                            <dt class="text-slate-500">{{ __('Attendance') }}</dt>
                            <dd class="font-semibold">{{ $cell['attendance'] }}%</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Performance') }}</dt>
                            <dd class="font-semibold">{{ $cell['performance'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">{{ __('Headcount') }}</dt>
                            <dd class="font-semibold">{{ $cell['headcount'] }}</dd>
                        </div>
                    </dl>
                </div>
            @endforeach
        </div>
    </x-admin.card>
@endif
