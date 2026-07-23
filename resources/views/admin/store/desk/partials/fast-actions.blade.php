@php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
@endphp

@if (count($fastActions ?? []) > 0)
    <section class="store-desk-actions h-full rounded-xl border border-erp-border bg-white p-3 shadow-sm" aria-label="{{ __('Quick actions') }}">
        <h2 class="mb-2 text-sm font-semibold text-slate-900">{{ __('Quick actions') }}</h2>
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-3">
            @foreach ($fastActions as $action)
                <a
                    href="{{ ($action['modal'] ?? false) ? $action['url'] : WorkspaceEmbed::url($action['url']) }}"
                    @class([
                        'store-desk-action-tile flex flex-col items-center justify-center gap-1.5 rounded-lg border px-2 py-3 text-center transition',
                        'border-erp-accent/40 bg-erp-accent/5 text-erp-primary hover:bg-erp-accent/10' => $action['primary'] ?? false,
                        'border-slate-200 bg-white text-slate-700 hover:border-erp-accent/30 hover:bg-slate-50' => ! ($action['primary'] ?? false),
                    ])
                    @if ($action['modal'] ?? false)
                        data-erp-modal-open
                    @else
                        data-turbo-frame="{{ $frame }}"
                        data-turbo-action="advance"
                    @endif
                >
                    <span @class([
                        'flex h-9 w-9 items-center justify-center rounded-lg',
                        'bg-erp-accent text-white' => $action['primary'] ?? false,
                        'bg-slate-100 text-slate-700' => ! ($action['primary'] ?? false),
                    ])>
                        <x-admin.icon :name="$action['icon'] ?? 'cube'" class="h-4 w-4" />
                    </span>
                    <span class="text-[11px] font-semibold leading-tight">{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
