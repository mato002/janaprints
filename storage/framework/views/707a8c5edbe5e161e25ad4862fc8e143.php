<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => $application->candidate->full_name] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => $application->candidate->full_name,'description' => $application->reference]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($application->candidate->full_name),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($application->reference)]); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <span class="erp-badge bg-slate-100 text-slate-700"><?php echo e($application->stage->label()); ?></span>
            <?php if($application->employee): ?>
                <a href="<?php echo e(route('admin.hr.employees.show', $application->employee)); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('View Employee')); ?></a>
            <?php endif; ?>
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $attributes = $__attributesOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__attributesOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcb19cb35a534439097b02b8af91726ee)): ?>
<?php $component = $__componentOriginalcb19cb35a534439097b02b8af91726ee; ?>
<?php unset($__componentOriginalcb19cb35a534439097b02b8af91726ee); ?>
<?php endif; ?>

<div class="mb-6 grid gap-4 md:grid-cols-3">
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Vacancy'),'value' => $application->vacancy->title,'icon' => 'briefcase']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Vacancy')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($application->vacancy->title),'icon' => 'briefcase']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Email'),'value' => $application->candidate->email ?? '—','icon' => 'mail']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($application->candidate->email ?? '—'),'icon' => 'mail']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal6d3db93990d768743336ad0c9a75de7b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6d3db93990d768743336ad0c9a75de7b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.kpi-widget','data' => ['label' => __('Applied'),'value' => $application->applied_at->format('M j, Y'),'icon' => 'calendar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.kpi-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Applied')),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($application->applied_at->format('M j, Y')),'icon' => 'calendar']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $attributes = $__attributesOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__attributesOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6d3db93990d768743336ad0c9a75de7b)): ?>
<?php $component = $__componentOriginal6d3db93990d768743336ad0c9a75de7b; ?>
<?php unset($__componentOriginal6d3db93990d768743336ad0c9a75de7b); ?>
<?php endif; ?>
    </div>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $application)): ?>
        <div class="mb-6 flex flex-wrap gap-2">
            <form method="POST" action="<?php echo e(route('admin.hr.recruitment.applications.advance', $application)); ?>" class="flex items-center gap-2">
                <?php echo csrf_field(); ?>
                <select name="stage" class="erp-input text-xs">
                    <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($stage->value); ?>" <?php if($application->stage === $stage): echo 'selected'; endif; ?>><?php echo e($stage->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Move')); ?></button>
            </form>
            <?php if($application->stage !== \App\Enums\RecruitmentPipelineStage::Rejected): ?>
                <form method="POST" action="<?php echo e(route('admin.hr.recruitment.applications.reject', $application)); ?>"><?php echo csrf_field(); ?><button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Reject')); ?></button></form>
            <?php endif; ?>
            <?php if($application->stage === \App\Enums\RecruitmentPipelineStage::Accepted && ! $application->onboarding): ?>
                <form method="POST" action="<?php echo e(route('admin.hr.recruitment.onboarding.start', $application)); ?>"><?php echo csrf_field(); ?><button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Start Onboarding')); ?></button></form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-2">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $application)): ?>
            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => __('Interview Scheduling')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Interview Scheduling'))]); ?>
                <form method="POST" action="<?php echo e(route('admin.hr.recruitment.applications.interview', $application)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="erp-label" for="scheduled_at"><?php echo e(__('Date & Time')); ?></label>
                        <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="erp-label" for="location"><?php echo e(__('Location')); ?></label>
                        <input type="text" id="location" name="location" class="erp-input w-full">
                    </div>
                    <button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Schedule Interview')); ?></button>
                </form>
                <?php if($application->interviews->isNotEmpty()): ?>
                    <div class="mt-4 space-y-2 text-sm">
                        <?php $__currentLoopData = $application->interviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interview): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded border border-erp-border/60 p-2">
                                <p class="font-medium"><?php echo e($interview->scheduled_at->format('M j, Y H:i')); ?></p>
                                <p class="text-slate-500"><?php echo e($interview->status->label()); ?> · <?php echo e($interview->location ?? __('TBD')); ?></p>
                                <?php if($interview->feedback): ?>
                                    <p class="mt-1 text-xs"><?php echo e(__('Rating')); ?>: <?php echo e($interview->feedback->rating); ?>/5 — <?php echo e($interview->feedback->recommendation->label()); ?></p>
                                <?php elseif($interview->status === \App\Enums\InterviewScheduleStatus::Scheduled): ?>
                                    <form method="POST" action="<?php echo e(route('admin.hr.recruitment.applications.feedback', $application)); ?>" class="mt-2 space-y-2">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="interview_schedule_id" value="<?php echo e($interview->id); ?>">
                                        <input type="number" name="rating" min="1" max="5" class="erp-input w-20 text-xs" placeholder="<?php echo e(__('Rating')); ?>" required>
                                        <select name="recommendation" class="erp-input text-xs">
                                            <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($rec->value); ?>"><?php echo e($rec->label()); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <textarea name="feedback" rows="2" class="erp-input w-full text-xs" placeholder="<?php echo e(__('Feedback')); ?>"></textarea>
                                        <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Submit Feedback')); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>

            <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['title' => __('Offer Letter')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Offer Letter'))]); ?>
                <form method="POST" action="<?php echo e(route('admin.hr.recruitment.applications.offer', $application)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="erp-label" for="salary_offered"><?php echo e(__('Salary Offered')); ?></label>
                        <input type="number" step="0.01" id="salary_offered" name="salary_offered" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label" for="start_date"><?php echo e(__('Start Date')); ?></label>
                        <input type="date" id="start_date" name="start_date" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label" for="terms"><?php echo e(__('Terms')); ?></label>
                        <textarea id="terms" name="terms" rows="2" class="erp-input w-full"></textarea>
                    </div>
                    <button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Create Offer')); ?></button>
                </form>
                <?php $__currentLoopData = $application->offerLetters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mt-4 rounded border border-erp-border/60 p-3 text-sm">
                        <p class="font-medium"><?php echo e($offer->reference); ?> — <?php echo e($offer->status->label()); ?></p>
                        <p class="text-slate-500"><?php echo e($offer->salary_offered ? number_format($offer->salary_offered, 2) : '—'); ?> · <?php echo e($offer->start_date?->format('Y-m-d') ?? '—'); ?></p>
                        <div class="mt-2 flex gap-2">
                            <?php if($offer->status === \App\Enums\OfferLetterStatus::Draft): ?>
                                <form method="POST" action="<?php echo e(route('admin.hr.recruitment.offers.send', $offer)); ?>"><?php echo csrf_field(); ?><button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Send')); ?></button></form>
                            <?php endif; ?>
                            <?php if($offer->status === \App\Enums\OfferLetterStatus::Sent): ?>
                                <form method="POST" action="<?php echo e(route('admin.hr.recruitment.offers.accept', $offer)); ?>"><?php echo csrf_field(); ?><button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Accept')); ?></button></form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if($application->onboarding): ?>
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mt-6','title' => __('Onboarding')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mt-6','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Onboarding'))]); ?>
            <p class="text-sm"><?php echo e(__('Status')); ?>: <?php echo e($application->onboarding->status->label()); ?></p>
            <a href="<?php echo e(route('admin.hr.recruitment.onboarding.show', $application->onboarding)); ?>" class="erp-btn-secondary mt-3 inline-block text-xs"><?php echo e(__('View onboarding')); ?></a>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\hr\recruitment\applications\show.blade.php ENDPATH**/ ?>