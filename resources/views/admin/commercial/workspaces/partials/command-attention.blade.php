@if (! empty($attention))
    <section class="mb-6" aria-label="{{ __('Quick attention center') }}">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Quick Attention Center') }}</h2>
        </div>
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @foreach ($attention as $section)
                <x-admin.card>
                    <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                        <h3 class="text-sm font-semibold text-erp-primary">{{ $section['title'] }}</h3>
                        @if (! empty($section['route']) && Route::has($section['route']))
                            <a href="{{ route($section['route']) }}" data-turbo-frame="erp-main" class="text-xs font-medium text-erp-accent hover:text-erp-accent-hover">
                                {{ __('View all') }}
                            </a>
                        @endif
                    </div>
                    <div class="overflow-x-auto">
                        <table class="erp-table w-full text-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Reference') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Updated') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($section['items'] as $item)
                                    <tr>
                                        <td>
                                            @if (! empty($item['url']))
                                                <a href="{{ $item['url'] }}" data-turbo-frame="erp-main" class="font-medium text-erp-accent hover:text-erp-accent-hover">{{ $item['reference'] }}</a>
                                            @else
                                                {{ $item['reference'] }}
                                            @endif
                                        </td>
                                        <td>{{ $item['customer'] }}</td>
                                        <td class="font-mono">{{ $item['amount'] }}</td>
                                        <td class="text-slate-500">{{ $item['date']?->format('d M Y') ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-slate-500">{{ __('No records.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>
            @endforeach
        </div>
    </section>
@endif
