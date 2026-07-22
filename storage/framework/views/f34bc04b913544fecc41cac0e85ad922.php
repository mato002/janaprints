<?php
    $campaign = $campaign ?? null;
?>

<div
    x-data="smsCampaignForm(<?php echo \Illuminate\Support\Js::from([
        'previewUrl' => route('admin.communications.sms.campaigns.preview'),
        'estimateUrl' => route('admin.communications.sms.campaigns.estimate-recipients'),
        'sendMode' => old('send_mode', $campaign?->send_mode?->value ?? 'immediate'),
        'recipientSource' => old('recipient_source', $campaign?->recipient_source?->value ?? 'customers'),
        'messageTemplate' => old('message_template', $campaign?->message_template ?? ''),
        'manualPhones' => old('manual_phones', ''),
        'pickerOptions' => $pickerOptions ?? [],
        'selectedRecipientIds' => collect(old('recipient_filters.ids', $campaign?->recipient_filters['ids'] ?? []))->map(fn ($id) => (string) $id)->values()->all(),
        'filters' => [
            'branch_id' => (string) old('recipient_filters.branch_id', $campaign?->recipient_filters['branch_id'] ?? ''),
            'customer_type' => (string) old('recipient_filters.customer_type', $campaign?->recipient_filters['customer_type'] ?? ''),
            'status' => (string) old('recipient_filters.status', $campaign?->recipient_filters['status'] ?? ''),
            'has_outstanding' => (string) old('recipient_filters.has_outstanding', $campaign?->recipient_filters['has_outstanding'] ?? ''),
            'department_id' => (string) old('recipient_filters.department_id', $campaign?->recipient_filters['department_id'] ?? ''),
            'employment_status' => (string) old('recipient_filters.employment_status', $campaign?->recipient_filters['employment_status'] ?? ''),
            'vendor_type' => (string) old('recipient_filters.vendor_type', $campaign?->recipient_filters['vendor_type'] ?? ''),
        ],
    ])->toHtml() ?>)"
    class="grid gap-3 lg:grid-cols-12"
>
    
    <div class="lg:col-span-8 grid gap-3 sm:grid-cols-2">
        <?php if($campaign): ?>
            <div class="sm:col-span-2">
                <label class="erp-label"><?php echo e(__('Campaign name')); ?> <span class="font-normal text-slate-400">(<?php echo e(__('optional')); ?>)</span></label>
                <input type="text" name="name" class="erp-input w-full" value="<?php echo e(old('name', $campaign->name)); ?>" placeholder="<?php echo e(__('Leave blank to keep the current name')); ?>">
            </div>
        <?php endif; ?>
        <div class="grid gap-2" :class="sendMode === 'scheduled' ? 'grid-cols-2' : 'grid-cols-1'">
            <div>
                <label class="erp-label"><?php echo e(__('Send mode')); ?></label>
                <select name="send_mode" class="erp-input w-full" x-model="sendMode">
                    <?php $__currentLoopData = $sendModes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($mode->value); ?>" <?php if(old('send_mode', $campaign?->send_mode?->value ?? 'immediate') === $mode->value): echo 'selected'; endif; ?>><?php echo e($mode->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div x-show="sendMode === 'scheduled'" x-cloak>
                <label class="erp-label"><?php echo e(__('Scheduled at')); ?></label>
                <input type="datetime-local" name="scheduled_at" class="erp-input w-full" value="<?php echo e(old('scheduled_at', $campaign?->scheduled_at?->format('Y-m-d\TH:i'))); ?>">
            </div>
        </div>

        <div>
            <label class="erp-label"><?php echo e(__('COM-1 template')); ?></label>
            <select name="communication_template_id" class="erp-input w-full" @change="onTemplateChange($event)">
                <option value=""><?php echo e(__('Custom message')); ?></option>
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($template->id); ?>" data-body="<?php echo e(e($template->body)); ?>" <?php if(old('communication_template_id', $campaign?->communication_template_id) == $template->id): echo 'selected'; endif; ?>><?php echo e($template->name); ?> (<?php echo e($template->code); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="erp-label"><?php echo e(__('Recipient source')); ?></label>
            <select name="recipient_source" class="erp-input w-full" x-model="recipientSource" @change="onRecipientSourceChange()">
                <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($source->value); ?>" <?php if(old('recipient_source', $campaign?->recipient_source?->value) === $source->value): echo 'selected'; endif; ?>><?php echo e($source->label()); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="erp-label"><?php echo e(__('Message template')); ?></label>
            <textarea name="message_template" class="erp-input w-full font-mono text-sm" rows="4" x-model="messageTemplate" required><?php echo e(old('message_template', $campaign?->message_template)); ?></textarea>
        </div>
    </div>

    
    <aside class="lg:col-span-4 lg:sticky lg:top-0 h-fit rounded-lg border border-erp-border bg-slate-50/80 p-3 space-y-2">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Message preview')); ?></h2>
            <p class="text-xs text-slate-500"><?php echo e(__('Rendered via COM-1 template engine')); ?></p>
        </div>
        <button type="button" class="erp-btn erp-btn--secondary erp-btn--sm w-full" @click="runPreview()"><?php echo e(__('Render preview')); ?></button>
        <template x-if="preview">
            <div class="space-y-2 text-sm">
                <pre class="whitespace-pre-wrap rounded border border-emerald-200 bg-emerald-50 p-2 text-xs" x-text="preview.body"></pre>
                <p class="text-xs text-slate-600">
                    <?php echo e(__('Characters')); ?>: <span x-text="preview.character_count"></span>
                    · <?php echo e(__('Segments')); ?>: <span x-text="preview.segments"></span>
                </p>
            </div>
        </template>
        <p x-show="!preview" class="rounded border border-dashed border-erp-border bg-white px-3 py-6 text-center text-xs text-slate-500">
            <?php echo e(__('Click render to see character count and segments.')); ?>

        </p>
        <p class="rounded bg-white border border-erp-border px-2 py-1.5 text-xs text-slate-600" x-show="audienceEstimate !== null" x-cloak>
            <?php echo e(__('Audience')); ?>:
            <span class="font-semibold text-erp-primary" x-text="audienceEstimate"></span>
        </p>
    </aside>

    
    <div
        class="lg:col-span-12 grid gap-3"
        :class="recipientSource === 'dynamic' ? 'lg:grid-cols-1' : 'lg:grid-cols-2'"
        x-show="['customers', 'dynamic', 'leads', 'employees', 'suppliers'].includes(recipientSource)"
        x-cloak
    >
        <?php echo $__env->make('admin.communications.sms.campaigns._picker', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.communications.sms.campaigns._filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="lg:col-span-12">
        <?php echo $__env->make('admin.communications.sms.campaigns._import', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\sms\campaigns\_form.blade.php ENDPATH**/ ?>