<?php if (isset($component)) { $__componentOriginalb327e04d2aba66fca2df8a26a48e286d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb327e04d2aba66fca2df8a26a48e286d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.section','data' => ['title' => __('Activity timeline'),'class' => 'rw-section--timeline','tone' => 'work']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Activity timeline')),'class' => 'rw-section--timeline','tone' => 'work']); ?>
     <?php $__env->slot('actions', null, []); ?> 
        <button
            type="button"
            class="text-xs font-semibold text-slate-500 hover:text-slate-800"
            @click="timelineOpen = ! timelineOpen"
            :aria-expanded="timelineOpen"
        >
            <span x-text="timelineOpen ? <?php echo \Illuminate\Support\Js::from(__('Collapse'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from(__('Expand'))->toHtml() ?>"></span>
        </button>
     <?php $__env->endSlot(); ?>

    <ul class="crm-360__timeline" role="list" x-show="timelineOpen" x-cloak>
        <?php $__empty_1 = true; $__currentLoopData = $workspace['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="crm-360__timeline-item">
                <span class="crm-360__timeline-dot" aria-hidden="true"></span>
                <div class="crm-360__timeline-body">
                    <div class="crm-360__timeline-head">
                        <span class="crm-360__badge crm-360__badge--activity"><?php echo e($event['badge']); ?></span>
                        <time class="crm-360__timeline-date"><?php echo e($event['at']?->format('d M Y, H:i')); ?></time>
                    </div>
                    <span class="crm-360__timeline-title"><?php echo e($event['title']); ?></span>
                    <p class="crm-360__timeline-meta"><?php echo e($event['body']); ?> · <?php echo e($event['at']?->diffForHumans()); ?></p>
                </div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="crm-360__empty-inline"><?php echo e(__('No activity yet')); ?></li>
        <?php endif; ?>
    </ul>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb327e04d2aba66fca2df8a26a48e286d)): ?>
<?php $attributes = $__attributesOriginalb327e04d2aba66fca2df8a26a48e286d; ?>
<?php unset($__attributesOriginalb327e04d2aba66fca2df8a26a48e286d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb327e04d2aba66fca2df8a26a48e286d)): ?>
<?php $component = $__componentOriginalb327e04d2aba66fca2df8a26a48e286d; ?>
<?php unset($__componentOriginalb327e04d2aba66fca2df8a26a48e286d); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\timeline.blade.php ENDPATH**/ ?>