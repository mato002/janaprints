@if (! empty($environment['warnings']))
    <x-admin.alert variant="warning" class="mb-4">
        <ul class="list-disc space-y-1 pl-4 text-sm">
            @foreach ($environment['warnings'] as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </x-admin.alert>
@endif
