<div class="crm-360__tab-stack">
    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Conversion history')); ?></h2>
            <?php if($lead->customer_id && $lead->customer): ?>
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','href' => route('admin.crm.customers.show', $lead->customer),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.show', $lead->customer)),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('Open customer 360')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
            <?php elseif(auth()->user()?->can('convert', $lead)): ?>
                <form method="POST" action="<?php echo e(route('admin.crm.leads.convert', $lead)); ?>" class="inline"><?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'submit','variant' => 'primary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','size' => 'sm']); ?><?php echo e(__('Convert lead')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                </form>
            <?php endif; ?>
        </div>

        <ul class="crm-360__feed" role="list">
            <?php $__currentLoopData = $conversionHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="crm-360__feed-item">
                    <div class="crm-360__feed-head">
                        <?php if(! empty($entry['url'])): ?>
                            <a href="<?php echo e($entry['url']); ?>" class="crm-360__feed-title" data-turbo-frame="erp-main"><?php echo e($entry['event']); ?></a>
                        <?php else: ?>
                            <span class="crm-360__feed-title"><?php echo e($entry['event']); ?></span>
                        <?php endif; ?>
                        <time class="crm-360__feed-time"><?php echo e($entry['at']?->format('d M Y H:i')); ?></time>
                    </div>
                    <?php if($entry['detail']): ?>
                        <p class="crm-360__feed-meta"><?php echo e($entry['detail']); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </section>

    <?php if($lead->status === App\Enums\LeadStatus::Open): ?>
        <section class="crm-360__card">
            <h2 class="crm-360__card-title"><?php echo e(__('Next steps')); ?></h2>
            <ul class="crm-360__mini-list" role="list">
                <li><?php echo e(__('Log activities and schedule follow-ups to advance the opportunity')); ?></li>
                <li><?php echo e(__('Convert to customer when ready to quote')); ?></li>
                <li><?php echo e(__('Create quotations linked to this lead for full acquisition traceability')); ?></li>
            </ul>
        </section>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\tab-conversion.blade.php ENDPATH**/ ?>