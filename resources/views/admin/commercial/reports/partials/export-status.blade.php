@if (session('export_id'))
    @php
        $export = \App\Models\CommercialReportExport::query()->find(session('export_id'));
    @endphp
    @if ($export)
        <div
            class="mb-4 rounded-xl border border-erp-border bg-erp-page p-4"
            id="commercial-report-export"
            data-status-url="{{ route('admin.commercial.reports.exports.status', $export) }}"
            data-initial-ready="{{ $export->isDownloadable() ? '1' : '0' }}"
        >
            @if ($export->isDownloadable())
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-erp-text">{{ __('Your export is ready.') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.commercial.reports.exports.download', $export) }}" class="erp-btn-primary text-xs">
                            {{ __('Download :filename', ['filename' => $export->filename]) }}
                        </a>
                        <a href="{{ route('admin.commercial.reports.exports.index') }}" class="erp-btn-secondary text-xs">
                            {{ __('Export History') }}
                        </a>
                    </div>
                </div>
            @elseif ($export->status->value === 'failed')
                <p class="text-sm text-erp-danger">{{ __('Export failed. Please try again.') }}</p>
                @if ($export->error_message)
                    <p class="mt-1 text-xs text-erp-muted">{{ $export->error_message }}</p>
                @endif
            @elseif ($export->isExpired())
                <p class="text-sm text-amber-700">{{ __('This export has expired.') }}</p>
            @else
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-erp-muted" id="commercial-report-export-message">
                        {{ __('Your export is queued and processing in the background…') }}
                    </p>
                    <a href="{{ route('admin.commercial.reports.exports.index') }}" class="erp-btn-secondary text-xs">
                        {{ __('View Export History') }}
                    </a>
                </div>
            @endif
        </div>
        @if (! $export->isDownloadable() && ! in_array($export->status->value, ['failed', 'expired'], true) && ! $export->isExpired())
            <script>
                (function () {
                    const container = document.getElementById('commercial-report-export');
                    if (!container || container.dataset.initialReady === '1') return;

                    const statusUrl = container.dataset.statusUrl;
                    const readyLabel = @json(__('Your export is ready.'));
                    const downloadLabel = @json(__('Download'));
                    const failedLabel = @json(__('Export failed. Please try again.'));
                    const expiredLabel = @json(__('This export has expired.'));

                    const poll = () => {
                        fetch(statusUrl, {
                            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        })
                            .then((response) => response.json())
                            .then((data) => {
                                if (data.ready && data.download_url) {
                                    const filename = data.filename ? ` (${data.filename})` : '';
                                    container.innerHTML = `
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <p class="text-sm text-erp-text">${readyLabel}</p>
                                            <a href="${data.download_url}" class="erp-btn-primary text-xs">${downloadLabel}${filename}</a>
                                        </div>`;
                                    return;
                                }
                                if (data.failed) {
                                    container.innerHTML = `<p class="text-sm text-erp-danger">${failedLabel}</p>`;
                                    return;
                                }
                                if (data.expired) {
                                    container.innerHTML = `<p class="text-sm text-amber-700">${expiredLabel}</p>`;
                                    return;
                                }
                                window.setTimeout(poll, 2000);
                            })
                            .catch(() => window.setTimeout(poll, 3000));
                    };

                    window.setTimeout(poll, 2000);
                })();
            </script>
        @endif
    @endif
@endif
