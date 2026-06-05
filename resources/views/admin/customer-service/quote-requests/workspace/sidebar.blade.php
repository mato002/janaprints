<aside class="qr-intake__sidebar space-y-4 xl:sticky xl:top-24">
    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Intake Snapshot') }}</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Status') }}</dt>
                <dd><x-admin.status-badge :variant="$quoteRequest->status->badgeVariant()">{{ $quoteRequest->status->workspaceLabel() }}</x-admin.status-badge></dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Priority') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['sidebar']['priority'] }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Assigned To') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['sidebar']['assigned_to'] }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Artwork Files') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['sidebar']['artwork_count'] }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Submitted') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['sidebar']['submitted_at']->format('d M Y') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Last Updated') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['sidebar']['updated_at']->diffForHumans() }}</dd>
            </div>
        </dl>
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Conversion Links') }}</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Customer') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['links']['customer_exists'] ? __('Linked') : __('Not linked') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Lead') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['links']['lead_exists'] ? __('Linked') : __('Not linked') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Quotation') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['links']['quotation_exists'] ? __('Linked') : __('Not linked') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-slate-500">{{ __('Order') }}</dt>
                <dd class="font-medium text-slate-800">{{ $workspace['links']['order_exists'] ? __('Linked') : __('Not linked') }}</dd>
            </div>
        </dl>
    </section>
</aside>
