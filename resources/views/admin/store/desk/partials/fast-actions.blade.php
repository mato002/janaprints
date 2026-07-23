@if (count($fastActions ?? []) > 0)
    <div class="mb-4">
        <h2 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Quick actions') }}</h2>
        <div class="flex flex-wrap gap-2">
            @foreach ($fastActions as $action)
                <a
                    href="{{ $action['url'] }}"
                    @class([
                        'inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition',
                        'border-erp-accent bg-erp-accent text-white hover:bg-erp-accent/90' => $action['primary'] ?? false,
                        'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => ! ($action['primary'] ?? false),
                    ])
                    @if ($action['modal'] ?? false) data-erp-modal-open @else data-turbo-frame="erp-main" @endif
                >{{ $action['label'] }}</a>
            @endforeach
        </div>
    </div>
@endif
