<?php
    $filters = old('recipient_filters', $campaign?->recipient_filters ?? []);
    $selectedIds = collect($filters['ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values()->all();
?>

<div
    class="rounded-lg border border-erp-border p-3 space-y-2 min-h-0"
    x-show="['customers', 'leads', 'employees', 'suppliers'].includes(recipientSource)"
    x-cloak
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="text-xs font-semibold text-slate-500"><?php echo e(__('Pick recipients')); ?></p>
            <p class="text-xs text-slate-500">
                <span x-text="selectedRecipientCount"></span>
                <?php echo e(__('selected')); ?>

                ·
                <span x-text="visibleRecipients.length"></span>
                <?php echo e(__('shown')); ?>

                ·
                <?php echo e(__('Leave empty to include everyone matching the filters on the right.')); ?>

            </p>
        </div>
        <div class="flex gap-2">
            <button type="button" class="erp-btn erp-btn--ghost erp-btn--sm" @click="selectAllVisibleRecipients()"><?php echo e(__('Select all visible')); ?></button>
            <button type="button" class="erp-btn erp-btn--ghost erp-btn--sm" @click="clearSelectedRecipients()"><?php echo e(__('Clear')); ?></button>
        </div>
    </div>

    <input
        type="search"
        class="erp-input w-full"
        placeholder="<?php echo e(__('Search by name or phone…')); ?>"
        x-model="recipientSearch"
        autocomplete="off"
    >

    <div class="max-h-40 overflow-y-auto rounded-md border border-erp-border divide-y divide-erp-border bg-white">
        <template x-for="person in visibleRecipients" :key="recipientSource + '-' + person.id">
            <label class="flex cursor-pointer items-start gap-2 px-3 py-2 text-sm hover:bg-slate-50">
                <input
                    type="checkbox"
                    class="mt-0.5 rounded border-erp-border text-erp-accent"
                    :value="person.id"
                    :checked="selectedRecipientIds.includes(String(person.id)) || selectedRecipientIds.includes(Number(person.id))"
                    @change="toggleRecipient(person.id, $event.target.checked)"
                >
                <span class="min-w-0 flex-1">
                    <span class="block font-medium text-erp-primary" x-text="person.label"></span>
                    <span class="block font-mono text-xs text-slate-500" x-text="person.phone"></span>
                </span>
            </label>
        </template>
        <p x-show="visibleRecipients.length === 0" class="px-3 py-4 text-center text-xs text-slate-500">
            <?php echo e(__('No people with phone numbers for this source.')); ?>

        </p>
    </div>

    <template x-for="id in selectedRecipientIds" :key="'selected-' + id">
        <input type="hidden" name="recipient_filters[ids][]" :value="id">
    </template>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\sms\campaigns\_picker.blade.php ENDPATH**/ ?>