<x-admin-layout :title="__('SMS Campaigns')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('SMS Campaigns')]]">
    @include('admin.communications.sms.partials.nav')

    <x-admin.page-header :title="__('SMS Campaigns')">
        <x-slot:actions>
            @can('create', App\Models\Communications\SmsCampaign::class)
                <x-admin.crm-btn variant="primary" :href="route('admin.communications.sms.campaigns.create')" data-turbo-frame="erp-main">{{ __('Send SMS') }}</x-admin.crm-btn>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="erp-card overflow-hidden p-0">
        <table class="erp-table w-full">
            <thead>
                <tr>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Recipients') }}</th>
                    <th>{{ __('Est. cost') }}</th>
                    <th>{{ __('Created') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaigns as $campaign)
                    <tr class="hover:bg-slate-50/80">
                        <td class="font-mono text-xs"><a href="{{ route('admin.communications.sms.campaigns.show', $campaign) }}" data-turbo-frame="erp-main" class="text-erp-accent hover:underline">{{ $campaign->campaign_code }}</a></td>
                        <td class="font-medium">{{ $campaign->name }}</td>
                        <td><span class="erp-badge erp-badge--neutral">{{ $campaign->status->label() }}</span></td>
                        <td class="tabular-nums">{{ $campaign->total_recipients }}</td>
                        <td class="tabular-nums">{{ number_format($campaign->estimated_cost, 2) }}</td>
                        <td class="text-xs text-slate-500">{{ $campaign->created_at?->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No campaigns yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($campaigns->hasPages())
            <div class="border-t border-erp-border px-4 py-3">{{ $campaigns->links() }}</div>
        @endif
    </div>
</x-admin-layout>
