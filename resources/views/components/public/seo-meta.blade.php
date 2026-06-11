@props([
    'seo' => null,
    'title' => null,
    'metaDescription' => null,
    'canonical' => null,
    'robots' => null,
    'ogImage' => null,
    'ogType' => null,
    'structuredData' => [],
])

@php
    $site = $websiteSite ?? config('site');
    $seoDefaults = $websiteSeo ?? ($site['seo'] ?? config('site.seo', []));

    if ($seo instanceof \App\Support\Storefront\SeoMeta) {
        $pageTitle = $seo->title;
        $pageDescription = $seo->description;
        $pageUrl = $seo->canonical;
        $pageRobots = $seo->robots;
        $resolvedOgImage = $seo->ogImageUrl();
        $resolvedOgType = $seo->ogType;
        $twitterTitle = $seo->twitterTitle();
        $twitterDescription = $seo->twitterDescription();
        $twitterImage = $seo->twitterImageUrl();
        $jsonLdBlocks = $seo->structuredData ?: $structuredData;
    } else {
        $pageTitle = $title ?? $seoDefaults['title'];
        $pageDescription = $metaDescription ?? $seoDefaults['description'];
        $pageUrl = $canonical ?? url()->current();
        $pageRobots = $robots ?? 'index, follow';
        $settingsOgPath = $seoDefaults['og_image'] ?? null;
        $defaultOgPath = $settingsOgPath
            ? (str_starts_with((string) $settingsOgPath, 'http') ? $settingsOgPath : url($settingsOgPath))
            : url(app(\App\Services\Website\WebsiteMediaResolver::class)->resolvePath('seo.og_image'));
        $resolvedOgImage = $ogImage
            ? (str_starts_with((string) $ogImage, 'http') ? $ogImage : url($ogImage))
            : $defaultOgPath;
        $resolvedOgType = $ogType ?? 'website';
        $twitterTitle = $pageTitle;
        $twitterDescription = $pageDescription;
        $twitterImage = $resolvedOgImage;
        $jsonLdBlocks = $structuredData;
    }

    $analytics = $site['analytics'];
@endphp

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{{ $pageDescription }}">
<meta name="keywords" content="{{ $seoDefaults['keywords'] }}">
<meta name="author" content="{{ $site['name'] }}">
<meta name="robots" content="{{ $pageRobots }}">
<link rel="canonical" href="{{ $pageUrl }}">

<title>{{ $pageTitle }}</title>

@if ($analytics['google_search_console_verification'])
    <meta name="google-site-verification" content="{{ $analytics['google_search_console_verification'] }}">
@endif

<meta property="og:type" content="{{ $resolvedOgType }}">
<meta property="og:site_name" content="{{ $site['name'] }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:image" content="{{ $resolvedOgImage }}">
<meta property="og:locale" content="en_KE">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $twitterTitle }}">
<meta name="twitter:description" content="{{ $twitterDescription }}">
<meta name="twitter:image" content="{{ $twitterImage }}">
@if ($seoDefaults['twitter_site'])
    <meta name="twitter:site" content="{{ $seoDefaults['twitter_site'] }}">
@endif

<link rel="icon" href="{{ $brandingFaviconUrl ?? url($site['local']['favicon'] ?? $site['local']['logo']) }}" type="image/png">
<link rel="apple-touch-icon" href="{{ $brandingFaviconUrl ?? url($site['local']['favicon'] ?? $site['local']['logo']) }}">

@foreach ($jsonLdBlocks as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach

@if ($analytics['google_analytics_id'])
    {{-- GA4 placeholder: wire gtag.js when measurement ID is configured --}}
    <meta name="ga-measurement-id" content="{{ $analytics['google_analytics_id'] }}">
@endif

@if ($analytics['facebook_pixel_id'])
    <meta name="facebook-pixel-id" content="{{ $analytics['facebook_pixel_id'] }}">
@endif

@if ($analytics['tiktok_pixel_id'])
    <meta name="tiktok-pixel-id" content="{{ $analytics['tiktok_pixel_id'] }}">
@endif
