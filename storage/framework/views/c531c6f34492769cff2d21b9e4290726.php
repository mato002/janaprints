<?php
    $statusColors = [
        'requested' => 'bg-slate-100 text-slate-700',
        'in_design' => 'bg-blue-100 text-blue-700',
        'submitted' => 'bg-indigo-100 text-indigo-700',
        'approved' => 'bg-emerald-100 text-emerald-700',
        'revision_requested' => 'bg-amber-100 text-amber-800',
        'rejected' => 'bg-rose-100 text-rose-700',
    ];
    $availableRows = $availableRows ?? [];
    $myRows = $rows ?? [];
?>

<section class="designer-desk-queue space-y-3" aria-label="<?php echo e(__("Today's queue")); ?>">
    <?php if(count($availableRows) > 0): ?>
        <div class="rounded-xl border border-amber-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-2 border-b border-amber-100 bg-amber-50/60 px-3 py-2.5">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Available to claim')); ?></h2>
                    <p class="text-[11px] text-slate-500"><?php echo e(__('Claim first — only one designer can work a job.')); ?></p>
                </div>
                <span class="text-[11px] font-semibold tabular-nums text-amber-800"><?php echo e(count($availableRows)); ?></span>
            </div>
            <ul class="max-h-[28vh] divide-y divide-slate-100 overflow-y-auto">
                <?php $__currentLoopData = $availableRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li
                        x-show="rowVisible($el)"
                        data-filter-available="1"
                        data-filter-mine="0"
                        data-urgency-due-today="<?php echo e($row['is_due_today'] ? '1' : '0'); ?>"
                        data-urgency-overdue="<?php echo e($row['is_late'] ? '1' : '0'); ?>"
                        data-filter-working="0"
                        data-filter-review="0"
                        data-filter-late="<?php echo e($row['is_late'] ? '1' : '0'); ?>"
                        data-filter-high="<?php echo e(($row['is_high'] ?? false) ? '1' : '0'); ?>"
                        data-filter-today="<?php echo e(($row['is_due_today'] || $row['is_late']) ? '1' : '0'); ?>"
                    >
                        <button
                            type="button"
                            class="designer-desk-queue-card w-full px-3 py-3 text-left transition hover:bg-amber-50/40"
                            :class="{ 'bg-erp-accent/5 ring-1 ring-inset ring-erp-accent/30': selectedKey === <?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?> }"
                            @click="selectRequest(<?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?>)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900"><?php echo e($row['title']); ?></p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500"><?php echo e($row['customer'] ?? '—'); ?> · <span class="font-mono"><?php echo e($row['request_number']); ?></span></p>
                                </div>
                                <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-900"><?php echo e(__('Unclaimed')); ?></span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-semibold', 'text-rose-700' => $row['is_late'], 'text-amber-700' => $row['is_due_today'] && ! $row['is_late']]); ?>"><?php echo e(__('Due')); ?> <?php echo e($row['due_date']); ?></span>
                            </div>
                        </button>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="rounded-xl border border-erp-border bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2.5">
            <div>
                <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('My queue')); ?></h2>
                <p class="text-[11px] text-slate-500"><?php echo e(__('Jobs claimed by you — upload PDF, then mark complete.')); ?></p>
            </div>
            <span class="text-[11px] font-semibold tabular-nums text-slate-500"><?php echo e(count($myRows)); ?></span>
        </div>

        <?php if(count($myRows) === 0): ?>
            <div class="px-4 py-8 text-center">
                <?php if(count($availableRows) > 0): ?>
                    <p class="text-sm font-semibold text-slate-800"><?php echo e(__('Nothing claimed yet')); ?></p>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Pick a job from Available to claim and start working.')); ?></p>
                <?php else: ?>
                    <p class="text-sm font-semibold text-slate-800"><?php echo e(__('You\'re all caught up')); ?></p>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('No jobs assigned. The next artwork request will appear automatically.')); ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <ul class="max-h-[42vh] divide-y divide-slate-100 overflow-y-auto">
                <?php $__currentLoopData = $myRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li
                        x-show="rowVisible($el)"
                        data-filter-available="0"
                        data-filter-mine="1"
                        data-urgency-due-today="<?php echo e($row['is_due_today'] ? '1' : '0'); ?>"
                        data-urgency-overdue="<?php echo e($row['is_late'] ? '1' : '0'); ?>"
                        data-urgency-waiting="<?php echo e($row['is_waiting'] ? '1' : '0'); ?>"
                        data-urgency-new="<?php echo e($row['status'] === 'requested' ? '1' : '0'); ?>"
                        data-filter-working="<?php echo e(($row['is_working'] ?? false) ? '1' : '0'); ?>"
                        data-filter-review="<?php echo e(($row['is_review'] ?? false) ? '1' : '0'); ?>"
                        data-filter-late="<?php echo e($row['is_late'] ? '1' : '0'); ?>"
                        data-filter-high="<?php echo e(($row['is_high'] ?? false) ? '1' : '0'); ?>"
                        data-filter-today="<?php echo e(($row['is_due_today'] || $row['is_late']) ? '1' : '0'); ?>"
                    >
                        <button
                            type="button"
                            class="designer-desk-queue-card w-full px-3 py-3 text-left transition hover:bg-slate-50"
                            :class="{ 'bg-erp-accent/5 ring-1 ring-inset ring-erp-accent/30': selectedKey === <?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?> }"
                            @click="selectRequest(<?php echo \Illuminate\Support\Js::from($row['key'])->toHtml() ?>)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900"><?php echo e($row['title']); ?></p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500"><?php echo e($row['customer'] ?? '—'); ?> · <span class="font-mono"><?php echo e($row['request_number']); ?></span></p>
                                </div>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold',
                                    $statusColors[$row['status']] ?? 'bg-slate-100 text-slate-700',
                                ]); ?>"><?php echo e($row['status_label']); ?></span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'font-semibold',
                                    'text-rose-700' => $row['is_late'],
                                    'text-amber-700' => $row['is_due_today'] && ! $row['is_late'],
                                    'text-slate-500' => ! $row['is_late'] && ! $row['is_due_today'],
                                ]); ?>"><?php echo e(__('Due')); ?> <?php echo e($row['due_date']); ?></span>
                                <span class="text-slate-400">·</span>
                                <span class="text-slate-500"><?php echo e($row['version_label'] ?? $row['version']); ?></span>
                                <?php if(! empty($row['designer'])): ?>
                                    <span class="text-slate-400">·</span>
                                    <span class="font-medium text-slate-600"><?php echo e($row['designer']); ?></span>
                                <?php endif; ?>
                            </div>
                        </button>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

            <?php if($requests->hasPages()): ?>
                <div class="border-t border-erp-border px-3 py-2"><?php echo e($requests->links()); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/partials/queue-cards.blade.php ENDPATH**/ ?>