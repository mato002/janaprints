<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-public.seo-meta :seo="$seo ?? null" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

    <noscript>
        <style>
            [data-animate], [data-image-reveal] { opacity: 1 !important; transform: none !important; }
            .public-testimonials-rotator [data-testimonial-slide] { opacity: 1 !important; position: relative !important; }
        </style>
    </noscript>

    @vite(['resources/css/public.css', 'resources/js/public.js'])
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
