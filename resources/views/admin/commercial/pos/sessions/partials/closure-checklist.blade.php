@props(['governance', 'showCashCountHint' => false])

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold text-erp-primary">{{ __('Session Closure Checklist') }}</h3>
    <ul class="space-y-2 text-sm">
        @foreach ($governance['checklist'] as $item)
            <li class="flex items-start gap-2">
                <span class="mt-0.5 shrink-0 {{ $item['passed'] ? 'text-emerald-600' : 'text-rose-600' }}" aria-hidden="true">
                    {{ $item['passed'] ? '✓' : '✗' }}
                </span>
                <span>
                    <span class="font-medium {{ $item['passed'] ? 'text-slate-800' : 'text-rose-800' }}">{{ $item['label'] }}</span>
                    @if (! $item['passed'] && ! empty($item['detail']))
                        <span class="block text-xs text-slate-500">{{ $item['detail'] }}</span>
                    @elseif ($showCashCountHint && $item['key'] === 'cash_count_completed' && ! empty($item['detail']))
                        <span class="block text-xs text-slate-500">{{ $item['detail'] }}</span>
                    @endif
                </span>
            </li>
        @endforeach
    </ul>
    @unless ($governance['can_close'])
        <p class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800">
            {{ __('Resolve all checklist items above before closing this session.') }}
        </p>
    @endunless
</x-admin.card>
