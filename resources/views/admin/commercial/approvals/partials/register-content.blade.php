@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Commercial approvals queue')" :description="__('Pending quotations, sales orders, and artwork submissions.')" />
@else
    <div class="mb-3">
        <h2 class="text-sm font-semibold text-erp-primary">{{ $registerTitle ?? __('Commercial approvals') }}</h2>
        @if (! empty($registerDescription))
            <p class="text-xs text-slate-600">{{ $registerDescription }}</p>
        @endif
    </div>
@endif

@include('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_quotations'], 'title' => __('Pending Quotations')])
@include('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_sales_orders'], 'title' => __('Pending Sales Orders')])
@include('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_artwork'], 'title' => __('Pending Artwork')])
@include('admin.commercial.approvals.partials.table', ['rows' => $sections['recently_approved'], 'title' => __('Recently Approved')])
@include('admin.commercial.approvals.partials.table', ['rows' => $sections['recently_rejected'], 'title' => __('Recently Rejected')])
