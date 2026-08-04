<?php
    $typeLabel = ucfirst(str_replace('_', ' ', $activity->activity_type->value));
    $assigneeName = $activity->user?->name ?? __('Unassigned');
    $assigneeInitials = collect(preg_split('/\s+/', trim($assigneeName)) ?: [])
        ->filter()
        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
        ->take(2)
        ->join('');
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $activity->subject,'breadcrumbs' => [
        ['label' => __('Commercial')],
        ['label' => __('CRM')],
        ['label' => __('Activities'), 'url' => route('admin.commercial.activities.index')],
        ['label' => $activity->subject],
    ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="activity-show w-full min-w-0 space-y-4">
        <div class="activity-show__toolbar">
            <a
                href="<?php echo e(route('admin.commercial.activities.index')); ?>"
                class="activity-show__back"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"/>
                </svg>
                <?php echo e(__('Back to Activities')); ?>

            </a>

            <div class="activity-show__toolbar-actions">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $activity)): ?>
                    <a href="<?php echo e(route('admin.commercial.activities.edit', $activity)); ?>" class="erp-btn-secondary erp-btn--sm"><?php echo e(__('Edit')); ?></a>
                <?php endif; ?>
                <?php if($activity->customer): ?>
                    <?php if (isset($component)) { $__componentOriginal186b247d17dc6bc0966b3f703835eca3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal186b247d17dc6bc0966b3f703835eca3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.customer-360-action','data' => ['customer' => $activity->customer]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.customer-360-action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['customer' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activity->customer)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal186b247d17dc6bc0966b3f703835eca3)): ?>
<?php $attributes = $__attributesOriginal186b247d17dc6bc0966b3f703835eca3; ?>
<?php unset($__attributesOriginal186b247d17dc6bc0966b3f703835eca3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal186b247d17dc6bc0966b3f703835eca3)): ?>
<?php $component = $__componentOriginal186b247d17dc6bc0966b3f703835eca3; ?>
<?php unset($__componentOriginal186b247d17dc6bc0966b3f703835eca3); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <section class="activity-show__hero" aria-labelledby="activity-show-title">
            <?php echo $__env->make('admin.commercial.activities.partials.type-icon', ['type' => $activity->activity_type], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="activity-show__hero-body">
                <div class="activity-show__hero-badges">
                    <span class="activity-show__type-chip"><?php echo e($typeLabel); ?></span>
                    <?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $activity->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activity->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
                </div>

                <h1 id="activity-show-title" class="activity-show__title"><?php echo e($activity->subject); ?></h1>

                <div class="activity-show__hero-meta">
                    <time datetime="<?php echo e($activity->activity_at->toIso8601String()); ?>" class="activity-show__when">
                        <?php echo e($activity->activity_at->format('D, j M Y · H:i')); ?>

                    </time>
                    <span class="activity-show__meta-dot" aria-hidden="true">·</span>
                    <span class="activity-show__relative"><?php echo e($activity->activity_at->diffForHumans()); ?></span>
                </div>
            </div>

            <div class="activity-show__assignee" title="<?php echo e($assigneeName); ?>">
                <span class="activity-show__assignee-avatar" aria-hidden="true"><?php echo e($assigneeInitials ?: '?'); ?></span>
                <div class="activity-show__assignee-copy">
                    <span class="activity-show__assignee-label"><?php echo e(__('Assigned to')); ?></span>
                    <span class="activity-show__assignee-name"><?php echo e($assigneeName); ?></span>
                </div>
            </div>
        </section>

        <div class="activity-show__layout">
            <div class="activity-show__main">
                <section class="activity-show__panel">
                    <header class="activity-show__panel-head">
                        <h2 class="activity-show__panel-title"><?php echo e(__('Notes & description')); ?></h2>
                    </header>
                    <div class="activity-show__panel-body">
                        <?php if(filled($activity->description)): ?>
                            <p class="activity-show__notes"><?php echo e($activity->description); ?></p>
                        <?php else: ?>
                            <p class="activity-show__empty"><?php echo e(__('No notes were added for this activity.')); ?></p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <aside class="activity-show__aside">
                <?php if($activity->customer || $activity->lead): ?>
                    <section class="activity-show__panel">
                        <header class="activity-show__panel-head">
                            <h2 class="activity-show__panel-title"><?php echo e(__('Related records')); ?></h2>
                        </header>
                        <div class="activity-show__panel-body activity-show__links">
                            <?php if($activity->customer): ?>
                                <a
                                    href="<?php echo e(route('admin.crm.customers.show', $activity->customer)); ?>"
                                    class="activity-show__link-card"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >
                                    <span class="activity-show__link-icon activity-show__link-icon--customer" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    </span>
                                    <span class="activity-show__link-copy">
                                        <span class="activity-show__link-label"><?php echo e(__('Customer')); ?></span>
                                        <span class="activity-show__link-value"><?php echo e($activity->customer->company_name); ?></span>
                                    </span>
                                    <svg class="activity-show__link-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            <?php endif; ?>

                            <?php if($activity->lead): ?>
                                <a
                                    href="<?php echo e(route('admin.crm.leads.show', $activity->lead)); ?>"
                                    class="activity-show__link-card"
                                    data-turbo-frame="erp-main"
                                    data-turbo-action="advance"
                                >
                                    <span class="activity-show__link-icon activity-show__link-icon--lead" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </span>
                                    <span class="activity-show__link-copy">
                                        <span class="activity-show__link-label"><?php echo e(__('Lead')); ?></span>
                                        <span class="activity-show__link-value"><?php echo e($activity->lead->lead_name); ?></span>
                                    </span>
                                    <svg class="activity-show__link-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section class="activity-show__panel">
                    <header class="activity-show__panel-head">
                        <h2 class="activity-show__panel-title"><?php echo e(__('Activity details')); ?></h2>
                    </header>
                    <dl class="activity-show__details">
                        <div class="activity-show__detail">
                            <dt><?php echo e(__('Type')); ?></dt>
                            <dd><?php echo e($typeLabel); ?></dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt><?php echo e(__('Status')); ?></dt>
                            <dd><?php if (isset($component)) { $__componentOriginal6b1e38fda99422b6943f33aba545ca7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.enum-status-badge','data' => ['status' => $activity->status->value]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.enum-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activity->status->value)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $attributes = $__attributesOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__attributesOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b)): ?>
<?php $component = $__componentOriginal6b1e38fda99422b6943f33aba545ca7b; ?>
<?php unset($__componentOriginal6b1e38fda99422b6943f33aba545ca7b); ?>
<?php endif; ?></dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt><?php echo e(__('When')); ?></dt>
                            <dd><?php echo e($activity->activity_at->format('Y-m-d H:i')); ?></dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt><?php echo e(__('Assigned to')); ?></dt>
                            <dd><?php echo e($assigneeName); ?></dd>
                        </div>
                        <div class="activity-show__detail">
                            <dt><?php echo e(__('Logged')); ?></dt>
                            <dd><?php echo e($activity->created_at?->format('Y-m-d H:i') ?? '—'); ?></dd>
                        </div>
                        <?php if($activity->updated_at && ! $activity->updated_at->equalTo($activity->created_at)): ?>
                            <div class="activity-show__detail">
                                <dt><?php echo e(__('Last updated')); ?></dt>
                                <dd><?php echo e($activity->updated_at->format('Y-m-d H:i')); ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </section>

                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $activity)): ?>
                    <section class="activity-show__panel activity-show__panel--danger">
                        <header class="activity-show__panel-head">
                            <h2 class="activity-show__panel-title"><?php echo e(__('Remove activity')); ?></h2>
                        </header>
                        <div class="activity-show__panel-body">
                            <p class="activity-show__danger-copy"><?php echo e(__('This permanently removes the activity from customer and lead history.')); ?></p>
                            <form
                                method="POST"
                                action="<?php echo e(route('admin.commercial.activities.destroy', $activity)); ?>"
                                onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Delete this activity?'))->toHtml() ?>)"
                            >
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="erp-btn-secondary erp-btn--sm text-red-700 hover:border-red-200 hover:bg-red-50">
                                    <?php echo e(__('Delete activity')); ?>

                                </button>
                            </form>
                        </div>
                    </section>
                <?php endif; ?>
            </aside>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\activities\show.blade.php ENDPATH**/ ?>