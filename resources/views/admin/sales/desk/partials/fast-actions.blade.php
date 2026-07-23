@php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
@endphp

@if (count($fastActions ?? []) > 0)
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($fastActions as $action)
            <a
                href="{{ ($action['modal'] ?? false) ? $action['url'] : WorkspaceEmbed::url($action['url']) }}"
                @class([
                    'inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition',
                    'border-erp-accent bg-erp-accent text-white hover:bg-erp-accent/90' => ($action['key'] ?? '') === 'new_quote',
                    'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => ($action['key'] ?? '') !== 'new_quote',
                ])
                @if ($action['modal'] ?? false)
                    data-erp-modal-open
                @else
                    data-turbo-frame="{{ $frame }}"
                    data-turbo-action="advance"
                @endif
            >{{ $action['label'] }}</a>
        @endforeach
    </div>
@endif
