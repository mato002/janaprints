<x-admin-layout :title="__('Organization Chart')" :breadcrumbs="[['label' => __('Organization')], ['label' => __('Job Titles'), 'url' => route('admin.job-titles.index')], ['label' => __('Organization Chart')]]">
    <x-admin.page-header :title="__('Organization Chart')" :description="__('Reporting hierarchy by job title with branch, department, and employee counts.')">
        <x-slot name="actions">
            <a href="{{ route('admin.job-titles.index') }}" class="erp-btn-secondary">{{ __('Back to job titles') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <x-admin.kpi-widget :label="__('Company')" :value="$hierarchy['company']?->name ?? '—'" icon="building" />
        <x-admin.kpi-widget :label="__('Branches')" :value="$hierarchy['branches']->count()" icon="location-marker" />
        <x-admin.kpi-widget :label="__('Departments')" :value="$hierarchy['departments']->count()" icon="view-grid" />
    </div>

    <section class="erp-card mb-6">
        <h2 class="mb-4 text-base font-semibold text-erp-primary">{{ __('Branch Overview') }}</h2>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($hierarchy['branches'] as $branch)
                <div class="rounded-lg border border-slate-200 p-3">
                    <div class="font-medium text-slate-800">{{ $branch->name }}</div>
                    <div class="text-sm text-slate-500">{{ trans_choice(':count employee|:count employees', $branch->employees_count, ['count' => $branch->employees_count]) }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="erp-card mb-6">
        <h2 class="mb-4 text-base font-semibold text-erp-primary">{{ __('Department Overview') }}</h2>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($hierarchy['departments'] as $department)
                <div class="rounded-lg border border-slate-200 p-3">
                    <div class="font-medium text-slate-800">{{ $department->name }}</div>
                    <div class="text-sm text-slate-500">{{ trans_choice(':count employee|:count employees', $department->employees_count, ['count' => $department->employees_count]) }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="erp-card">
        <h2 class="mb-4 text-base font-semibold text-erp-primary">{{ __('Reporting Lines') }}</h2>
        @if (empty($hierarchy['nodes']))
            <p class="text-sm text-slate-500">{{ __('No active job titles configured yet.') }}</p>
        @else
            <div class="space-y-3">
                @foreach ($hierarchy['nodes'] as $node)
                    @include('admin.job-titles.partials.hierarchy-node', ['node' => $node, 'depth' => 0])
                @endforeach
            </div>
        @endif
    </section>
</x-admin-layout>
