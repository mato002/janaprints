<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description" />

    @include('admin.commercial.reports.sales.partials.readiness-table', [
        'readiness' => $readiness,
        'report_ready' => $framework_ready,
        'context' => __('commercial report exports'),
    ])

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2 font-semibold">{{ __('Report') }}</th>
                        <th class="px-3 py-2 font-semibold">{{ __('Tab') }}</th>
                        <th class="px-3 py-2 font-semibold">{{ __('Format') }}</th>
                        <th class="px-3 py-2 font-semibold">{{ __('Status') }}</th>
                        <th class="px-3 py-2 font-semibold">{{ __('Requested By') }}</th>
                        <th class="px-3 py-2 font-semibold">{{ __('Queued') }}</th>
                        <th class="px-3 py-2 font-semibold">{{ __('Rows') }}</th>
                        <th class="px-3 py-2 font-semibold">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exports as $export)
                        <tr class="border-b border-erp-border/60" data-export-id="{{ $export->id }}">
                            <td class="px-3 py-2 font-medium text-erp-primary">{{ $export->moduleLabel() }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ ucfirst(str_replace('_', ' ', $export->tab)) }}</td>
                            <td class="px-3 py-2 uppercase text-slate-600">{{ $export->format }}</td>
                            <td class="px-3 py-2">
                                @include('admin.commercial.reports.exports.partials.status-badge', ['export' => $export])
                            </td>
                            <td class="px-3 py-2 text-slate-600">{{ $export->user?->name ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $export->queued_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ $export->row_count ?? '—' }}</td>
                            <td class="px-3 py-2">
                                @if ($can_download && $export->isDownloadable())
                                    <a href="{{ route('commercial.reports.exports.download', $export) }}" class="erp-btn-primary text-xs">
                                        {{ __('Download') }}
                                    </a>
                                @elseif ($export->status->value === 'failed')
                                    <span class="text-xs text-erp-danger">{{ Str::limit($export->error_message, 40) }}</span>
                                @elseif ($export->isExpired())
                                    <span class="text-xs text-slate-500">{{ __('Expired') }}</span>
                                @elseif (in_array($export->status->value, ['queued', 'processing'], true))
                                    <span class="text-xs text-slate-500 export-pending" data-status-url="{{ route('commercial.reports.exports.status', $export) }}">{{ __('Processing…') }}</span>
                                @else
                                    <span class="text-xs text-slate-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-slate-500">{{ __('No exports yet. Use Export on any commercial report workspace.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($exports->hasPages())
            <div class="mt-4">
                {{ $exports->links() }}
            </div>
        @endif
    </x-admin.card>

    @if ($exports->contains(fn ($export) => in_array($export->status->value, ['queued', 'processing'], true)))
        <script>
            (function () {
                const pending = document.querySelectorAll('.export-pending');
                pending.forEach((el) => {
                    const url = el.dataset.statusUrl;
                    const poll = () => {
                        fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                            .then((r) => r.json())
                            .then((data) => {
                                if (data.ready || data.failed || data.expired) {
                                    window.location.reload();
                                    return;
                                }
                                window.setTimeout(poll, 3000);
                            })
                            .catch(() => window.setTimeout(poll, 5000));
                    };
                    window.setTimeout(poll, 3000);
                });
            })();
        </script>
    @endif
</x-admin-layout>
