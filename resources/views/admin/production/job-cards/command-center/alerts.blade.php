<x-admin.card>
    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production Alerts') }}</h2>
    <div class="space-y-4">
        @foreach (['overdue', 'awaiting_qc', 'awaiting_artwork', 'dispatch_due_today'] as $key)
            @php $section = $alerts[$key] ?? []; @endphp
            <div>
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ $section['title'] ?? '' }}</h3>
                    @if (! empty($section['view_all_url']))
                        <a href="{{ $section['view_all_url'] }}" class="text-[10px] text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('View All') }}</a>
                    @endif
                </div>
                @if (! empty($section['empty']))
                    <p class="text-xs text-slate-500">{{ __('All clear') }}</p>
                @else
                    <ul class="divide-y divide-erp-border text-xs">
                        @foreach ($section['records'] ?? [] as $record)
                            <li class="py-2">
                                @if ($record['url'] ?? null)
                                    <a href="{{ $record['url'] }}" class="font-mono font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $record['job_number'] }}</a>
                                @else
                                    <span class="font-mono font-medium">{{ $record['job_number'] }}</span>
                                @endif
                                <p class="truncate text-slate-600">{{ $record['customer'] }}</p>
                                <p class="mt-0.5 flex justify-between text-slate-500">
                                    <span>{{ $record['due_date'] }}</span>
                                    <span>{{ $record['status'] }}</span>
                                </p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
</x-admin.card>
