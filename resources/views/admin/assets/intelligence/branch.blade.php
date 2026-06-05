<x-admin-layout :title="__('Branch Asset Intelligence')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Branch Intelligence')]]">
    <x-admin.page-header :title="__('Branch Asset Intelligence')" :description="$stats['branch']->name" />

    <form method="GET" class="mb-4">
        <select name="branch_id" class="erp-select" onchange="this.form.submit()">
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected($selected_branch_id == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Assets')" :value="$stats['asset_count']" icon="chip" />
        <x-admin.kpi-widget :label="__('Asset Value')" :value="number_format($stats['asset_value'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Machines')" :value="$stats['machines']" icon="cog" />
        <x-admin.kpi-widget :label="__('Unassigned')" :value="$stats['unassigned']" icon="user" />
    </div>
</x-admin-layout>
