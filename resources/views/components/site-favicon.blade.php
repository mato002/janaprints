@props([
    'url' => null,
])

@php
    $iconUrl = $url ?: url(config('site.local.logo'));
@endphp

<link rel="icon" href="{{ $iconUrl }}" type="image/png">
<link rel="apple-touch-icon" href="{{ $iconUrl }}">
