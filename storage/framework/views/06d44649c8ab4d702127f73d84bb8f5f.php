<?php
    $enumLabel = fn ($case) => str_replace('_', ' ', ucfirst($case->value));
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('New WhatsApp message'),'breadcrumbs' => [['label' => __('WhatsApp'), 'url' => route('admin.communications.whatsapp.inbox')], ['label' => __('New message')]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php echo $__env->make('admin.communications.whatsapp.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if (isset($component)) { $__componentOriginalcb19cb35a534439097b02b8af91726ee = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcb19cb35a534439097b02b8af91726ee = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.page-header','data' => ['title' => __('New WhatsApp message'),'description' => __('Pick a person by category, filter the list, search, then send.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('New WhatsApp message')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Pick a person by category, filter the list, search, then send.'))]); ?>
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

    <form
        method="POST"
        action="<?php echo e(route('admin.communications.whatsapp.conversations.store')); ?>"
        class="erp-card max-w-3xl space-y-4"
        x-data="whatsappComposeForm(<?php echo \Illuminate\Support\Js::from([
            'contactType' => old('contact_type', $contactType),
            'selectedId' => old('contact_id', $selectedId),
            'phone' => old('phone_number', $defaultPhone),
            'pickerOptions' => $pickerOptions,
            'filters' => [
                'branch_id' => (string) old('filters.branch_id', ''),
                'customer_type' => (string) old('filters.customer_type', ''),
                'status' => (string) old('filters.status', ''),
                'has_outstanding' => (string) old('filters.has_outstanding', ''),
                'department_id' => (string) old('filters.department_id', ''),
                'employment_status' => (string) old('filters.employment_status', ''),
                'vendor_type' => (string) old('filters.vendor_type', ''),
            ],
        ])->toHtml() ?>)"
    >
        <?php echo csrf_field(); ?>

        <input type="hidden" name="contact_type" :value="contactType">
        <input type="hidden" name="contact_id" :value="selectedId || ''">

        <div>
            <label class="erp-label"><?php echo e(__('Who are you messaging?')); ?></label>
            <div class="mt-1 flex flex-wrap gap-1">
                <?php $__currentLoopData = [
                    'customers' => __('Customers'),
                    'leads' => __('Leads'),
                    'employees' => __('Employees'),
                    'suppliers' => __('Suppliers'),
                    'phone' => __('Phone only'),
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="contactType === '<?php echo e($value); ?>' ? 'bg-erp-accent text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        @click="setContactType('<?php echo e($value); ?>')"
                    ><?php echo e($label); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2" x-show="contactType !== 'phone'" x-cloak>
            <div class="space-y-2 rounded-lg border border-erp-border p-3 sm:col-span-2">
                <p class="text-xs font-semibold text-slate-500"><?php echo e(__('Filters')); ?></p>
                <div class="grid grid-cols-2 gap-2 lg:grid-cols-3">
                    <div x-show="['customers', 'leads'].includes(contactType)" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Branch')); ?></label>
                        <select class="erp-input w-full" x-model="filters.branch_id" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div x-show="contactType === 'customers'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Customer type')); ?></label>
                        <select class="erp-input w-full" x-model="filters.customer_type" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = \App\Enums\CustomerType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type->value); ?>"><?php echo e($enumLabel($type)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div x-show="contactType === 'customers'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Customer status')); ?></label>
                        <select class="erp-input w-full" x-model="filters.status" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = \App\Enums\CustomerStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div x-show="contactType === 'customers'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Outstanding')); ?></label>
                        <select class="erp-input w-full" x-model="filters.has_outstanding" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('Any')); ?></option>
                            <option value="1"><?php echo e(__('Has outstanding')); ?></option>
                        </select>
                    </div>

                    <div x-show="contactType === 'leads'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Lead status')); ?></label>
                        <select class="erp-input w-full" x-model="filters.status" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = \App\Enums\LeadStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div x-show="contactType === 'employees'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Department')); ?></label>
                        <select class="erp-input w-full" x-model="filters.department_id" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div x-show="contactType === 'employees'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Employment status')); ?></label>
                        <select class="erp-input w-full" x-model="filters.employment_status" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = \App\Enums\EmploymentStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div x-show="contactType === 'suppliers'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Supplier type')); ?></label>
                        <select class="erp-input w-full" x-model="filters.vendor_type" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = \App\Enums\VendorType::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type->value); ?>"><?php echo e($enumLabel($type)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div x-show="contactType === 'suppliers'" x-cloak>
                        <label class="erp-label text-xs"><?php echo e(__('Supplier status')); ?></label>
                        <select class="erp-input w-full" x-model="filters.status" @change="onFiltersChanged()">
                            <option value=""><?php echo e(__('All')); ?></option>
                            <?php $__currentLoopData = \App\Enums\VendorStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($status->value); ?>"><?php echo e($enumLabel($status)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="space-y-2 rounded-lg border border-erp-border p-3 sm:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-500"><?php echo e(__('Search & pick')); ?></p>
                    <p class="text-xs text-slate-500">
                        <span x-text="visibleContacts.length"></span> <?php echo e(__('shown')); ?>

                    </p>
                </div>
                <input
                    type="search"
                    class="erp-input w-full"
                    placeholder="<?php echo e(__('Type a name or phone…')); ?>"
                    x-model="contactSearch"
                    autocomplete="off"
                >
                <div class="max-h-52 overflow-y-auto rounded-md border border-erp-border divide-y divide-erp-border bg-white">
                    <template x-for="person in visibleContacts" :key="contactType + '-' + person.id">
                        <button
                            type="button"
                            class="flex w-full items-start gap-2 px-3 py-2 text-left text-sm hover:bg-slate-50"
                            :class="String(selectedId) === String(person.id) ? 'bg-emerald-50' : ''"
                            @click="selectContact(person)"
                        >
                            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-erp-border"
                                  :class="String(selectedId) === String(person.id) ? 'border-erp-accent bg-erp-accent' : ''">
                                <span class="h-1.5 w-1.5 rounded-full bg-white" x-show="String(selectedId) === String(person.id)"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-medium text-erp-primary" x-text="person.label"></span>
                                <span class="block font-mono text-xs text-slate-500" x-text="person.phone"></span>
                            </span>
                        </button>
                    </template>
                    <p x-show="visibleContacts.length === 0" class="px-3 py-4 text-center text-xs text-slate-500">
                        <?php echo e(__('No matches — adjust filters or search.')); ?>

                    </p>
                </div>
                <?php $__errorArgs = ['contact_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div>
            <label class="erp-label"><?php echo e(__('WhatsApp phone')); ?></label>
            <input
                type="text"
                name="phone_number"
                class="erp-input w-full"
                x-model="phone"
                placeholder="2547…"
                :required="contactType === 'phone'"
            >
            <p class="mt-1 text-xs text-slate-500" x-show="contactType === 'phone'" x-cloak>
                <?php echo e(__('Enter any number in international format when possible.')); ?>

            </p>
            <p class="mt-1 text-xs text-slate-500" x-show="contactType !== 'phone'" x-cloak>
                <?php echo e(__('Filled from the selected person — you can still edit it.')); ?>

            </p>
            <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <?php if($templates->isNotEmpty()): ?>
            <div>
                <label class="erp-label"><?php echo e(__('Template (optional)')); ?></label>
                <select name="whatsapp_template_id" class="erp-input w-full">
                    <option value=""><?php echo e(__('Free-form message')); ?></option>
                    <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($tpl->id); ?>" <?php if((int) old('whatsapp_template_id') === $tpl->id): echo 'selected'; endif; ?>>
                            <?php echo e($tpl->communicationTemplate?->name ?? __('Template #:id', ['id' => $tpl->id])); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        <?php endif; ?>

        <div>
            <label class="erp-label"><?php echo e(__('Message')); ?></label>
            <textarea
                name="body"
                rows="4"
                class="erp-input w-full"
                placeholder="<?php echo e(__('Type your WhatsApp message…')); ?>"
            ><?php echo e(old('body')); ?></textarea>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Required unless you choose a template.')); ?></p>
            <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="erp-btn erp-btn--primary"><?php echo e(__('Send')); ?></button>
            <a href="<?php echo e(route('admin.communications.whatsapp.inbox')); ?>" class="erp-btn erp-btn--secondary" data-turbo-frame="erp-main">
                <?php echo e(__('Cancel')); ?>

            </a>
        </div>
    </form>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/communications/whatsapp/conversations/create.blade.php ENDPATH**/ ?>