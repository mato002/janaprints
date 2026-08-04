<?php if(count($filters ?? []) > 0): ?>
    <div class="mb-3 flex flex-wrap items-center gap-1.5" role="toolbar" aria-label="<?php echo e(__('Queue filters')); ?>">
        <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button
                type="button"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold transition',
                ]); ?>"
                :class="(activeFilter === <?php echo \Illuminate\Support\Js::from($filter['key'])->toHtml() ?> || (<?php echo \Illuminate\Support\Js::from($filter['key'])->toHtml() ?> === 'all' && !activeFilter))
                    ? 'border-erp-accent bg-erp-accent text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                @click="setFilter(<?php echo \Illuminate\Support\Js::from($filter['key'])->toHtml() ?>)"
            ><?php echo e($filter['label']); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\artwork\desk\partials\queue-filters.blade.php ENDPATH**/ ?>