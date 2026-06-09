@props([
    'title',
    'maxWidth' => 'md',
])

@php
    $inDrawer = request()->header('Turbo-Frame') === 'erp-preview-drawer';
    $drawerMaxWidth = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

@if ($inDrawer)
    <turbo-frame id="erp-preview-drawer">
        <div @class(['erp-drawer', $drawerMaxWidth]) data-erp-drawer-panel>
            <div class="erp-drawer__header">
                <h2 class="erp-drawer__title">{{ $title }}</h2>
                <button
                    type="button"
                    class="erp-drawer__close"
                    data-erp-drawer-close
                    aria-label="{{ __('Close') }}"
                >
                    <x-admin.icon name="x-mark" class="h-5 w-5" />
                </button>
            </div>
            <div class="erp-drawer__body">
                {{ $slot }}
            </div>
        </div>
    </turbo-frame>
@else
    <x-admin-layout :title="$title">
        <div class="bg-white shadow rounded-lg p-6">
            {{ $slot }}
        </div>
    </x-admin-layout>
@endif
