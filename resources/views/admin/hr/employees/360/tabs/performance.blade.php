<x-admin.card class="mb-4">
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('KPI Snapshot') }}</h3>
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        @foreach ($performance['kpis'] as $key => $value)
            @if (is_numeric($value))
                <div><dt class="text-xs text-slate-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt><dd class="font-medium">{{ is_float($value) ? number_format($value, 1) : $value }}</dd></div>
            @endif
        @endforeach
    </dl>
</x-admin.card>

<x-admin.card class="mb-4">
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Reviews') }}</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Reference') }}</th><th>{{ __('Period') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Score') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($performance['reviews'] as $review)
                <tr>
                    <td><a href="{{ route('admin.hr.performance.show', $review) }}" class="text-erp-primary hover:underline">{{ $review->reference }}</a></td>
                    <td>{{ $review->period_start?->format('M j') }} – {{ $review->period_end?->format('M j, Y') }}</td>
                    <td>{{ $review->rating?->value ?? '—' }}</td>
                    <td>{{ $review->composite_score ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No reviews')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$performance['reviews']" /></x-slot>
    </x-admin.data-table>
</x-admin.card>

@if ($performance['targets']->isNotEmpty())
    <x-admin.card>
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Sales Targets') }}</h3>
        <x-admin.data-table>
            <x-slot name="head"><tr><th>{{ __('Period') }}</th><th>{{ __('Target') }}</th></tr></x-slot>
            <x-slot name="body">
                @foreach ($performance['targets'] as $target)
                    <tr>
                        <td>{{ $target->period_start?->format('M j') }} – {{ $target->period_end?->format('M j, Y') }}</td>
                        <td>{{ number_format((float) $target->target_amount, 2) }}</td>
                    </tr>
                @endforeach
            </x-slot>
        </x-admin.data-table>
    </x-admin.card>
@endif
