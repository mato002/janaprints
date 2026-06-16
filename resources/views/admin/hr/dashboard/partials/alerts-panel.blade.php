@props(['alerts'])

@php
    use App\Support\Navigation\WorkspaceEmbed;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<x-admin.card>
    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-erp-primary">{{ __('HR Alerts') }}</h2>
    @if (! empty($alerts))
        <ul class="space-y-2">
            @foreach ($alerts as $alert)
                @php
                    $priorityClass = match ($alert['priority']) {
                        'high' => 'border-red-200 bg-red-50 text-red-800',
                        'medium' => 'border-amber-200 bg-amber-50 text-amber-900',
                        default => 'border-slate-200 bg-slate-50 text-slate-700',
                    };
                @endphp
                <li class="rounded-md border px-3 py-2 text-xs {{ $priorityClass }}">
                    @if ($alert['url'] ?? null)
                        <a href="{{ \App\Support\Navigation\WorkspaceEmbed::url($alert['url']) }}" class="font-medium hover:underline" data-turbo-frame="{{ $turboFrame }}">{{ $alert['message'] }}</a>
                    @else
                        <span>{{ $alert['message'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-xs text-slate-500">{{ __('No active HR alerts.') }}</p>
    @endif
</x-admin.card>
