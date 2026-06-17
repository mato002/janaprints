<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}{{ __('Employee Self Service') }} — {{ config('app.name') }}</title>
    <x-site-favicon />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-erp-page text-erp-primary ess-portal ess-mobile-shell min-h-screen">
    <header class="sticky top-0 z-30 border-b border-erp-border bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-3xl items-center justify-between gap-3 px-4 py-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-erp-muted">{{ __('Employee Self Service') }}</p>
                <h1 class="truncate text-lg font-semibold text-erp-primary">{{ $title ?: __('My Workspace') }}</h1>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ess-btn ess-btn--ghost text-sm">{{ __('Sign out') }}</button>
            </form>
        </div>
        <nav class="ess-tab-nav mx-auto max-w-3xl overflow-x-auto px-2 pb-2" aria-label="{{ __('Workspace tabs') }}">
            <ul class="flex gap-1">
                @foreach ($tabs as $tab)
                    <li class="shrink-0">
                        <a
                            href="{{ route('ess.dashboard', ['tab' => $tab['id']]) }}"
                            @class([
                                'ess-tab-link',
                                'ess-tab-link--active' => $activeTab === $tab['id'],
                            ])
                        >
                            {{ $tab['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-4 pb-24">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ is_string(session('status')) ? session('status') : __('Saved successfully.') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <ul class="list-disc ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
