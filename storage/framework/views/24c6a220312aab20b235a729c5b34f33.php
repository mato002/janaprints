<?php if(session('export_id')): ?>
    <?php
        $export = \App\Models\CommercialReportExport::query()->find(session('export_id'));
    ?>
    <?php if($export): ?>
        <div
            class="mb-4 rounded-xl border border-erp-border bg-erp-page p-4"
            id="commercial-report-export"
            data-status-url="<?php echo e(route('admin.commercial.reports.exports.status', $export)); ?>"
            data-initial-ready="<?php echo e($export->isDownloadable() ? '1' : '0'); ?>"
        >
            <?php if($export->isDownloadable()): ?>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-erp-text"><?php echo e(__('Your export is ready.')); ?></p>
                    <div class="flex flex-wrap gap-2">
                        <a href="<?php echo e(route('admin.commercial.reports.exports.download', $export)); ?>" class="erp-btn-primary text-xs">
                            <?php echo e(__('Download :filename', ['filename' => $export->filename])); ?>

                        </a>
                        <a href="<?php echo e(route('admin.commercial.reports.exports.index')); ?>" class="erp-btn-secondary text-xs">
                            <?php echo e(__('Export History')); ?>

                        </a>
                    </div>
                </div>
            <?php elseif($export->status->value === 'failed'): ?>
                <p class="text-sm text-erp-danger"><?php echo e(__('Export failed. Please try again.')); ?></p>
                <?php if($export->error_message): ?>
                    <p class="mt-1 text-xs text-erp-muted"><?php echo e($export->error_message); ?></p>
                <?php endif; ?>
            <?php elseif($export->isExpired()): ?>
                <p class="text-sm text-amber-700"><?php echo e(__('This export has expired.')); ?></p>
            <?php else: ?>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-erp-muted" id="commercial-report-export-message">
                        <?php echo e(__('Your export is queued and processing in the background…')); ?>

                    </p>
                    <a href="<?php echo e(route('admin.commercial.reports.exports.index')); ?>" class="erp-btn-secondary text-xs">
                        <?php echo e(__('View Export History')); ?>

                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php if(! $export->isDownloadable() && ! in_array($export->status->value, ['failed', 'expired'], true) && ! $export->isExpired()): ?>
            <script>
                (function () {
                    const container = document.getElementById('commercial-report-export');
                    if (!container || container.dataset.initialReady === '1') return;

                    const statusUrl = container.dataset.statusUrl;
                    const readyLabel = <?php echo json_encode(__('Your export is ready.'), 15, 512) ?>;
                    const downloadLabel = <?php echo json_encode(__('Download'), 15, 512) ?>;
                    const failedLabel = <?php echo json_encode(__('Export failed. Please try again.'), 15, 512) ?>;
                    const expiredLabel = <?php echo json_encode(__('This export has expired.'), 15, 512) ?>;

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
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/reports/partials/export-status.blade.php ENDPATH**/ ?>