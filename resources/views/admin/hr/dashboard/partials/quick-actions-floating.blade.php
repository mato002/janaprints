@props(['actions'])

@if (! empty($actions))
    <div
        class="pointer-events-none fixed bottom-4 right-4 z-40"
        x-data="{ open: false }"
    >
        <div
            class="pointer-events-auto mb-2 flex flex-col items-end gap-2"
            x-show="open"
            x-cloak
            x-transition
        >
            @foreach ($actions as $action)
                <a
                    href="{{ \App\Support\Navigation\WorkspaceEmbed::url($action['url']) }}"
                    @if ($action['modal'] ?? false) data-erp-modal-open @endif
                    class="rounded-full border border-erp-border bg-white px-4 py-2 text-xs font-medium text-erp-primary shadow-md transition hover:border-erp-accent hover:text-erp-accent"
                >
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
        <button
            type="button"
            class="pointer-events-auto flex h-12 w-12 items-center justify-center rounded-full bg-erp-accent text-white shadow-lg transition hover:bg-erp-accent-hover"
            @click="open = !open"
            :aria-expanded="open"
            aria-label="{{ __('Quick Actions') }}"
        >
            <x-admin.icon name="plus" class="h-5 w-5" />
        </button>
    </div>
@endif
