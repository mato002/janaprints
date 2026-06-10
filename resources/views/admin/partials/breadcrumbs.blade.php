@if (! empty($workspaceNavigation))
    @include('admin.partials.workspace-back', ['compact' => ! empty($compact)])
@endif

@if (! empty($breadcrumbs))
    <nav @class([
        'text-slate-500',
        'mb-2 text-xs' => ! empty($compact),
        'mb-4 text-sm' => empty($compact),
    ]) aria-label="{{ __('Breadcrumb') }}">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li>
                <a href="{{ route('admin.dashboard') }}" data-turbo-frame="erp-main" data-turbo-action="advance" class="font-medium transition-colors hover:text-erp-accent">{{ __('Dashboard') }}</a>
            </li>
            @foreach ($breadcrumbs as $crumb)
                <li class="flex items-center gap-1.5" aria-current="{{ empty($crumb['url']) ? 'page' : false }}">
                    <span class="text-slate-300" aria-hidden="true">/</span>
                    @if (! empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" data-turbo-frame="erp-main" data-turbo-action="advance" class="transition-colors hover:text-erp-accent">{{ $crumb['label'] }}</a>
                    @else
                        <span class="font-medium text-erp-primary">{{ $crumb['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
