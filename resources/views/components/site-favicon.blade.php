@props([
    'url' => null,
])

@php
    $iconUrl = $url ?? ($brandingFaviconUrl ?? url(config('site.local.favicon', config('site.local.logo'))));
@endphp

<link rel="icon" href="{{ $iconUrl }}" type="image/png">
<link rel="apple-touch-icon" href="{{ $iconUrl }}">
