@php
    $profile = $tabData['profile'] ?? [];
    $recentQuotations = $tabData['recent_quotations'] ?? collect();
    $recentOrders = $tabData['recent_orders'] ?? collect();
    $recentArtwork = $tabData['recent_artwork'] ?? collect();
    $recentJobs = $tabData['recent_jobs'] ?? collect();
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <x-admin.card class="lg:col-span-1">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Profile summary') }}</h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ $profile['type']?->value ?? '—' }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('City') }}</dt><dd>{{ $profile['city'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Address') }}</dt><dd class="mt-0.5">{{ $profile['address'] ?? '—' }}</dd></div>
            @if (! empty($profile['website']))
                <div><dt class="text-slate-500">{{ __('Website') }}</dt><dd class="mt-0.5"><a href="{{ $profile['website'] }}" class="text-erp-accent hover:text-erp-accent-hover" target="_blank" rel="noopener">{{ $profile['website'] }}</a></dd></div>
            @endif
            @if (! empty($profile['kra_pin']))
                <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('KRA PIN') }}</dt><dd>{{ $profile['kra_pin'] }}</dd></div>
            @endif
        </dl>
    </x-admin.card>

    <div class="lg:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">
        @include('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent quotations'),
            'empty' => __('No quotations yet.'),
            'items' => $recentQuotations,
            'permission' => 'quotations.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-quotation-row',
            'tab' => 'quotations',
        ])
        @include('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent orders'),
            'empty' => __('No sales orders yet.'),
            'items' => $recentOrders,
            'permission' => 'sales_orders.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-order-row',
            'tab' => 'sales-orders',
        ])
        @include('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent artwork'),
            'empty' => __('No artwork requests yet.'),
            'items' => $recentArtwork,
            'permission' => 'artwork.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-artwork-row',
            'tab' => 'artwork',
        ])
        @include('admin.crm.customers.workspace.partials.recent-list', [
            'customer' => $customer,
            'title' => __('Recent jobs'),
            'empty' => __('No production jobs yet.'),
            'items' => $recentJobs,
            'permission' => 'production.view',
            'rowView' => 'admin.crm.customers.workspace.partials.recent-job-row',
            'tab' => 'production',
        ])
    </div>
</div>
