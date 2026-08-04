<div class="crm-360__tab-stack">
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Crm\CustomerActivity::class)): ?>
        <section class="crm-360__card crm-360__card--form">
            <h2 class="crm-360__card-title"><?php echo e(__('Log activity')); ?></h2>
            <form method="POST" action="<?php echo e(route('admin.crm.leads.activities.store', $lead)); ?>" class="crm-360__form-grid">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="erp-label"><?php echo e(__('Type')); ?></label>
                    <select name="activity_type" class="erp-select w-full text-sm">
                        <?php $__currentLoopData = App\Enums\ActivityType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->value); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $t->value))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Subject')); ?></label>
                    <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['name' => 'subject','class' => 'w-full','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'subject','class' => 'w-full','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label"><?php echo e(__('Description')); ?></label>
                    <textarea name="description" class="erp-input w-full text-sm" rows="2"></textarea>
                </div>
                <div>
                    <label class="erp-label"><?php echo e(__('When')); ?></label>
                    <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['name' => 'activity_at','type' => 'datetime-local','class' => 'w-full','value' => ''.e(now()->format('Y-m-d\TH:i')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'activity_at','type' => 'datetime-local','class' => 'w-full','value' => ''.e(now()->format('Y-m-d\TH:i')).'','required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
                </div>
                <div class="sm:col-span-3">
                    <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['type' => 'submit','variant' => 'primary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'primary','size' => 'sm']); ?><?php echo e(__('Log activity')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Activity history')); ?></h2>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\Crm\CustomerActivity::class)): ?>
                <?php if (isset($component)) { $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.crm-btn','data' => ['variant' => 'outline','size' => 'sm','href' => route('admin.commercial.activities.index', ['lead_id' => $lead->id]),'dataTurboFrame' => 'erp-main']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.crm-btn'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'outline','size' => 'sm','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.commercial.activities.index', ['lead_id' => $lead->id])),'data-turbo-frame' => 'erp-main']); ?><?php echo e(__('All activities')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $attributes = $__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__attributesOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d)): ?>
<?php $component = $__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d; ?>
<?php unset($__componentOriginalf2d8f3251f16f5ac1869b336e1c7548d); ?>
<?php endif; ?>
            <?php endif; ?>
        </div>

        <?php
            $activityGroups = $lead->activities->sortByDesc('activity_at')->groupBy(fn ($a) => ucfirst(str_replace('_', ' ', $a->activity_type->value)));
        ?>

        <?php $__empty_1 = true; $__currentLoopData = $activityGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeLabel => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="crm-360__activity-group">
                <h3 class="crm-360__activity-type"><?php echo e($typeLabel); ?></h3>
                <ul class="crm-360__feed" role="list">
                    <?php $__currentLoopData = $group; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="crm-360__feed-item">
                            <div class="crm-360__feed-head">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view', $activity)): ?>
                                    <a href="<?php echo e(route('admin.commercial.activities.show', $activity)); ?>" class="crm-360__feed-title" data-turbo-frame="erp-main"><?php echo e($activity->subject); ?></a>
                                <?php else: ?>
                                    <span class="crm-360__feed-title"><?php echo e($activity->subject); ?></span>
                                <?php endif; ?>
                                <time class="crm-360__feed-time"><?php echo e($activity->activity_at?->diffForHumans()); ?></time>
                            </div>
                            <p class="crm-360__feed-meta"><?php echo e($activity->user?->name ?? __('System')); ?></p>
                            <?php if($activity->description): ?>
                                <p class="crm-360__feed-meta text-slate-500"><?php echo e($activity->description); ?></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="crm-360__empty-inline"><?php echo e(__('No activities logged yet')); ?></p>
        <?php endif; ?>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\tab-activities.blade.php ENDPATH**/ ?>