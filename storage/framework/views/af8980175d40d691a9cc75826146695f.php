<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $stepLabels = [
        1 => __('Customer'),
        2 => __('Specification'),
        3 => __('Order'),
        4 => __('Artwork'),
        5 => __('Complete'),
    ];
    $deskFrame = WorkspaceEmbed::turboFrame();

    $createCustomerUrl = route('admin.crm.customers.create', ['from' => 'sales-desk']);
    $walkInComplete = ! empty($orderPresentation['released_to_queue']);
    $hasSpecs = count($printSpecifications) > 0;
    $defaultSpecMode = $hasSpecs ? 'existing' : 'new';
?>

<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Sales Desk'),'breadcrumbs' => $operatorMode
        ? [['label' => __('Sales Desk')]]
        : [
            ['label' => __('Commercial'), 'url' => $fullCommercialDeskUrl],
            ['label' => __('Sales Desk')],
        ]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="sales-desk-shell min-w-0 max-w-full"
        x-data="salesDeskSearch(<?php echo \Illuminate\Support\Js::from([
            'searchUrl' => $searchUrl,
            'deskUrl' => route('admin.sales.desk'),
        ])->toHtml() ?>)"
    >
        <?php echo $__env->make('admin.sales.desk.partials.desk-mode-nav', ['activeSalesView' => \App\Support\Sales\SalesDeskViews::DESK], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="mb-3 flex flex-col gap-2 rounded-lg border border-erp-accent/25 bg-erp-accent/5 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-erp-primary"><?php echo e(__('Sales desk')); ?></p>
                <p class="text-xs text-slate-600"><?php echo e(__('One guided walk-in — customer through order, without leaving this desk.')); ?></p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if (! ($operatorMode)): ?>
                    <a href="<?php echo e($fullCommercialDeskUrl); ?>" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main"><?php echo e(__('Full Commercial desk')); ?></a>
                <?php endif; ?>
                <a href="<?php echo e(WorkspaceEmbed::url(route('admin.sales.desk'))); ?>" class="erp-btn-secondary text-xs" data-turbo-frame="<?php echo e($deskFrame); ?>" data-turbo-action="advance"><?php echo e(__('Start another')); ?></a>
            </div>
        </div>

        <?php if(session('status')): ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" data-erp-flash-status>
                <?php echo e(session('status')); ?>

                <?php if(session('sales_desk_receipt_url')): ?>
                    <a href="<?php echo e(session('sales_desk_receipt_url')); ?>" class="ml-2 font-medium underline" data-erp-modal-open><?php echo e(__('View receipt')); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if(! empty($specificationNotice)): ?>
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"><?php echo e($specificationNotice); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" data-erp-flash-error>
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" data-erp-validation-errors>
                <ul class="list-disc pl-4">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php echo $__env->make('admin.sales.desk.partials.fast-actions', ['fastActions' => $fastActions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <nav class="mb-4 -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" aria-label="<?php echo e(__('Walk-in steps')); ?>">
            <?php $__currentLoopData = $stepLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $enabled = $id === 1
                        || ($id === 2 && $customer)
                        || ($id === 3 && $customer && ($specification || $hasSpecs))
                        || ($id === 4 && $order)
                        || ($id === 5 && $order && $walkInComplete);
                    $href = match (true) {
                        $id === 1 => route('admin.sales.desk', ['step' => 1]),
                        $id === 2 && $customer => route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2]),
                        $id === 3 && $customer => route('admin.sales.desk', array_filter([
                            'customer' => $customer->getRouteKey(),
                            'specification' => $specification?->id,
                            'step' => 3,
                        ])),
                        $id === 4 && $order => route('admin.sales.desk', [
                            'customer' => $customer?->getRouteKey() ?? $order->customer?->getRouteKey(),
                            'order' => $order->getRouteKey(),
                            'step' => 4,
                        ]),
                        $id === 5 && $order && $walkInComplete => route('admin.sales.desk', [
                            'customer' => $customer?->getRouteKey() ?? $order->customer?->getRouteKey(),
                            'order' => $order->getRouteKey(),
                            'step' => 5,
                        ]),
                        default => null,
                    };
                    $stepComplete = $id < $step || ($id === 5 && $walkInComplete);
                    $stepCurrent = $step === $id && ! ($id === 5 && $walkInComplete && $step > 5);
                    if ($id === 5 && $walkInComplete && $step === 5) {
                        $stepCurrent = true;
                        $stepComplete = true;
                    }
                ?>
                <?php if($enabled && $href): ?>
                    <a
                        href="<?php echo e(WorkspaceEmbed::url($href)); ?>"
                        data-turbo-frame="<?php echo e($deskFrame); ?>"
                        data-turbo-action="advance"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'shrink-0 rounded-full border px-3 py-1 text-xs font-medium transition',
                            'border-erp-accent bg-erp-accent text-white' => $stepCurrent && ! $stepComplete,
                            'border-emerald-300 bg-emerald-50 text-emerald-800' => $stepComplete,
                            'border-slate-200 bg-white text-slate-600' => ! $stepComplete && ! $stepCurrent,
                        ]); ?>"
                    ><?php echo e($stepComplete ? '✓ ' : ''); ?><?php echo e($label); ?></a>
                <?php else: ?>
                    <span class="shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-400"><?php echo e($label); ?></span>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-4">
                <?php if($step === 1): ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('1. Find or create customer')); ?></h2>
                            <div class="flex flex-wrap gap-2">
                                <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => $createCustomerUrl]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($createCustomerUrl)]); ?>
                                    <?php echo e(__('Create customer')); ?>

                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $attributes = $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $component = $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
                                <?php if($operatorMode && $customer && ($deskUrls['quotation'] ?? null)): ?>
                                    <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => $deskUrls['quotation'],'class' => 'erp-btn-secondary text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deskUrls['quotation']),'class' => 'erp-btn-secondary text-xs']); ?>
                                        <?php echo e(__('Quote first')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $attributes = $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $component = $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="relative" @click.outside="closeDropdown()">
                            <label class="erp-label" for="desk-customer-search"><?php echo e(__('Search existing')); ?></label>
                            <div class="relative">
                                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','class' => 'pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
                                <input
                                    id="desk-customer-search"
                                    type="text"
                                    class="erp-input w-full py-2 pl-9 pr-3"
                                    placeholder="<?php echo e(__('Customer, quote, order, job, phone…')); ?>"
                                    autocomplete="off"
                                    role="combobox"
                                    aria-autocomplete="list"
                                    aria-controls="desk-customer-search-list"
                                    :aria-expanded="open"
                                    x-model="query"
                                    @focus="openDropdown()"
                                    @click="openDropdown()"
                                    @input="onInput()"
                                    @keydown.escape.prevent="closeDropdown()"
                                >
                            </div>

                            <div
                                id="desk-customer-search-list"
                                role="listbox"
                                x-show="open"
                                x-cloak
                                class="absolute z-30 mt-1 max-h-64 w-full overflow-y-auto rounded-lg border border-erp-border bg-white shadow-lg"
                            >
                                <p x-show="loading" class="px-3 py-4 text-center text-sm text-slate-500"><?php echo e(__('Loading…')); ?></p>

                                <template x-if="! loading && results.length === 0">
                                    <p class="px-3 py-4 text-center text-sm text-slate-500">
                                        <span x-show="query.trim()"><?php echo e(__('No matches for your search.')); ?></span>
                                        <span x-show="! query.trim()"><?php echo e(__('No active customers yet.')); ?></span>
                                    </p>
                                </template>

                                <ul x-show="! loading && results.length" class="divide-y divide-slate-100">
                                    <template x-for="row in results" :key="`${row.kind}-${row.id}`">
                                        <li>
                                            <a
                                                role="option"
                                                class="flex w-full items-start justify-between gap-2 px-3 py-2.5 text-left text-sm transition hover:bg-slate-50 focus:bg-slate-50 focus:outline-none"
                                                :href="resultHref(row)"
                                                :data-erp-modal-open="row.modal ? '' : null"
                                                :data-turbo-frame="row.modal ? null : '_top'"
                                                @click="closeDropdown()"
                                            >
                                                <span class="min-w-0">
                                                    <span class="mb-0.5 inline-flex rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600" x-text="resultKindLabel(row)"></span>
                                                    <span class="block truncate font-medium text-slate-900" x-text="row.label"></span>
                                                    <span class="block truncate text-xs text-slate-500" x-text="row.meta || ''"></span>
                                                </span>
                                                <span class="shrink-0 text-xs font-medium text-erp-accent"><?php echo e(__('Open')); ?></span>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-slate-500"><?php echo e(__('Search customers, quotes, orders, or jobs—or create a new customer to start a walk-in.')); ?></p>
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

                <?php if($step === 2 && $customer): ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div
                            x-data="{
                                specMode: <?php echo \Illuminate\Support\Js::from($defaultSpecMode)->toHtml() ?>,
                                specQuery: '',
                                setMode(mode) {
                                    if (mode === 'existing' && ! <?php echo \Illuminate\Support\Js::from($hasSpecs)->toHtml() ?>) {
                                        this.specMode = 'new';
                                        return;
                                    }
                                    this.specMode = mode;
                                },
                            }"
                            x-on:desk-spec-mode.window="setMode($event.detail.mode || 'existing')"
                        >
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('2. Print specification')); ?></h2>
                                <p class="text-xs text-slate-600">
                                    <?php echo e(__('Customer')); ?>:
                                    <span class="font-medium text-slate-900"><?php echo e($customer->name); ?></span>
                                    <span class="ml-1 rounded-full border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800"><?php echo e(__('Locked')); ?></span>
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <?php if(($deskUrls['customer_360'] ?? null)): ?>
                                    <a href="<?php echo e($deskUrls['customer_360']); ?>" class="erp-btn-secondary text-xs" data-turbo-frame="erp-main"><?php echo e(__('View Customer 360')); ?></a>
                                <?php endif; ?>
                                <?php if($operatorMode && ($deskUrls['quotation'] ?? null)): ?>
                                    <?php if (isset($component)) { $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-modal-link','data' => ['href' => $deskUrls['quotation'],'class' => 'erp-btn-secondary text-xs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-modal-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deskUrls['quotation']),'class' => 'erp-btn-secondary text-xs']); ?>
                                        <?php echo e(__('Quote first')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $attributes = $__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__attributesOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c)): ?>
<?php $component = $__componentOriginal07cb24c7759400d4bcd39cc892e46f4c; ?>
<?php unset($__componentOriginal07cb24c7759400d4bcd39cc892e46f4c); ?>
<?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <fieldset class="mb-4">
                            <legend class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Print specification')); ?></legend>
                            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition"
                                    :class="specMode === 'existing' ? 'border-erp-accent bg-erp-accent/5 text-slate-900' : 'border-slate-200 bg-white text-slate-600'"
                                >
                                    <input
                                        type="radio"
                                        class="text-erp-accent"
                                        name="desk_spec_mode"
                                        value="existing"
                                        <?php if($defaultSpecMode === 'existing'): echo 'checked'; endif; ?>
                                        <?php if(! $hasSpecs): echo 'disabled'; endif; ?>
                                        @change="setMode('existing')"
                                    >
                                    <?php echo e(__('Use existing specification')); ?>

                                </label>
                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 text-sm transition"
                                    :class="specMode === 'new' ? 'border-erp-accent bg-erp-accent/5 text-slate-900' : 'border-slate-200 bg-white text-slate-600'"
                                >
                                    <input
                                        type="radio"
                                        class="text-erp-accent"
                                        name="desk_spec_mode"
                                        value="new"
                                        <?php if($defaultSpecMode === 'new'): echo 'checked'; endif; ?>
                                        @change="setMode('new')"
                                    >
                                    <?php echo e(__('Create new specification')); ?>

                                </label>
                            </div>
                            <p class="mt-2 text-xs text-slate-500" x-text="specMode === 'existing'
                                ? <?php echo \Illuminate\Support\Js::from(__('Pick a saved specification, then click Use to continue to the order.'))->toHtml() ?>
                                : <?php echo \Illuminate\Support\Js::from(__('Fill in the form below, save, and the walk-in continues automatically.'))->toHtml() ?>"></p>
                        </fieldset>

                        <div x-show="specMode === 'existing'" style="<?php echo e($defaultSpecMode === 'existing' ? '' : 'display: none'); ?>">
                            <?php if($hasSpecs): ?>
                                <div class="mb-3">
                                    <label class="erp-label" for="desk-spec-search"><?php echo e(__('Search specification')); ?></label>
                                    <input
                                        id="desk-spec-search"
                                        type="search"
                                        class="erp-input w-full"
                                        placeholder="<?php echo e(__('Name, code, or product…')); ?>"
                                        x-model="specQuery"
                                    >
                                </div>

                                <h3 class="mb-2 text-sm font-medium text-slate-800"><?php echo e(__('Recent & saved specifications')); ?></h3>
                                <div class="erp-table-scroll rounded-lg border border-erp-border">
                                    <table class="erp-table text-sm">
                                        <thead>
                                            <tr>
                                                <th><?php echo e(__('Code')); ?></th>
                                                <th><?php echo e(__('Name')); ?></th>
                                                <th><?php echo e(__('Product')); ?></th>
                                                <th><?php echo e(__('Artwork')); ?></th>
                                                <th><?php echo e(__('Default price')); ?></th>
                                                <th><?php echo e(__('Last used')); ?></th>
                                                <th class="erp-table-actions-col"><span class="sr-only"><?php echo e(__('Actions')); ?></span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $printSpecifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $artworkMissing = ($spec['artwork_required'] ?? false) && ! ($spec['has_active_artwork'] ?? false);
                                                    $searchBlob = strtolower(implode(' ', array_filter([
                                                        $spec['specification_code'] ?? '',
                                                        $spec['name'] ?? '',
                                                        $spec['product_name'] ?? '',
                                                    ])));
                                                    $lastUsed = $spec['last_used_at'] ?? null;
                                                    if ($lastUsed instanceof \Carbon\CarbonInterface) {
                                                        $lastUsedLabel = $lastUsed->format('d M Y');
                                                    } elseif (is_string($lastUsed) && $lastUsed !== '') {
                                                        $lastUsedLabel = \Illuminate\Support\Carbon::parse($lastUsed)->format('d M Y');
                                                    } else {
                                                        $lastUsedLabel = '—';
                                                    }
                                                ?>
                                                <tr
                                                    data-spec-search="<?php echo e(e($searchBlob)); ?>"
                                                    x-show="!specQuery.trim() || ($el.dataset.specSearch || '').includes(specQuery.trim().toLowerCase())"
                                                    class="<?php echo \Illuminate\Support\Arr::toCssClasses(['bg-erp-accent/5' => $specification && (int) $specification->id === (int) $spec['id']]); ?>"
                                                >
                                                    <td class="font-mono text-xs whitespace-nowrap"><?php echo e($spec['specification_code']); ?></td>
                                                    <td class="min-w-[8rem] font-medium"><?php echo e($spec['name']); ?></td>
                                                    <td class="min-w-[6rem]"><?php echo e($spec['product_name'] ?? '—'); ?></td>
                                                    <td class="min-w-[5rem] text-xs whitespace-nowrap">
                                                        <?php if($spec['has_active_artwork'] ?? false): ?>
                                                            <span class="inline-flex items-center gap-1 text-emerald-700">
                                                                <span>&#10003;</span> <?php echo e($spec['current_artwork_label']); ?>

                                                            </span>
                                                        <?php elseif($artworkMissing): ?>
                                                            <span class="inline-flex items-center gap-1 text-amber-700">
                                                                <span>!</span> <?php echo e(__('Required')); ?>

                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-slate-400"><?php echo e(__('N/A')); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="font-mono text-xs whitespace-nowrap"><?php echo e($spec['default_unit_price'] ?? '—'); ?></td>
                                                    <td class="text-xs whitespace-nowrap"><?php echo e($lastUsedLabel); ?></td>
                                                    <td class="erp-table-actions-col whitespace-nowrap">
                                                        <div class="flex items-center justify-end gap-1">
                                                            <?php if($artworkMissing): ?>
                                                                <a
                                                                    class="erp-btn-secondary text-xs py-1 px-2"
                                                                    href="<?php echo e(route('admin.crm.customers.print-specifications.edit', [$customer, $spec['id'], 'from' => 'sales-desk'])); ?>"
                                                                    data-erp-modal-open
                                                                    title="<?php echo e(__('Upload artwork first')); ?>"
                                                                ><?php echo e(__('Upload artwork')); ?></a>
                                                            <?php endif; ?>
                                                            <a
                                                                class="erp-btn-secondary text-xs py-1 px-2"
                                                                href="<?php echo e(WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'specification' => $spec['id'], 'step' => 3]))); ?>"
                                                                data-turbo-frame="<?php echo e($deskFrame); ?>"
                                                                data-turbo-action="advance"
                                                            ><?php echo e(__('Use')); ?></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-slate-600"><?php echo e(__('No saved specifications for this customer yet. Choose Create new specification above.')); ?></p>
                            <?php endif; ?>
                        </div>

                        <div x-show="specMode === 'new'" style="<?php echo e($defaultSpecMode === 'new' ? '' : 'display: none'); ?>">
                            <?php echo $__env->make('admin.sales.desk.partials.inline-spec-form', [
                                'customer' => $customer,
                                'inventoryItemOptions' => $inventoryItemOptions ?? [],
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                        </div>
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

                <?php if($step === 3 && $customer): ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('3. Order details')); ?></h2>
                                <p class="text-xs text-slate-600"><?php echo e(__('Enter quantity and price for this order. Delivery, priority, and billing come next — customer and specification stay locked.')); ?></p>
                            </div>
                        </div>

                        <?php if(! $specification && ! $hasSpecs): ?>
                            <p class="text-sm text-amber-800"><?php echo e(__('No active print specification for this customer yet. Create one on the Specification step.')); ?></p>
                            <a href="<?php echo e(WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2]))); ?>" class="erp-btn-primary mt-3 inline-flex text-sm" data-turbo-frame="<?php echo e($deskFrame); ?>" data-turbo-action="advance"><?php echo e(__('Go to specification')); ?></a>
                        <?php elseif(! $specification): ?>
                            <p class="text-sm text-slate-600"><?php echo e(__('Select a specification to continue. It will be locked for this order.')); ?></p>
                            <a href="<?php echo e(WorkspaceEmbed::url(route('admin.sales.desk', ['customer' => $customer->getRouteKey(), 'step' => 2]))); ?>" class="erp-btn-primary mt-3 inline-flex text-sm" data-turbo-frame="<?php echo e($deskFrame); ?>" data-turbo-action="advance"><?php echo e(__('Choose specification')); ?></a>
                        <?php else: ?>
                            <?php echo $__env->make('admin.sales.desk.partials.inline-order-form', [
                                'customer' => $customer,
                                'specification' => $specification,
                                'deskUrls' => $deskUrls,
                                'orderPriorities' => $orderPriorities,
                                'canSendToProduction' => $canSendToProduction,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                <?php endif; ?>

                <?php if($step === 4 && $order): ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                        <h2 class="mb-3 text-sm font-semibold text-slate-900"><?php echo e(__('4. Artwork & release')); ?></h2>

                        <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <p class="font-medium text-slate-900"><?php echo e($orderPresentation['order_number']); ?></p>
                            <p class="text-xs text-slate-600">
                                <?php echo e($orderPresentation['status_label']); ?>

                                <?php if($orderPresentation['job_card_number']): ?>
                                    · <?php echo e(__('Job')); ?> <?php echo e($orderPresentation['job_card_number']); ?>

                                <?php endif; ?>
                            </p>
                            <?php if($specification): ?>
                                <p class="mt-2 text-xs">
                                    <?php if($specification->activeArtworkVersion): ?>
                                        <span class="text-emerald-700">&#10003; <?php echo e(__('Artwork')); ?>: <?php echo e($specification->activeArtworkVersion->versionLabel()); ?> — <?php echo e($specification->activeArtworkVersion->artwork_name); ?></span>
                                    <?php else: ?>
                                        <span class="text-amber-700"><?php echo e(__('Artwork pending on specification')); ?></span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if($specification && ! $specification->activeArtworkVersion): ?>
                            <div class="mb-4 flex flex-wrap gap-2">
                                <a
                                    class="erp-btn-secondary text-xs"
                                    href="<?php echo e(route('admin.crm.customers.print-specifications.edit', [$customer ?? $order->customer, $specification, 'from' => 'sales-desk'])); ?>"
                                    data-erp-modal-open
                                ><?php echo e(__('Upload artwork')); ?></a>
                                <?php if($operatorMode && ($deskUrls['artwork_request'] ?? null)): ?>
                                    <a class="erp-btn-secondary text-xs" href="<?php echo e($deskUrls['artwork_request']); ?>" data-erp-modal-open><?php echo e(__('Send to designer')); ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($orderPresentation['can_release'] && ! empty($orderPresentation['readiness']['checks'])): ?>
                            <?php
                                $releaseDashboard = $walkInPanel['dashboard'] ?? [];
                                $releaseReady = (bool) ($orderPresentation['readiness']['ready'] ?? false);
                            ?>
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Release readiness')); ?></p>
                                    <?php if($releaseReady): ?>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-800"><?php echo e(__('Ready')); ?></span>
                                    <?php endif; ?>
                                </div>
                                <ul class="divide-y divide-slate-100 rounded-lg border border-erp-border bg-white text-sm">
                                    <?php $__currentLoopData = $releaseDashboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="px-3 py-2.5">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="font-medium text-slate-800"><?php echo e($row['label']); ?></span>
                                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                    'text-sm font-semibold',
                                                    'text-emerald-700' => $row['passed'] ?? false,
                                                    'text-amber-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') === 'warning',
                                                    'text-rose-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') !== 'warning',
                                                ]); ?>"><?php echo e(($row['passed'] ?? false) ? '✓' : '!'); ?></span>
                                            </div>
                                            <?php if(! ($row['passed'] ?? false) && ! empty($row['message'])): ?>
                                                <p class="mt-1 text-xs text-slate-600"><?php echo e($row['message']); ?></p>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <?php if($releaseReady): ?>
                                    <p class="mt-3 text-sm font-semibold uppercase tracking-wide text-emerald-800"><?php echo e(__('Ready for production')); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($orderPresentation['can_release']): ?>
                            <form
                                method="POST"
                                action="<?php echo e(route('admin.sales-orders.release-to-production', $order)); ?>"
                                class="mb-4"
                                data-erp-desk-form
                                data-turbo="false"
                                data-erp-desk-success-message="<?php echo e(__('Sales order sent to production queue.')); ?>"
                                data-erp-desk-submitting-message="<?php echo e(__('Submitting to production queue…')); ?>"
                            >
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="from" value="sales-desk">
                                <button type="submit" class="erp-btn-primary" <?php if(! ($orderPresentation['readiness']['ready'] ?? false)): echo 'disabled'; endif; ?>>
                                    <?php if(empty($orderPresentation['job_card_id'])): ?>
                                        <?php echo e(__('Release to production')); ?>

                                    <?php else: ?>
                                        <?php echo e(__('Submit to production queue')); ?>

                                    <?php endif; ?>
                                </button>
                            </form>
                            <?php if(empty($orderPresentation['readiness']['ready'] ?? false)): ?>
                                <p class="mb-4 text-sm text-amber-700"><?php echo e(__('Fix the items marked above before releasing to production.')); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($orderPresentation['job_card_id']): ?>
                            <?php echo $__env->make('admin.sales.desk.partials.production-handoff', ['orderPresentation' => $orderPresentation], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?>

                        <?php if($operatorMode): ?>
                            <?php echo $__env->make('admin.sales.desk.partials.order-actions', ['orderPresentation' => $orderPresentation], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?>

                        <div class="flex flex-wrap gap-2">
                            <a href="<?php echo e($orderPresentation['show_url']); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Open sales order')); ?></a>
                            <?php if($orderPresentation['job_url']): ?>
                                <a href="<?php echo e($orderPresentation['job_url']); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Open job card')); ?></a>
                            <?php endif; ?>
                        </div>
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

                <?php if($step === 5 && $order): ?>
                    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'border-emerald-200']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'border-emerald-200']); ?>
                        <div class="mb-4 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <span class="text-lg font-semibold text-emerald-600" aria-hidden="true">✓</span>
                            <div class="min-w-0">
                                <h2 class="text-sm font-semibold text-emerald-900"><?php echo e(__('5. Walk-in complete')); ?></h2>
                                <p class="mt-1 text-sm text-emerald-800">
                                    <?php echo e(__(':order is on the production queue. Production picks up from here.', ['order' => $orderPresentation['order_number']])); ?>

                                </p>
                                <?php if(! empty($orderPresentation['production']['work_center'])): ?>
                                    <p class="mt-1 text-xs text-emerald-700">
                                        <?php echo e(__('Queued at :work_center · :status', [
                                            'work_center' => $orderPresentation['production']['work_center'],
                                            'status' => $orderPresentation['production']['queue_status'] ?? __('Waiting'),
                                        ])); ?>

                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Completed steps')); ?></p>
                            <ul class="space-y-1 text-sm text-emerald-800">
                                <li>✓ <?php echo e(__('Customer')); ?> — <?php echo e($customer?->name ?? '—'); ?></li>
                                <li>✓ <?php echo e(__('Specification')); ?> — <?php echo e($specification?->name ?? __('On order')); ?></li>
                                <li>✓ <?php echo e(__('Order')); ?> — <?php echo e($orderPresentation['order_number']); ?></li>
                                <li>✓ <?php echo e(__('Artwork')); ?> — <?php echo e($specification?->activeArtworkVersion?->versionLabel() ?? __('Reviewed')); ?></li>
                                <li>✓ <?php echo e(__('Complete')); ?> — <?php echo e($orderPresentation['job_card_number'] ?? __('Job created')); ?></li>
                            </ul>
                        </div>

                        <?php if($orderPresentation['job_card_id']): ?>
                            <?php echo $__env->make('admin.sales.desk.partials.production-handoff', ['orderPresentation' => $orderPresentation], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?>

                        <div class="flex flex-wrap gap-2">
                            <a href="<?php echo e($orderPresentation['show_url']); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Open sales order')); ?></a>
                            <?php if($orderPresentation['job_url']): ?>
                                <a href="<?php echo e($orderPresentation['job_url']); ?>" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Open job card')); ?></a>
                            <?php endif; ?>
                            <?php if(! empty($orderPresentation['production']['department_queue_url'])): ?>
                                <a href="<?php echo e(WorkspaceEmbed::url($orderPresentation['production']['department_queue_url'])); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="<?php echo e($deskFrame); ?>" data-turbo-action="advance"><?php echo e(__('Open production queue')); ?></a>
                            <?php endif; ?>
                            <a href="<?php echo e(WorkspaceEmbed::url(route('admin.sales.desk'))); ?>" class="erp-btn-primary text-sm" data-turbo-frame="<?php echo e($deskFrame); ?>" data-turbo-action="advance"><?php echo e(__('Start another walk-in')); ?></a>
                        </div>
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

            <?php echo $__env->make('admin.sales.desk.partials.walk-in-panel', [
                'walkInPanel' => $walkInPanel ?? [],
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <?php echo $__env->make('admin.sales.desk.partials.work-queue', ['workQueue' => $workQueue], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/index.blade.php ENDPATH**/ ?>