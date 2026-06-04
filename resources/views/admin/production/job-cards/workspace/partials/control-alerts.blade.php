@if (! empty($alerts))
    <div class="job-360__alerts mb-4 space-y-2">
        @foreach ($alerts as $alert)
            <div @class([
                'rounded-lg border px-4 py-3 text-sm',
                'border-red-200 bg-red-50 text-red-900' => ($alert['type'] ?? '') === 'error',
                'border-amber-200 bg-amber-50 text-amber-900' => ($alert['type'] ?? '') === 'warning',
            ])>
                {{ $alert['message'] }}
            </div>
        @endforeach
    </div>
@endif
