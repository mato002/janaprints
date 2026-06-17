<x-admin-layout :title="__('Communication reports')" :breadcrumbs="[['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Reports')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Department communication reports')" :description="__('Email volume and failures by department mailbox.')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.communications.email.reports.index')" :reset-url="route('admin.communications.email.reports.index')" compact>
            <x-admin.filter-pill-date name="date_from" :label="__('From date')" :value="$filters['date_from'] ?? ''" />
            <x-admin.filter-pill-date name="date_to" :label="__('To date')" :value="$filters['date_to'] ?? ''" />
        </x-admin.index-toolbar>
    </x-admin.card>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($departments as $key => $department)
            <div class="erp-card">
                <h2 class="erp-card-title">{{ $department['label'] }}</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ __('Emails sent') }}</dt>
                        <dd class="font-semibold tabular-nums">{{ $department['sent'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ __('Failures') }}</dt>
                        <dd class="font-semibold tabular-nums text-red-600">{{ $department['failed'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">{{ __('Queued') }}</dt>
                        <dd class="font-semibold tabular-nums">{{ $department['queued'] }}</dd>
                    </div>
                </dl>
            </div>
        @endforeach
    </div>
</x-admin-layout>
