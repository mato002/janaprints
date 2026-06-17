@props([
    'action',
    'resetUrl' => null,
    'turboFrame' => null,
    'method' => 'GET',
    'pills' => [],
    'pillParam' => 'status',
    'activePill' => null,
    'showReset' => true,
    'compact' => false,
])

@php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolvedTurboFrame = $turboFrame ?? WorkspaceEmbed::turboFrame();
    $resolvedAction = WorkspaceEmbed::url($action) ?? $action;
    $resolvedResetUrl = $resetUrl ? (WorkspaceEmbed::url($resetUrl) ?? $resetUrl) : null;
    $embedded = WorkspaceEmbed::inWorkspaceContext();
@endphp

<form
    method="{{ $method }}"
    action="{{ $resolvedAction }}"
    {{ $attributes->merge(['class' => 'erp-index-toolbar-form']) }}
    @if ($resolvedTurboFrame) data-turbo-frame="{{ $resolvedTurboFrame }}" @endif
>
    @if ($embedded)
        <input type="hidden" name="embedded" value="1">
    @endif
    <div class="erp-index-toolbar border-b border-erp-border bg-white px-4 py-3">
        <div @class(['flex items-center gap-2', 'erp-index-toolbar-row' => $compact])>
            <div @class([
                'flex min-w-0 flex-1 items-center gap-1.5',
                'flex-nowrap' => $compact,
                'flex-wrap' => ! $compact,
            ])>
                @if (count($pills) > 1)
                    <x-admin.status-pills
                        :options="$pills"
                        :param="$pillParam"
                        :current="$activePill"
                        :turbo-frame="$resolvedTurboFrame"
                    />
                @endif

                {{ $slot }}

                @if ($showReset)
                    <button
                        type="button"
                        data-erp-filter-reset
                        class="erp-btn-ghost shrink-0 py-1 text-xs text-slate-500"
                    >{{ __('Reset') }}</button>
                @endif
            </div>

            <div class="ml-auto flex shrink-0 items-center gap-2">
                @isset($pagination)
                    {{ $pagination }}
                @endisset

                @isset($actions)
                    {{ $actions }}
                @endisset

                @isset($export)
                    {{ $export }}
                @endisset
            </div>
        </div>

        @isset($secondary)
            <div class="mt-2 flex w-full flex-wrap items-center gap-2 border-t border-erp-border/60 pt-2">
                {{ $secondary }}
            </div>
        @endisset
    </div>
</form>
