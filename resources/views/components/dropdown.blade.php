@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white border border-erp-border'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div
    class="relative"
    x-data="erpFloatingMenu(@js($align))"
    @click.outside="close()"
    @keydown.escape.window="close()"
    @scroll.window="close()"
    @resize.window="close()"
    @close.stop="close()"
>
    <div x-ref="trigger" @click.stop="toggle($event)">
        {{ $trigger }}
    </div>

    <div
        x-ref="menu"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        :style="menuStyle"
        class="{{ $width }} rounded-xl shadow-card-hover"
        @click="close()"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
