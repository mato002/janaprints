<?php
    $dispatchForm = $dispatchForm ?? [];
    $couriers = $couriers ?? [];
    $defaultKey = old('courier_key', $dispatchForm['default_courier_key'] ?? '');
    $suggestedNotes = old('dispatch_notes', $dispatchForm['suggested_dispatch_notes'] ?? '');
    $alpineConfig = [
        'courierKey' => $defaultKey,
        'couriers' => $couriers,
        'profiles' => $dispatchForm['courier_profiles'] ?? [],
        'vehicles' => $dispatchForm['vehicles'] ?? [],
        'previewTracking' => $dispatchForm['preview_tracking'] ?? '',
        'previewWaybill' => $dispatchForm['preview_waybill'] ?? '',
        'collectionOtp' => old('collection_otp', $dispatchForm['collection_otp_preview'] ?? ''),
        'deliveryOtp' => old('delivery_otp', $dispatchForm['delivery_otp_preview'] ?? ''),
        'vehicleId' => old('vehicle_asset_id', ''),
        'driverId' => old('driver_employee_id', ''),
    ];
?>

<form
    method="POST"
    action="<?php echo e(route('admin.dispatch.delivery-notes.dispatch', $note)); ?>"
    class="space-y-3 rounded-lg border border-erp-border p-3"
    x-data="{
        courierKey: <?php echo \Illuminate\Support\Js::from($alpineConfig['courierKey'])->toHtml() ?>,
        couriers: <?php echo \Illuminate\Support\Js::from($alpineConfig['couriers'])->toHtml() ?>,
        profiles: <?php echo \Illuminate\Support\Js::from($alpineConfig['profiles'])->toHtml() ?>,
        vehicles: <?php echo \Illuminate\Support\Js::from($alpineConfig['vehicles'])->toHtml() ?>,
        vehicleId: <?php echo \Illuminate\Support\Js::from($alpineConfig['vehicleId'])->toHtml() ?>,
        driverId: <?php echo \Illuminate\Support\Js::from($alpineConfig['driverId'])->toHtml() ?>,
        previewTracking: <?php echo \Illuminate\Support\Js::from($alpineConfig['previewTracking'])->toHtml() ?>,
        previewWaybill: <?php echo \Illuminate\Support\Js::from($alpineConfig['previewWaybill'])->toHtml() ?>,
        collectionOtp: <?php echo \Illuminate\Support\Js::from($alpineConfig['collectionOtp'])->toHtml() ?>,
        deliveryOtp: <?php echo \Illuminate\Support\Js::from($alpineConfig['deliveryOtp'])->toHtml() ?>,
        isExternalCourier() { return ['fargo', 'g4s'].includes(this.courierKey); },
        externalCourierLabel() { return this.couriers[this.courierKey] || <?php echo \Illuminate\Support\Js::from(__('External courier'))->toHtml() ?>; },
        courierProfile() { return this.profiles[this.courierKey] || null; },
        syncDriverFromVehicle() {
            const vehicle = this.vehicles.find((item) => String(item.id) === String(this.vehicleId));
            this.driverId = vehicle?.driver_employee_id ? String(vehicle.driver_employee_id) : '';
        },
        regenerateCollectionOtp() {
            this.collectionOtp = Math.random().toString(36).slice(2, 8).toUpperCase();
        },
    }"
>
    <?php echo csrf_field(); ?>
    <div class="flex items-center justify-between gap-2">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Courier / Dispatch')); ?></p>
        <span class="text-[11px] text-slate-500"><?php echo e(__('Confirm dispatch')); ?></span>
    </div>

    <div>
        <label class="erp-label text-xs"><?php echo e(__('Delivery method')); ?></label>
        <select name="courier_key" class="erp-input text-sm" required x-model="courierKey">
            <option value=""><?php echo e(__('Select delivery method')); ?></option>
            <?php $__currentLoopData = $couriers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3" x-show="courierKey === 'pickup'" x-cloak>
        <p class="text-xs font-semibold text-slate-700"><?php echo e(__('Customer collection')); ?></p>
        <div>
            <label class="erp-label text-xs"><?php echo e(__('Collector')); ?></label>
            <select name="collector_contact_id" class="erp-select w-full text-sm" :disabled="courierKey !== 'pickup'">
                <option value=""><?php echo e(__('Select customer contact')); ?></option>
                <?php $__currentLoopData = $dispatchForm['customer_contacts'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($contact->id); ?>" <?php if(old('collector_contact_id') == $contact->id): echo 'selected'; endif; ?>>
                        <?php echo e($contact->name); ?><?php if($contact->phone): ?> · <?php echo e($contact->phone); ?><?php endif; ?>
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Collection date')); ?></label>
                <input type="date" name="collection_date" class="erp-input w-full text-sm" value="<?php echo e(old('collection_date', $dispatchForm['collection_date'] ?? now()->toDateString())); ?>" readonly>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('OTP / collection code')); ?></label>
                <div class="flex gap-2">
                    <input type="text" name="collection_otp" class="erp-input w-full text-sm font-mono" x-model="collectionOtp" readonly>
                    <button type="button" class="erp-btn-secondary shrink-0 text-xs" @click="regenerateCollectionOtp()"><?php echo e(__('Regenerate')); ?></button>
                </div>
            </div>
        </div>
        <div>
            <label class="erp-label text-xs"><?php echo e(__('National ID')); ?></label>
            <input type="text" name="collector_id_number" class="erp-input w-full text-sm" value="<?php echo e(old('collector_id_number')); ?>" :disabled="courierKey !== 'pickup'">
        </div>
        <div>
            <label class="erp-label text-xs"><?php echo e(__('Received by (dispatch officer)')); ?></label>
            <input type="text" class="erp-input w-full bg-white text-sm" value="<?php echo e($dispatchForm['dispatch_officer'] ?? ''); ?>" readonly>
        </div>
    </div>

    <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3" x-show="courierKey === 'in_house'" x-cloak>
        <p class="text-xs font-semibold text-slate-700"><?php echo e(__('In-house delivery')); ?></p>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Vehicle')); ?></label>
                <select name="vehicle_asset_id" class="erp-select w-full text-sm" x-model="vehicleId" @change="syncDriverFromVehicle()" :disabled="courierKey !== 'in_house'">
                    <option value=""><?php echo e(__('Select vehicle')); ?></option>
                    <template x-for="vehicle in vehicles" :key="vehicle.id">
                        <option :value="vehicle.id" x-text="vehicle.label"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Driver')); ?></label>
                <select name="driver_employee_id" class="erp-select w-full text-sm" x-model="driverId" :disabled="courierKey !== 'in_house'">
                    <option value=""><?php echo e(__('Select driver')); ?></option>
                    <template x-for="vehicle in vehicles" :key="'driver-'+vehicle.id">
                        <option :value="vehicle.driver_employee_id" x-show="vehicle.driver_employee_id" x-text="vehicle.driver_name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Delivery route')); ?></label>
                <select name="delivery_route" class="erp-select w-full text-sm" :disabled="courierKey !== 'in_house'">
                    <option value=""><?php echo e(__('Select route')); ?></option>
                    <?php $__currentLoopData = $dispatchForm['delivery_routes'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($route['value']); ?>" <?php if(old('delivery_route') === $route['value']): echo 'selected'; endif; ?>><?php echo e($route['label']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Expected arrival')); ?></label>
                <input type="text" name="expected_arrival" class="erp-input w-full text-sm" value="<?php echo e(old('expected_arrival', $dispatchForm['expected_arrival'] ?? '')); ?>" readonly>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Tracking number')); ?></label>
                <input type="text" class="erp-input w-full bg-white text-sm font-mono" :value="previewTracking" readonly>
                <p class="mt-1 text-[11px] text-emerald-700"><?php echo e(__('Auto-generated')); ?></p>
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Waybill')); ?></label>
                <input type="text" class="erp-input w-full bg-white text-sm font-mono" :value="previewWaybill" readonly>
                <p class="mt-1 text-[11px] text-emerald-700"><?php echo e(__('Auto-generated')); ?></p>
            </div>
        </div>
        <template x-if="courierKey === 'in_house'">
            <div>
                <input type="hidden" name="tracking_number" :value="previewTracking">
                <input type="hidden" name="waybill_number" :value="previewWaybill">
            </div>
        </template>
        <div>
            <label class="erp-label text-xs"><?php echo e(__('Delivery OTP')); ?></label>
            <input type="text" name="delivery_otp" class="erp-input w-full text-sm font-mono" x-model="deliveryOtp" readonly>
        </div>
    </div>

    <div class="space-y-3 rounded-md border border-slate-200 bg-slate-50 p-3" x-show="isExternalCourier()" x-cloak>
        <p class="text-xs font-semibold text-slate-700" x-text="externalCourierLabel()"></p>
        <template x-if="courierProfile()?.integrated">
            <div class="rounded-md border border-sky-200 bg-sky-50 p-3 text-sm text-sky-950">
                <p class="font-semibold"><?php echo e(__('Courier integration connected')); ?></p>
                <p class="mt-1 text-xs" x-text="courierProfile()?.contact"></p>
                <p class="mt-1 text-xs" x-text="courierProfile()?.sla"></p>
                <button type="button" class="erp-btn-secondary mt-3 text-sm" disabled><?php echo e(__('Create shipment (coming soon)')); ?></button>
            </div>
        </template>
        <template x-if="courierProfile() && ! courierProfile().integrated">
            <div class="space-y-3">
                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-xs text-amber-950">
                    <p class="font-semibold"><?php echo e(__('Manual courier entry')); ?></p>
                    <p class="mt-1"><?php echo e(__('Enter tracking and waybill from the courier document.')); ?></p>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="erp-label text-xs"><?php echo e(__('Courier tracking')); ?></label>
                        <input type="text" name="tracking_number" class="erp-input w-full text-sm font-mono" value="<?php echo e(old('tracking_number', $note->tracking_number)); ?>" :disabled="! isExternalCourier() || courierProfile()?.integrated">
                    </div>
                    <div>
                        <label class="erp-label text-xs"><?php echo e(__('Courier waybill')); ?></label>
                        <input type="text" name="waybill_number" class="erp-input w-full text-sm font-mono" value="<?php echo e(old('waybill_number', $note->waybill_number)); ?>" :disabled="! isExternalCourier() || courierProfile()?.integrated">
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="space-y-3" x-show="courierKey === 'other'" x-cloak>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Courier tracking')); ?></label>
                <input type="text" name="tracking_number" class="erp-input w-full text-sm font-mono" value="<?php echo e(old('tracking_number', $note->tracking_number)); ?>" :disabled="courierKey !== 'other'">
            </div>
            <div>
                <label class="erp-label text-xs"><?php echo e(__('Courier waybill')); ?></label>
                <input type="text" name="waybill_number" class="erp-input w-full text-sm font-mono" value="<?php echo e(old('waybill_number', $note->waybill_number)); ?>" :disabled="courierKey !== 'other'">
            </div>
        </div>
    </div>

    <div>
        <label class="erp-label text-xs"><?php echo e(__('Dispatch notes')); ?></label>
        <textarea name="dispatch_notes" rows="4" class="erp-input w-full text-sm"><?php echo e($suggestedNotes); ?></textarea>
    </div>

    <?php if(! empty($dispatchForm['destination_summary'])): ?>
        <div class="rounded-md border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600">
            <p><span class="font-semibold text-slate-800"><?php echo e(__('Destination')); ?>:</span> <?php echo e($dispatchForm['destination_summary']); ?></p>
            <?php if(! empty($dispatchForm['order_summary'])): ?>
                <p class="mt-1"><span class="font-semibold text-slate-800"><?php echo e(__('Shipment')); ?>:</span> <?php echo e($dispatchForm['order_summary']); ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?><?php echo e(__('Dispatch')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
</form>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dispatch\delivery-notes\partials\dispatch-workflow-form.blade.php ENDPATH**/ ?>