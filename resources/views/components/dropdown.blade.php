@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white border border-erp-border'])

@php
$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div
    class="relative z-20 inline-block text-left"
    x-data="erpRowActionsMenu(@js($align))"
    @click.outside="closeFromOutside()"
    @keydown.escape.window="close()"
    @scroll.window="close()"
    @resize.window="close()"
>
    <div x-ref="trigger" @click.stop="toggle($event)">
        {{ $trigger }}
    </div>

    <div
        x-ref="menu"
        :style="open ? menuStyle : null"
        :class="open ? 'erp-row-actions-menu--open' : ''"
        class="erp-row-actions-menu {{ $width }} rounded-xl shadow-card-hover"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
