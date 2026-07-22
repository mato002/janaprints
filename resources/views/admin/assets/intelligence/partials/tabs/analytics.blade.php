<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('By Category') }}</h3>
        <ul class="space-y-2 text-sm">
            @foreach ($stats['by_category'] as $row)
                <li class="flex justify-between"><span>{{ $row['label'] }}</span><span>{{ $row['count'] }} / {{ number_format($row['value'], 2) }}</span></li>
            @endforeach
        </ul>
    </x-admin.card>
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Age Distribution') }}</h3>
        <ul class="space-y-2 text-sm">
            @foreach ($stats['age_distribution'] as $row)
                <li class="flex justify-between"><span>{{ $row['label'] }}</span><span>{{ $row['count'] }}</span></li>
            @endforeach
        </ul>
    </x-admin.card>
</div>
