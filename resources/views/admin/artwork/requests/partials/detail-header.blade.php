@php
    $designerOperator = $designerOperator ?? auth()->user()?->prefersDesignerOperatorMode() ?? false;
    $compact = (bool) ($compact ?? false);
@endphp

<header @class(['artwork-detail-header', 'mb-1' => $compact, 'mb-2' => ! $compact])>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <p class="artwork-detail-header__number">{{ $request->request_number }}</p>
            <h1 class="artwork-detail-header__title">{{ $request->title }}</h1>
            @if ($request->customer?->company_name)
                <p class="artwork-detail-header__customer">{{ $request->customer->company_name }}</p>
            @endif
        </div>
        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
            @if ($designerOperator)
                <a href="{{ route('admin.artwork.desk') }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Back to Designer Desk') }}</a>
            @endif
            <x-admin.artwork-status-badge :status="$request->status" />
            <span class="text-sm tabular-nums text-slate-500">v{{ $request->current_version ?: '0' }}</span>
            @can('update', $request)
                <a href="{{ route('admin.artwork.edit', $request) }}" class="erp-btn-secondary text-sm">{{ __('Edit') }}</a>
            @endcan
        </div>
    </div>
</header>
