@php
    $specData = $specification ?? ['has_specification' => false];
    $sections = $specData['sections'] ?? [];
@endphp

@if (! ($specData['has_specification'] ?? false))
    <x-admin.card>
        <p class="text-sm text-slate-600">{{ $specData['message'] ?? __('No structured production specification yet.') }}</p>
    </x-admin.card>
@else
    <div class="mb-4 flex flex-wrap items-center gap-2">
        @if (! empty($specData['approval_status_label']))
            <x-admin.status-badge :variant="$specData['approval_status_variant'] ?? 'neutral'">
                {{ $specData['approval_status_label'] }}
            </x-admin.status-badge>
        @endif
        @if (! empty($specData['updated_at']))
            <span class="text-xs text-slate-500">{{ __('Updated') }}: {{ $specData['updated_at'] }}</span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($sections as $sectionKey => $fields)
            @if (! empty($fields))
                <x-admin.card>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">
                        {{ match ($sectionKey) {
                            'product' => __('Product'),
                            'dimensions' => __('Dimensions'),
                            'materials' => __('Materials'),
                            'print' => __('Print'),
                            'finishing' => __('Finishing'),
                            'imposition' => __('Imposition'),
                            'artwork' => __('Artwork'),
                            'notes' => __('Notes'),
                            default => ucfirst(str_replace('_', ' ', $sectionKey)),
                        } }}
                    </h3>
                    <dl class="space-y-2 text-sm">
                        @foreach ($fields as $field)
                            <div class="flex justify-between gap-3">
                                <dt class="text-slate-500 shrink-0">{{ $field['label'] }}</dt>
                                <dd class="text-right font-medium">{{ $field['value'] ?? '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-admin.card>
            @endif
        @endforeach
    </div>
@endif
