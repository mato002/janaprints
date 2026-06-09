@props(['departments'])

<x-admin.card :title="__('Department Headcount')">
    @if (! empty($departments))
        <div class="space-y-2 text-sm">
            @foreach ($departments as $row)
                <div class="flex items-center justify-between rounded border border-erp-border/60 px-3 py-2">
                    <span>{{ $row[0] }}</span>
                    <span class="font-medium tabular-nums">{{ $row[2] }} / {{ $row[1] }}</span>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-slate-500">{{ __('No department headcount data.') }}</p>
    @endif
</x-admin.card>
