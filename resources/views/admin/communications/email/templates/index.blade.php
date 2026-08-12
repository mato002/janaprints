<x-admin-layout :title="__('Email templates')" :breadcrumbs="[['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Templates')]]">
    @include('admin.communications.email.partials.mailbox-chrome')
    <x-admin.page-header :title="__('Templates')" :description="__('Reusable email templates for campaigns and follow-ups.')">
        @can('manage', App\Models\Communications\EmailCampaign::class)
            <x-slot:actions><form method="POST" action="{{ route('admin.communications.email.templates.sync') }}">@csrf<button class="erp-btn erp-btn--secondary erp-btn--sm">{{ __('Sync COM-1') }}</button></form></x-slot:actions>
        @endcan
    </x-admin.page-header>
    <div class="erp-card mb-4 overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead><tr><th>{{ __('ERP event') }}</th><th>{{ __('Category') }}</th><th>{{ __('COM-1 template') }}</th><th>{{ __('Binding') }}</th></tr></thead>
            <tbody>
                @foreach ($automationMap as $row)
                    <tr>
                        <td>{{ $row['event']->label() }}</td>
                        <td>{{ $row['category_label'] }}</td>
                        <td>{{ $row['template']?->name ?? '—' }}</td>
                        <td>{{ $row['binding'] ? __('Linked') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead><tr><th>{{ __('Template') }}</th><th>{{ __('Automation') }}</th><th>{{ __('Active') }}</th></tr></thead>
            <tbody>
                @forelse ($bindings as $binding)
                    <tr>
                        <td>{{ $binding->communicationTemplate->name }}</td>
                        <td>{{ $binding->automation_event?->label() ?? '—' }}</td>
                        <td>{{ $binding->is_active ? __('Yes') : __('No') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-slate-500">{{ __('Sync COM-1 email-channel templates first.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
