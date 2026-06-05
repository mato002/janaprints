<x-admin-layout :title="$run->run_number" :breadcrumbs="[['label' => __('Depreciation Runs'), 'url' => route('admin.assets.finance.runs.index')], ['label' => $run->run_number]]">
    <x-admin.page-header :title="$run->run_number" :description="__('Period :period', ['period' => $run->period])">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$run->status->badgeVariant()">{{ $run->status->label() }}</x-admin.status-badge>
            @if ($run->is_dry_run)<x-admin.status-badge variant="warning">{{ __('Dry Run') }}</x-admin.status-badge>@endif
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Total Depreciation') }}</dt><dd class="font-semibold">{{ number_format($run->total_depreciation, 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Assets Processed') }}</dt><dd>{{ $run->assets_processed }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Executed By') }}</dt><dd>{{ $run->executor?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Run Date') }}</dt><dd>{{ $run->run_date?->format('Y-m-d') }}</dd></div>
            </dl>
            @if ($run->preview_summary)
                <h3 class="mb-2 mt-4 text-sm font-semibold">{{ __('Preview') }}</h3>
                <div class="overflow-x-auto">
                    <table class="erp-table w-full text-sm">
                        <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Amount') }}</th><th>{{ __('NBV After') }}</th></tr></thead>
                        <tbody>
                            @foreach (($run->preview_summary['assets'] ?? []) as $row)
                                <tr>
                                    <td>{{ $row['asset_number'] }} — {{ $row['asset_name'] }}</td>
                                    <td>{{ number_format($row['depreciation_amount'], 2) }}</td>
                                    <td>{{ number_format($row['net_book_value_after'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Actions') }}</h3>
            <div class="flex flex-col gap-2">
                @if ($run->status === \App\Enums\DepreciationRunStatus::Draft)
                    <form method="POST" action="{{ route('admin.assets.finance.runs.preview', $run) }}">@csrf<button class="erp-btn-secondary w-full">{{ __('Refresh Preview') }}</button></form>
                    @can('post', $run)
                        @unless($run->is_dry_run)
                            <form method="POST" action="{{ route('admin.assets.finance.runs.execute', $run) }}">@csrf<button class="erp-btn-primary w-full">{{ __('Execute & Post') }}</button></form>
                        @endunless
                    @endcan
                    <form method="POST" action="{{ route('admin.assets.finance.runs.cancel', $run) }}">@csrf<button class="erp-btn-secondary w-full">{{ __('Cancel') }}</button></form>
                @endif
            </div>
        </x-admin.card>
    </div>
</x-admin-layout>
