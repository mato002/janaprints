<?php
    $pi = $workspace['printing_intelligence'] ?? [];
    $summary = $pi['summary'] ?? null;
    $supported = (bool) ($pi['supported'] ?? false);
?>

<?php if($supported || $summary): ?>
    <?php if (isset($component)) { $__componentOriginalb327e04d2aba66fca2df8a26a48e286d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb327e04d2aba66fca2df8a26a48e286d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.record-workspace.section','data' => ['id' => 'qr-360-printing-intelligence','title' => __('Printing Intelligence'),'tone' => 'work']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.record-workspace.section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'qr-360-printing-intelligence','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Printing Intelligence')),'tone' => 'work']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <div class="flex flex-wrap gap-2">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.analyze')): ?>
                    <?php if($supported): ?>
                        <form
                            method="POST"
                            class="inline"
                            :action="piSummary ? piRerunUrl : piRunUrl"
                            @submit.prevent="submitPiForm($event)"
                        >
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm" :disabled="piAnalysisLoading">
                                <span x-show="! piSummary"><?php echo e(__('Run Analysis')); ?></span>
                                <span x-show="piSummary" x-cloak><?php echo e(__('Re-run')); ?></span>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <button
                    type="button"
                    class="crm-360__btn crm-360__btn--outline crm-360__btn--sm"
                    x-show="piSummary"
                    x-cloak
                    @click="openPiModal()"
                >
                    <?php echo e(__('View Analysis')); ?>

                </button>
            </div>
         <?php $__env->endSlot(); ?>

        <div x-show="piSummary" x-cloak class="qr-360__pi-compact">
            <p class="text-sm text-slate-700">
                <span class="font-medium" x-text="piSummary?.analysis_status_label"></span>
                <template x-if="piSummary?.page_count != null">
                    <span>
                        <span class="text-slate-400"> · </span>
                        <span x-text="piSummary.page_count + ' <?php echo e(__('pages')); ?>'"></span>
                    </span>
                </template>
                <template x-if="piSummary?.dimensions">
                    <span>
                        <span class="text-slate-400"> · </span>
                        <span x-text="piSummary.dimensions"></span>
                    </span>
                </template>
            </p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Full results open in the analysis modal on this page.')); ?></p>
        </div>

        <?php if($supported && auth()->user()?->can('printing.artwork.analyze')): ?>
            <p x-show="! piSummary" class="text-sm text-slate-600">
                <?php echo e(__('Run Printing Intelligence analysis on the customer artwork without re-uploading the file.')); ?>

            </p>
        <?php elseif(auth()->user()?->can('printing.intelligence.view')): ?>
            <p x-show="! piSummary" class="text-sm text-slate-500"><?php echo e(__('No Printing Intelligence analysis has been run for this artwork yet.')); ?></p>
        <?php endif; ?>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\printing-intelligence-panel.blade.php ENDPATH**/ ?>