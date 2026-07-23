<?php
    /** @var array<string, mixed> $row */
    $job = $row['job'];
    $workflow = $row['workflow'];
    $eligible = (bool) ($row['eligible_for_delivery_note'] ?? false);
?>
<tr>
    <td class="font-mono text-xs whitespace-nowrap">
        <a href="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::mainUrl(route('admin.production.job-cards.show', ['jobCard' => $job, 'tab' => 'dispatch']))); ?>" class="text-erp-accent hover:underline" data-turbo-frame="erp-main" data-turbo-action="advance">
            <?php echo e($job->job_card_number); ?>

        </a>
    </td>
    <td><?php echo e($job->customer?->company_name ?? '—'); ?></td>
    <td>
        <?php echo e($job->inventoryItem?->item_name ?? '—'); ?>

        <?php if($job->inventoryItem?->sku): ?>
            <span class="block text-[11px] text-slate-500"><?php echo e($job->inventoryItem->sku); ?></span>
        <?php endif; ?>
    </td>
    <td class="whitespace-nowrap text-xs">
        <?php echo e($job->required_date?->format('Y-m-d') ?? $job->salesOrder?->required_date?->format('Y-m-d') ?? '—'); ?>

    </td>
    <td>
        <?php
            $variant = $workflow['status_variant'] ?? 'warning';
            $badgeClass = match ($variant) {
                'success' => 'bg-emerald-100 text-emerald-800',
                default => 'bg-amber-100 text-amber-900',
            };
        ?>
        <span class="erp-badge <?php echo e($badgeClass); ?>"><?php echo e($workflow['status_label'] ?? __('Blocked')); ?></span>
        <?php if(! $eligible && ! empty($workflow['blockers'])): ?>
            <p class="mt-1 max-w-xs text-[11px] leading-snug text-slate-500"><?php echo e($workflow['blockers'][0]); ?></p>
        <?php endif; ?>
    </td>
    <td class="erp-table-actions-col">
        <?php if($eligible && ($canCreateNote ?? false)): ?>
            <form method="POST" action="<?php echo e(route('admin.dispatch.delivery-notes.store-from-job', $job)); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="erp-btn-primary text-xs py-1"><?php echo e(__('Create delivery note')); ?></button>
            </form>
        <?php elseif(! empty($workflow['next_step']['url'])): ?>
            <a
                href="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::mainUrl($workflow['next_step']['url'])); ?>"
                class="erp-btn-secondary text-xs py-1"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            ><?php echo e($workflow['next_step']['label']); ?></a>
        <?php else: ?>
            <a href="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::mainUrl(route('admin.production.job-cards.show', ['jobCard' => $job, 'tab' => 'dispatch']))); ?>" class="text-sm text-erp-accent hover:underline" data-turbo-frame="erp-main" data-turbo-action="advance"><?php echo e(__('Open job')); ?></a>
        <?php endif; ?>
    </td>
</tr>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/dispatch/partials/desk-job-row.blade.php ENDPATH**/ ?>