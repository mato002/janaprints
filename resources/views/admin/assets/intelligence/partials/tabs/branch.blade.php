@php
    use App\Support\Navigation\WorkspaceEmbed;

    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<form method="GET" action="{{ WorkspaceEmbed::url($hubUrl) }}" data-turbo-frame="{{ $turboFrame }}" class="mb-4">
    <input type="hidden" name="tab" value="branch">
    <select name="branch_id" class="erp-select" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
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
