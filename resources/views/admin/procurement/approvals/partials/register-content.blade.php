@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Procurement approvals')" :description="__('Pending, aging, escalated, and rejected procurement approval chains.')" />
@else
    <div class="mb-3">
        <h2 class="text-sm font-semibold text-erp-primary">{{ $registerTitle ?? __('Procurement approvals') }}</h2>
        @if (! empty($registerDescription))
            <p class="text-xs text-slate-600">{{ $registerDescription }}</p>
        @endif
    </div>
@endif

@include('admin.procurement.approvals.partials.table', ['rows' => $sections['pending'], 'title' => __('Pending Procurement Approvals')])
@include('admin.procurement.approvals.partials.table', ['rows' => $sections['aging'], 'title' => __('Aging Approvals')])
@include('admin.procurement.approvals.partials.table', ['rows' => $sections['escalated'], 'title' => __('Escalated Approvals')])
@include('admin.procurement.approvals.partials.table', ['rows' => $sections['rejected'], 'title' => __('Rejected Approvals')])
