<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ($tabData['kpis'] as $kpi)
        <x-admin.kpi-widget :label="$kpi['label']" :value="$kpi['value']" icon="chip" />
    @endforeach
</div>

<div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Health Factors') }}</h3>
        <ul class="space-y-2 text-sm">
            @foreach ($tabData['health_factors'] as $factor)
                <li class="flex justify-between">
                    <span>{{ $factor['label'] }}</span>
                    <span class="{{ $factor['impact'] < 0 ? 'text-red-600' : 'text-green-600' }}">{{ $factor['impact'] > 0 ? '+' : '' }}{{ $factor['impact'] }}</span>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
    @if ($tabData['replacement_candidate'] ?? null)
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-amber-700">{{ __('Replacement Candidate') }}</h3>
            <ul class="list-disc pl-5 text-sm">
                @foreach ($tabData['replacement_candidate']['reasons'] as $reason)
                    <li>{{ $reason }}</li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif
</div>
