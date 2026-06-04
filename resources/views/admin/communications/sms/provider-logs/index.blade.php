<x-admin-layout :title="__('SMS Provider Logs')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Provider logs')]]">
    @include('admin.communications.sms.partials.nav')
    <x-admin.page-header :title="__('SMS provider logs')" :description="__('Request and response audit trail (stub provider in COM-3).')" />

    <div class="erp-card overflow-hidden p-0">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Provider') }}</th>
                    <th>{{ __('HTTP') }}</th>
                    <th>{{ __('Message ID') }}</th>
                    <th>{{ __('Phone') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td class="text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->provider }}</td>
                        <td class="tabular-nums">{{ $log->http_status }}</td>
                        <td class="font-mono text-xs">{{ $log->provider_message_id }}</td>
                        <td class="font-mono text-xs">{{ $log->message?->phone_number }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No provider logs yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($logs->hasPages())
            <div class="border-t px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>
</x-admin-layout>
