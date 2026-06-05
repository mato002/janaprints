<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>Sign In — {{ config('site.name', 'Jana Prints') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet">

    <noscript>
        <style>
            .login-card--centered { opacity: 1 !important; transform: none !important; }
            .login-scene__slide { opacity: 1 !important; }
            .login-scene__slide:not(:first-child) { display: none; }
        </style>
    </noscript>

    @vite(['resources/css/login.css', 'resources/js/login.js'])
</head>
<body class="login-page font-sans antialiased">
    @yield('content')
</body>
</html>
