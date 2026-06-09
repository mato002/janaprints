@if (! empty($quickActions))
    <x-admin.card class="mb-6">
        <div class="flex flex-wrap items-center gap-2 p-4">
            @foreach ($quickActions as $action)
                @if (! empty($action['modal']))
                    <x-admin.form-modal-link
                        :href="$action['href']"
                        :variant="($action['variant'] ?? 'secondary') === 'primary' ? 'primary' : 'secondary'"
                    >{{ $action['label'] }}</x-admin.form-modal-link>
                @else
                    <a
                        href="{{ $action['href'] }}"
                        data-turbo-frame="erp-main"
                        @class([
                            'erp-btn-primary' => ($action['variant'] ?? 'secondary') === 'primary',
                            'erp-btn-secondary' => ($action['variant'] ?? 'secondary') !== 'primary',
                        ])
                    >
                        {{ $action['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </x-admin.card>
@endif
