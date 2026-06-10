<dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
    <div><dt class="text-xs text-slate-500">{{ __('Forecast') }}</dt><dd class="font-medium">{{ number_format((float) ($forecast['forecast_value'] ?? 0), 2) }}{{ $suffix ?? '' }}</dd></div>
    <div><dt class="text-xs text-slate-500">{{ __('Lower bound') }}</dt><dd class="font-medium">{{ number_format((float) ($forecast['lower_bound'] ?? 0), 2) }}{{ $suffix ?? '' }}</dd></div>
    <div><dt class="text-xs text-slate-500">{{ __('Upper bound') }}</dt><dd class="font-medium">{{ number_format((float) ($forecast['upper_bound'] ?? 0), 2) }}{{ $suffix ?? '' }}</dd></div>
    <div><dt class="text-xs text-slate-500">{{ __('Confidence') }}</dt><dd class="font-medium">{{ ($forecast['confidence_score'] ?? null) !== null ? number_format((float) $forecast['confidence_score'], 1).'%' : '—' }}</dd></div>
</dl>
