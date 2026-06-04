<x-admin-layout :title="__('Tax Audit Trail')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Tax Audit')]]">
    <x-admin.page-header :title="__('Tax Audit Trail')" />

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                    <th class="py-2">{{ __('When') }}</th>
                    <th class="py-2">{{ __('User') }}</th>
                    <th class="py-2">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr class="border-b border-erp-border/50">
                        <td class="py-2">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="py-2">{{ $log->user?->name ?? '—' }}</td>
                        <td class="py-2 font-mono text-xs">{{ $log->action }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
