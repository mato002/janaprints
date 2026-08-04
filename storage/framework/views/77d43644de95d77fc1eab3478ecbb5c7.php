<?php
    $filters = old('recipient_filters', $campaign?->recipient_filters ?? []);
    $enumLabel = fn ($case) => str_replace('_', ' ', ucfirst($case->value));
?>

<div
    class="rounded-lg border border-erp-border p-3 space-y-2"
    x-show="['customers', 'dynamic', 'leads', 'employees', 'suppliers'].includes(recipientSource)"
    x-cloak
>
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-xs font-semibold text-slate-500"><?php echo e(__('Dynamic filters')); ?></p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'dynamic'">
                <?php echo e(__('Audience is built only from these filters (hand-picked names are ignored).')); ?>

            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'customers'">
                <?php echo e(__('Applied when no people are hand-picked on the left.')); ?>

            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'leads'">
                <?php echo e(__('Narrow which leads receive this campaign.')); ?>

            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'employees'">
                <?php echo e(__('Narrow which employees receive this campaign.')); ?>

            </p>
            <p class="text-xs text-slate-500" x-show="recipientSource === 'suppliers'">
                <?php echo e(__('Narrow which suppliers receive this campaign.')); ?>

            </p>
        </div>
        <button type="button" class="erp-btn erp-btn--ghost erp-btn--sm" @click="estimateAudience()" :disabled="estimatingAudience">
            <span x-show="!estimatingAudience"><?php echo e(__('Estimate audience')); ?></span>
            <span x-show="estimatingAudience" x-cloak><?php echo e(__('Counting…')); ?></span>
        </button>
    </div>

    <p class="rounded bg-slate-50 px-2 py-1.5 text-xs text-slate-600" x-show="audienceEstimate !== null" x-cloak>
        <span class="font-semibold text-erp-primary" x-text="audienceEstimate"></span>
        <?php echo e(__('people match the current source and filters.')); ?>

    </p>

    <div class="grid grid-cols-2 gap-2">
        <div x-show="['customers', 'dynamic', 'leads'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Branch')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.branch_id"
                :name="['customers', 'dynamic', 'leads'].includes(recipientSource) ? 'recipient_filters[branch_id]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div x-show="['customers', 'dynamic'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Customer type')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.customer_type"
                :name="['customers', 'dynamic'].includes(recipientSource) ? 'recipient_filters[customer_type]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = \App\Enums\CustomerType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->value); ?>"><?php echo e($enumLabel($type)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div x-show="['customers', 'dynamic'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Customer status')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.status"
                :name="['customers', 'dynamic'].includes(recipientSource) ? 'recipient_filters[status]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = \App\Enums\CustomerStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div x-show="['customers', 'dynamic'].includes(recipientSource)" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Outstanding balance')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.has_outstanding"
                :name="['customers', 'dynamic'].includes(recipientSource) ? 'recipient_filters[has_outstanding]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('Any')); ?></option>
                <option value="1"><?php echo e(__('Has outstanding')); ?></option>
            </select>
        </div>

        <div x-show="recipientSource === 'leads'" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Lead status')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.status"
                :name="recipientSource === 'leads' ? 'recipient_filters[status]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = \App\Enums\LeadStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div x-show="recipientSource === 'employees'" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Department')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.department_id"
                :name="recipientSource === 'employees' ? 'recipient_filters[department_id]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div x-show="recipientSource === 'employees'" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Employment status')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.employment_status"
                :name="recipientSource === 'employees' ? 'recipient_filters[employment_status]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = \App\Enums\EmploymentStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div x-show="recipientSource === 'suppliers'" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Supplier type')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.vendor_type"
                :name="recipientSource === 'suppliers' ? 'recipient_filters[vendor_type]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = \App\Enums\VendorType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->value); ?>"><?php echo e($enumLabel($type)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div x-show="recipientSource === 'suppliers'" x-cloak>
            <label class="erp-label text-xs"><?php echo e(__('Supplier status')); ?></label>
            <select
                class="erp-input w-full"
                x-model="filters.status"
                :name="recipientSource === 'suppliers' ? 'recipient_filters[status]' : null"
                @change="onFiltersChanged()"
            >
                <option value=""><?php echo e(__('All')); ?></option>
                <?php $__currentLoopData = \App\Enums\VendorStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\sms\campaigns\_filters.blade.php ENDPATH**/ ?>