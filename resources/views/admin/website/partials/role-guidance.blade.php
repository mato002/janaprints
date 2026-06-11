@php
    $context = $context ?? 'gallery';
@endphp

@if ($context === 'gallery' && auth()->user()->can('website.gallery.view') && ! auth()->user()->can('website.gallery.publish'))
    <x-admin.alert variant="info" class="mb-4">
        {{ __('You can prepare gallery content. Publishing requires approval permission.') }}
    </x-admin.alert>
@endif

@if ($context === 'settings' && auth()->user()->can('website.settings.view') && ! auth()->user()->can('website.settings.edit'))
    <x-admin.alert variant="info" class="mb-4">
        {{ __('Contact administrator for website settings changes.') }}
    </x-admin.alert>
@endif
