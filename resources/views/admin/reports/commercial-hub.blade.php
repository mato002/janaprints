<x-admin-layout :title="$title" :breadcrumbs="[['label' => __('Reports & Intelligence'), 'url' => route('admin.workspaces.reports')], ['label' => $title]]">
    <x-admin.page-header :title="$title" :description="$description" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($links as $link)
            @php
                $href = isset($link['route_params'])
                    ? route($link['route'], $link['route_params'])
                    : route($link['route']);
            @endphp
            <a href="{{ $href }}" class="erp-card block p-5 transition hover:border-erp-primary/40 hover:shadow-sm" data-turbo-frame="erp-main">
                <h3 class="text-base font-semibold text-slate-900">{{ $link['label'] }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $link['description'] }}</p>
                <span class="mt-3 inline-flex text-sm font-medium text-erp-primary">{{ __('Open') }} →</span>
            </a>
        @endforeach
    </div>
</x-admin-layout>
