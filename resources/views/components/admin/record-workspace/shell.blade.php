@props([
    'class' => null,
])

{{--
  Shared Record Workspace shell.
  Slots: header, workflow, actions, main, rail, modals (optional)
--}}
<div {{ $attributes->class(['rw', $class]) }}>
    @isset($header)
        <div class="rw__chrome">
            {{ $header }}
        </div>
    @endisset

    @isset($workflow)
        <div class="rw__workflow-wrap">
            {{ $workflow }}
        </div>
    @endisset

    @isset($actions)
        <div class="rw__actions-wrap">
            {{ $actions }}
        </div>
    @endisset

    <div class="rw__body">
        <div class="rw__main">
            {{ $main ?? $slot }}
        </div>

        @isset($rail)
            <aside class="rw__rail" aria-label="{{ __('Record intelligence') }}">
                {{ $rail }}
            </aside>
        @endisset
    </div>

    @isset($modals)
        {{ $modals }}
    @endisset
</div>
