@props([
    'theme' => 'slate',
    'title',
    'icon' => null,
    'compact' => false,
])

<div {{ $attributes->class([
    'job-360-module',
    'job-360-module--'.$theme,
    'job-360-module--compact' => $compact,
]) }}>
    <div class="job-360-module__accent" aria-hidden="true"></div>
    <div class="job-360-module__head">
        @if ($icon)
            <span class="job-360-module__icon-wrap">
                <x-admin.icon :name="$icon" class="job-360-module__icon h-4 w-4" />
            </span>
        @endif
        <h3 class="job-360-module__title">{{ $title }}</h3>
        @isset($actions)
            <div class="job-360-module__actions">{{ $actions }}</div>
        @endisset
    </div>
    <div class="job-360-module__body">
        {{ $slot }}
    </div>
</div>
