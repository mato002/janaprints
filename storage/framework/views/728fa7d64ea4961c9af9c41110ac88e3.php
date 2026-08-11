<?php
    use App\Support\Crm\CustomerArtworkTypeCatalog;
    use App\Enums\CustomerPrintSpecificationStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $artworkTypeCatalog = app(CustomerArtworkTypeCatalog::class);
    $storeUrl = route('admin.crm.print-specifications.quick-store');
    $continueUrl = WorkspaceEmbed::url(route('admin.sales.desk', [
        'customer' => $customer->getRouteKey(),
        'step' => 3,
        'specification' => '__SPEC__',
    ]));
?>

<div
    class="mt-4 space-y-4 rounded-lg border border-erp-border bg-slate-50/80 p-4"
    x-data="{
        storeUrl: <?php echo \Illuminate\Support\Js::from($storeUrl)->toHtml() ?>,
        continueUrl: <?php echo \Illuminate\Support\Js::from($continueUrl)->toHtml() ?>,
        customerId: <?php echo \Illuminate\Support\Js::from($customer->id)->toHtml() ?>,
        csrf: <?php echo \Illuminate\Support\Js::from(csrf_token())->toHtml() ?>,
        saving: false,
        error: '',
        async submit(form) {
            if (! form || this.saving) return;
            this.saving = true;
            this.error = '';
            try {
                const body = new FormData(form);
                if (! body.get('customer_id') && this.customerId) {
                    body.set('customer_id', String(this.customerId));
                }
                const response = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Erp-Lookup-Create': '1',
                    },
                    body,
                });
                const payload = await response.json().catch(() => ({}));
                if (! response.ok) {
                    const messages = payload.errors
                        ? Object.values(payload.errors).flat()
                        : [payload.message || 'Unable to save specification.'];
                    this.error = messages.join(' ');
                    return;
                }
                const specId = payload.value ?? payload.id ?? null;
                if (! specId) {
                    this.error = 'Specification saved but no id was returned.';
                    return;
                }
                window.location.href = this.continueUrl.replace('__SPEC__', encodeURIComponent(String(specId)));
            } catch (e) {
                this.error = 'Unable to save specification.';
            } finally {
                this.saving = false;
            }
        },
    }"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Create new specification')); ?></h3>
        <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800" x-on:click="$dispatch('desk-spec-mode', { mode: 'existing' })"><?php echo e(__('Cancel')); ?></button>
    </div>

    <p class="text-xs text-slate-600">
        <span class="font-medium text-slate-800"><?php echo e(__('Customer')); ?>:</span>
        <?php echo e($customer->company_name ?? $customer->name); ?>

    </p>
    <p class="text-xs text-slate-500"><?php echo e(__('Quantity and price are set on the next step for this order. Spec defaults can still be edited later in Customer 360.')); ?></p>

    <form class="space-y-3" x-on:submit.prevent="submit($el)" enctype="multipart/form-data">
        <input type="hidden" name="customer_id" value="<?php echo e($customer->id); ?>">

        <div>
            <label class="erp-label" for="desk-spec-name"><?php echo e(__('Specification name')); ?></label>
            <input id="desk-spec-name" type="text" name="name" class="erp-input w-full" maxlength="255" placeholder="<?php echo e(__('e.g. Fortress Receipt Book')); ?>" required>
        </div>

        <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'inventory_item_id','label' => __('Product / inventory item'),'options' => $inventoryItemOptions ?? [],'value' => null,'required' => true,'createRoute' => 'admin.inventory.items.quick-create','refreshRoute' => 'admin.lookups.items','permission' => 'catalogue.create','modalTitle' => __('Create product'),'selectClass' => 'erp-input w-full','emptyOption' => true,'placeholder' => __('Select product')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inventory_item_id','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Product / inventory item')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($inventoryItemOptions ?? []),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(null),'required' => true,'create-route' => 'admin.inventory.items.quick-create','refresh-route' => 'admin.lookups.items','permission' => 'catalogue.create','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create product')),'select-class' => 'erp-input w-full','empty-option' => true,'placeholder' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Select product'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>

        <input type="hidden" name="default_quantity" value="1">

        <div>
            <label class="erp-label" for="desk-spec-status"><?php echo e(__('Status')); ?></label>
            <select id="desk-spec-status" name="status" class="erp-input w-full" required>
                <?php $__currentLoopData = CustomerPrintSpecificationStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status->value); ?>" <?php if($status === CustomerPrintSpecificationStatus::Active): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Use Active so the specification is available for orders immediately.')); ?></p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <?php if (isset($component)) { $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.lookup-select','data' => ['name' => 'artwork_type','label' => __('Artwork type'),'options' => $artworkTypeCatalog->optionsForCompany((int) $customer->company_id),'value' => $artworkTypeCatalog->defaultCode(),'createRoute' => 'admin.crm.artwork-types.quick-create','refreshRoute' => 'admin.lookups.artwork_types','permission' => 'crm.customers.edit','modalTitle' => __('Create artwork type'),'selectClass' => 'erp-input w-full','emptyOption' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.lookup-select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'artwork_type','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Artwork type')),'options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artworkTypeCatalog->optionsForCompany((int) $customer->company_id)),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($artworkTypeCatalog->defaultCode()),'create-route' => 'admin.crm.artwork-types.quick-create','refresh-route' => 'admin.lookups.artwork_types','permission' => 'crm.customers.edit','modal-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Create artwork type')),'select-class' => 'erp-input w-full','empty-option' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $attributes = $__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__attributesOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3)): ?>
<?php $component = $__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3; ?>
<?php unset($__componentOriginald632580a64ffc7ae2a9fdfd16806b8a3); ?>
<?php endif; ?>
            <div>
                <label class="erp-label" for="desk-spec-artwork-file"><?php echo e(__('Initial artwork file')); ?></label>
                <input id="desk-spec-artwork-file" type="file" name="artwork_file" class="erp-input w-full" accept=".jpg,.jpeg,.png,.webp,.pdf">
            </div>
        </div>

        <p class="text-xs text-rose-600" x-show="error" x-text="error" style="display: none"></p>

        <div class="flex flex-wrap justify-end gap-2">
            <button type="submit" class="erp-btn-primary text-sm" :disabled="saving">
                <span x-show="!saving"><?php echo e(__('Save specification')); ?></span>
                <span x-show="saving" style="display: none"><?php echo e(__('Saving…')); ?></span>
            </button>
        </div>
    </form>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/inline-spec-form.blade.php ENDPATH**/ ?>