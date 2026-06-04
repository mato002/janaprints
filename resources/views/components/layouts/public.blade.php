<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $site = config('site');
    $seo = $site['seo'];
    $pageTitle = $title ?? $seo['title'];
    $pageDescription = $metaDescription ?? $seo['description'];
    $pageUrl = $site['url'];
    $ogImage = $ogImage ?? url($seo['og_image']);
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="{{ $seo['keywords'] }}">
    <meta name="author" content="{{ $site['name'] }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $pageUrl }}">

    <title>{{ $pageTitle }}</title>

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $site['name'] }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="en_KE">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Schema.org LocalBusiness --}}
    @php
        $contact = config('conversion.contact');
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $site['name'],
            'description' => $pageDescription,
            'url' => $pageUrl,
            'telephone' => $contact['phone'],
            'email' => $contact['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Nairobi',
                'addressCountry' => 'KE',
                'streetAddress' => $contact['address'],
            ],
            'areaServed' => 'Kenya',
            'priceRange' => '$$',
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

    <noscript>
        <style>
            [data-animate], [data-image-reveal] { opacity: 1 !important; transform: none !important; }
            .public-testimonials-rotator [data-testimonial-slide] { opacity: 1 !important; position: relative !important; }
        </style>
    </noscript>

    @vite(['resources/css/app.css', 'resources/css/public.css', 'resources/js/public.js'])
</head>
<body class="font-sans antialiased text-brand-text-primary bg-white public-page">

    <a href="#main-content" class="public-skip-link">Skip to main content</a>

    <div class="public-cmyk-ambient" aria-hidden="true"></div>

    <x-public.page-loader />
    <x-public.scroll-progress />

    <x-public.header />

    <main id="main-content">
        {{ $slot }}
    </main>

    <x-public.footer />

    <x-public.conversion-sticky />
    <x-public.conversion-exit-intent />

</body>
</html>
