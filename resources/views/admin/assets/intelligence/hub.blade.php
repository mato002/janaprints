@php
    use App\Support\Navigation\WorkspaceEmbed;

    $hubUrl = WorkspaceEmbed::url($hubUrl);
@endphp

<x-admin-layout
    :title="__('Intelligence')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Intelligence')],
    ]"
>
    <x-admin.page-header
        :title="__('Asset Intelligence')"
        :description="match ($activeTab) {
            'branch' => __('Per-branch asset profile and utilization.'),
            'analytics' => __('Trends, distributions, and lifecycle analytics.'),
            default => __('Company-wide asset valuation and risk KPIs.'),
        }"
    />

    @include('admin.assets.intelligence.partials.tabs-nav')

    @include('admin.assets.intelligence.partials.tabs.' . str_replace('-', '_', $activeTab))
</x-admin-layout>
