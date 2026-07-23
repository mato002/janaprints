<?php
    $timeline = $workspaceData['timeline'] ?? collect();
?>

<aside class="shared-inbox__ctx-panel flex h-full min-h-0 w-full flex-col">
    <div class="shared-inbox__ctx-head">
        <p class="shared-inbox__ctx-head-title"><?php echo e(__('Customer info')); ?></p>
        <button
            type="button"
            class="shared-inbox__ctx-close"
            @click="closeDrawer()"
            aria-label="<?php echo e(__('Close customer info')); ?>"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="shared-inbox__ctx-tabs" role="tablist">
        <?php $__currentLoopData = [
            'summary' => __('Customer'),
            'records' => __('Orders & quotes'),
            'files' => __('Files'),
            'notes' => __('Notes'),
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button
                type="button"
                role="tab"
                @click="ctxTab='<?php echo e($key); ?>'"
                class="shared-inbox__ctx-tab"
                :class="ctxTab==='<?php echo e($key); ?>' && 'shared-inbox__ctx-tab--active'"
            ><?php echo e($label); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <button type="button" role="tab" @click="ctxTab='manage'" class="shared-inbox__ctx-tab" :class="ctxTab==='manage' && 'shared-inbox__ctx-tab--active'"><?php echo e(__('Insights')); ?></button>
        <button type="button" role="tab" @click="ctxTab='timeline'" class="shared-inbox__ctx-tab" :class="ctxTab==='timeline' && 'shared-inbox__ctx-tab--active'"><?php echo e(__('Activity')); ?></button>
    </div>

    <div class="shared-inbox__ctx-body">
        <div x-show="ctxTab==='summary'" x-cloak>
            <?php echo $__env->make('admin.communications.inbox.workspace.tab-summary', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div x-show="ctxTab==='manage'" x-cloak>
            <?php echo $__env->make('admin.communications.inbox.workspace.tab-manage', [
                'kpis' => $workspaceData['kpis'],
                'slaDetail' => $workspaceData['sla_detail'],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div x-show="ctxTab==='records'" x-cloak>
            <?php echo $__env->make('admin.communications.inbox.workspace.tab-records', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div x-show="ctxTab==='files'" x-cloak>
            <?php echo $__env->make('admin.communications.inbox.workspace.attachments-hub', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div x-show="ctxTab==='timeline'" x-cloak>
            <?php echo $__env->make('admin.communications.inbox.workspace.tab-timeline', ['events' => $timeline], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div x-show="ctxTab==='notes'" x-cloak>
            <?php echo $__env->make('admin.communications.inbox.workspace.tab-notes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</aside>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\context-panel.blade.php ENDPATH**/ ?>