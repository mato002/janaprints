<details class="exec-intelligence">
    <summary class="exec-intelligence__summary">
        <span>{{ __('Operations intelligence') }}</span>
        <span class="exec-intelligence__hint">{{ __('CRM, insights, quick actions, inventory & finance') }}</span>
    </summary>
    <div class="exec-intelligence__body space-y-3">
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            @include('admin.dashboard.partials.crm-hr-pulse')
            @include('admin.dashboard.partials.smart-insights')
        </div>
        @include('admin.dashboard.partials.quick-actions')
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
            @include('admin.dashboard.partials.inventory-health')
            @include('admin.dashboard.partials.finance-snapshot')
        </div>
        @include('admin.dashboard.partials.production-performance')
    </div>
</details>
