<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('communications.email.view')): ?>
    <div
        class="crm-360__tab-stack"
        x-data="{
            drawerOpen: false,
            loading: false,
            detail: null,
            async openDrawer(messageId) {
                this.drawerOpen = true;
                this.loading = true;
                this.detail = null;
                try {
                    const response = await fetch(`<?php echo e(url('admin/communications/email/messages')); ?>/${messageId}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (response.ok) {
                        const data = await response.json();
                        this.detail = data.message;
                    }
                } finally {
                    this.loading = false;
                }
            },
            closeDrawer() {
                this.drawerOpen = false;
                this.detail = null;
            },
        }"
    >
        <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['padding' => false,'class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['padding' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mb-4']); ?>
            <?php if (isset($component)) { $__componentOriginal9577df2686262fb25ceb19a81119823d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9577df2686262fb25ceb19a81119823d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.index-toolbar','data' => ['action' => route('admin.crm.customers.show', $customer),'resetUrl' => route('admin.crm.customers.show', $customer),'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.index-toolbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.show', $customer)),'reset-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.crm.customers.show', $customer)),'compact' => true]); ?>
                <input type="hidden" name="tab" value="communications">
                <?php if (isset($component)) { $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.filter-pill-select','data' => ['name' => 'comm_type','label' => __('Type'),'selected' => request('comm_type')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.filter-pill-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'comm_type','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Type')),'selected' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(request('comm_type'))]); ?>
                    <option value=""><?php echo e(__('All types')); ?></option>
                    <option value="quotations" <?php if(request('comm_type') === 'quotations'): echo 'selected'; endif; ?>><?php echo e(__('Quotations')); ?></option>
                    <option value="invoices" <?php if(request('comm_type') === 'invoices'): echo 'selected'; endif; ?>><?php echo e(__('Invoices')); ?></option>
                    <option value="receipts" <?php if(request('comm_type') === 'receipts'): echo 'selected'; endif; ?>><?php echo e(__('Receipts')); ?></option>
                    <option value="general" <?php if(request('comm_type') === 'general'): echo 'selected'; endif; ?>><?php echo e(__('General')); ?></option>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $attributes = $__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__attributesOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3)): ?>
<?php $component = $__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3; ?>
<?php unset($__componentOriginal8f4fa08e40799e29ae21fbd8366f95e3); ?>
<?php endif; ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $attributes = $__attributesOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__attributesOriginal9577df2686262fb25ceb19a81119823d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9577df2686262fb25ceb19a81119823d)): ?>
<?php $component = $__componentOriginal9577df2686262fb25ceb19a81119823d; ?>
<?php unset($__componentOriginal9577df2686262fb25ceb19a81119823d); ?>
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

        <div class="erp-card overflow-x-auto">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th><?php echo e(__('Subject')); ?></th>
                        <th><?php echo e(__('Type')); ?></th>
                        <th><?php echo e(__('Sender')); ?></th>
                        <th><?php echo e(__('Date')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $customerEmailMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(Str::limit($message['subject'], 50)); ?></td>
                            <td class="text-xs"><?php echo e($message['type_label']); ?></td>
                            <td class="text-xs"><?php echo e($message['sender'] ?? '—'); ?></td>
                            <td class="text-xs"><?php echo e($message['date_formatted'] ?? '—'); ?></td>
                            <td><span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase <?php echo e($message['status_badge']); ?>"><?php echo e($message['status_label']); ?></span></td>
                            <td class="text-right">
                                <button type="button" class="text-sm text-erp-accent" @click="openDrawer(<?php echo e($message['id']); ?>)"><?php echo e(__('View')); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="py-8 text-center text-slate-500"><?php echo e(__('No email communications for this customer yet.')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php echo $__env->make('admin.communications.email.partials.detail-drawer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php else: ?>
    <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'mail','title' => __('Email access required'),'description' => __('You do not have permission to view customer email history.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'mail','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email access required')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('You do not have permission to view customer email history.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\360\tab-communications.blade.php ENDPATH**/ ?>