<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="{{ $refreshSeconds }}">
    <title>{{ $jobCard->job_card_number }} — {{ __('Production Floor') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .floor-display { min-height: 100vh; background: #0f172a; color: #f8fafc; padding: 2rem; font-family: system-ui, sans-serif; }
        .floor-display__grid { display: grid; gap: 1.5rem; grid-template-columns: 1fr 1fr; max-width: 1400px; margin: 0 auto; }
        .floor-display__hero { grid-column: 1 / -1; text-align: center; padding: 2rem; border: 2px solid #334155; border-radius: 1rem; }
        .floor-display__job { font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 800; letter-spacing: -0.02em; }
        .floor-display__panel { background: #1e293b; border-radius: 1rem; padding: 1.5rem 2rem; }
        .floor-display__label { font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 0.5rem; }
        .floor-display__value { font-size: clamp(1.5rem, 3vw, 2.25rem); font-weight: 700; }
        .floor-display__step { font-size: clamp(2rem, 5vw, 3.5rem); color: #38bdf8; }
        .floor-display__preview { max-height: 280px; object-fit: contain; border-radius: 0.5rem; }
        @media (max-width: 768px) { .floor-display__grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="floor-display">
    <div class="floor-display__grid">
        <div class="floor-display__hero">
            <p class="floor-display__label">{{ __('Job Card') }}</p>
            <h1 class="floor-display__job">{{ $jobCard->job_card_number }}</h1>
            <p class="mt-2 text-xl text-slate-300">{{ $jobCard->customer?->company_name }}</p>
        </div>

        <div class="floor-display__panel">
            <p class="floor-display__label">{{ __('Item') }}</p>
            <p class="floor-display__value">{{ $jobCard->inventoryItem?->item_name ?? __('—') }}</p>
            @if ($jobCard->serialAllocation)
                <p class="mt-2 text-lg text-slate-400">{{ __('Qty') }}: {{ $jobCard->serialAllocation->allocatedQuantity() }}</p>
            @endif
        </div>

        <div class="floor-display__panel">
            <p class="floor-display__label">{{ __('Due Date') }}</p>
            <p class="floor-display__value">{{ $jobCard->planned_end_date?->format('d M Y') ?? '—' }}</p>
        </div>

        <div class="floor-display__panel">
            <p class="floor-display__label">{{ __('Current Step') }}</p>
            <p class="floor-display__step">{{ $routeProgress['current']?->step_name ?? __('—') }}</p>
        </div>

        <div class="floor-display__panel">
            <p class="floor-display__label">{{ __('Next Step') }}</p>
            @php
                $current = $routeProgress['current'] ?? null;
                $next = $current
                    ? ($routeProgress['all'] ?? collect())->where('sequence', '>', $current->sequence)->first()
                    : null;
            @endphp
            <p class="floor-display__value text-slate-200">{{ $next?->step_name ?? __('—') }}</p>
        </div>

        @if ($jobCard->customerArtwork?->isPreviewable())
            <div class="floor-display__panel" style="grid-column: 1 / -1; text-align: center;">
                <p class="floor-display__label">{{ __('Artwork') }} — {{ $jobCard->customerArtwork->artwork_name }} ({{ $jobCard->customerArtwork->versionLabel() }})</p>
                @if (str_starts_with((string) $jobCard->customerArtwork->mime_type, 'image/'))
                    <img src="{{ $jobCard->customerArtwork->previewUrl() }}" alt="" class="floor-display__preview mx-auto mt-4">
                @else
                    <p class="mt-4 text-slate-400">{{ __('PDF artwork — open job card for preview') }}</p>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
