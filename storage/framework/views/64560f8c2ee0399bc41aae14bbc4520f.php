<section class="crm-360__kpi-strip" aria-label="<?php echo e(__('Lead KPIs')); ?>">
    <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $display = '—';
            $sub = $kpi['hint'] ?? null;
            if (($kpi['format'] ?? null) === 'money' && $kpi['value'] !== null) {
                $display = number_format((float) $kpi['value'], 2);
            } elseif (($kpi['format'] ?? null) === 'date' && $kpi['value']) {
                $display = $kpi['value']->format('d M Y');
                $sub = $kpi['value']->isPast() ? __('Past due') : __('Upcoming');
            } elseif ($kpi['value'] !== null && $kpi['value'] !== '') {
                $display = (string) $kpi['value'];
            } else {
                $sub = $sub ?? __('No data yet');
            }
            $priority = $kpi['priority'] ?? 'medium';
            $trend = $kpi['trend'] ?? null;
        ?>
        <article class="crm-360__kpi crm-360__kpi--<?php echo e($priority); ?>">
            <div class="crm-360__kpi-top">
                <span class="crm-360__kpi-icon crm-360__kpi-icon--<?php echo e($kpi['icon'] ?? 'default'); ?>" aria-hidden="true">
                    <?php switch($kpi['icon'] ?? 'default'):
                        case ('revenue'): ?>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php break; ?>
                        <?php case ('quote'): ?>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <?php break; ?>
                        <?php case ('chat'): ?>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <?php break; ?>
                        <?php case ('activity'): ?>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <?php break; ?>
                        <?php default: ?>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php endswitch; ?>
                </span>
                <?php if($trend === 'up'): ?>
                    <span class="crm-360__kpi-trend crm-360__kpi-trend--up" title="<?php echo e(__('Active')); ?>">↑</span>
                <?php elseif($trend === 'alert'): ?>
                    <span class="crm-360__kpi-trend crm-360__kpi-trend--alert" title="<?php echo e(__('Attention')); ?>">!</span>
                <?php endif; ?>
            </div>
            <span class="crm-360__kpi-label"><?php echo e($kpi['label']); ?></span>
            <span class="crm-360__kpi-value <?php echo e($display === '—' ? 'crm-360__kpi-value--empty' : ''); ?>"><?php echo e($display); ?></span>
            <?php if($sub): ?>
                <span class="crm-360__kpi-hint"><?php echo e($sub); ?></span>
            <?php endif; ?>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\kpi-strip.blade.php ENDPATH**/ ?>