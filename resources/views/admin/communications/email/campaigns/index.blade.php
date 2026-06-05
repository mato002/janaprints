<x-admin-layout :title="__('Email campaigns')" :breadcrumbs="[['label' => __('Email Center'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Campaigns')]]">
    @include('admin.communications.email.partials.nav')
    <x-admin.page-header :title="__('Campaigns')">
        @can('create', App\Models\Communications\EmailCampaign::class)
            <x-slot:actions>
                <x-admin.crm-btn variant="primary" size="sm" :href="route('admin.communications.email.campaigns.create')" data-turbo-frame="erp-main">{{ __('New campaign') }}</x-admin.crm-btn>
            </x-slot:actions>
        @endcan
    </x-admin.page-header>
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full">
            <thead><tr><th>{{ __('Campaign') }}</th><th>{{ __('Type') }}</th><th>{{ __('Status') }}</th><th>{{ __('Sent') }}</th></tr></thead>
            <tbody>
                @forelse ($campaigns as $campaign)
                    <tr>
                        <td><a href="{{ route('admin.communications.email.campaigns.show', $campaign) }}" class="text-erp-accent font-medium" data-turbo-frame="erp-main">{{ $campaign->name }}</a></td>
                        <td>{{ $campaign->campaign_type->label() }}</td>
                        <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $campaign->status->badgeClass() }}">{{ $campaign->status->label() }}</span></td>
                        <td class="tabular-nums">{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('No campaigns yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($campaigns->hasPages())<div class="mt-3">{{ $campaigns->links() }}</div>@endif
    </div>
</x-admin-layout>
