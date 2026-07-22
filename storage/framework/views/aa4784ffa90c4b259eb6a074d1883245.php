<div
    class="rounded-lg border border-erp-border p-3 space-y-3"
    x-show="recipientSource === 'imported'"
    x-cloak
>
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-xs font-semibold text-slate-500"><?php echo e(__('Import phone list')); ?></p>
            <p class="text-xs text-slate-500">
                <?php echo e(__('Upload a CSV/TXT file or paste numbers. One phone per line, or name,phone.')); ?>

            </p>
        </div>
        <a
            class="erp-btn erp-btn--ghost erp-btn--sm"
            href="data:text/csv;charset=utf-8,name%2Cphone%0AJane%20Doe%2C%2B254712345678%0AJohn%20Smith%2C0712345678"
            download="sms-recipients-sample.csv"
        >
            <?php echo e(__('Sample CSV')); ?>

        </a>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label class="erp-label text-xs"><?php echo e(__('Upload CSV / TXT')); ?></label>
            <input
                type="file"
                accept=".csv,.txt,text/csv,text/plain"
                class="erp-input w-full text-sm file:mr-2 file:rounded file:border-0 file:bg-slate-100 file:px-2 file:py-1"
                @change="importFromFile($event)"
            >
            <p class="mt-1 text-[11px] text-slate-500"><?php echo e(__('Headers like name,phone are detected automatically.')); ?></p>
        </div>
        <div class="flex flex-col justify-end gap-2 sm:items-end">
            <button type="button" class="erp-btn erp-btn--secondary erp-btn--sm w-full sm:w-auto" @click="applyImportedList()">
                <?php echo e(__('Parse & apply list')); ?>

            </button>
            <button type="button" class="erp-btn erp-btn--ghost erp-btn--sm w-full sm:w-auto" @click="clearImportedList()">
                <?php echo e(__('Clear list')); ?>

            </button>
        </div>
    </div>

    <div>
        <label class="erp-label text-xs"><?php echo e(__('Paste or edit numbers')); ?></label>
        <textarea
            class="erp-input w-full font-mono text-sm"
            rows="6"
            x-model="manualPhones"
            :name="recipientSource === 'imported' ? 'manual_phones' : null"
            placeholder="Jane Doe,+254712345678&#10;0712345678&#10;+254798765432"
            @input="importSummary = null"
        ></textarea>
    </div>

    <template x-if="importSummary">
        <div class="rounded border border-erp-border bg-slate-50 px-3 py-2 text-xs space-y-1">
            <p>
                <span class="font-semibold text-emerald-700" x-text="importSummary.valid"></span>
                <?php echo e(__('valid numbers ready to send')); ?>

                <span x-show="importSummary.invalid > 0">
                    · <span class="font-semibold text-amber-700" x-text="importSummary.invalid"></span>
                    <?php echo e(__('lines skipped')); ?>

                </span>
            </p>
            <ul class="list-disc pl-4 text-amber-800" x-show="importSummary.invalidSamples?.length">
                <template x-for="line in importSummary.invalidSamples" :key="line">
                    <li class="font-mono" x-text="line"></li>
                </template>
            </ul>
        </div>
    </template>
</div>

<div
    class="rounded-lg border border-erp-border p-3 space-y-2"
    x-show="recipientSource === 'manual'"
    x-cloak
>
    <p class="text-xs font-semibold text-slate-500"><?php echo e(__('Manual numbers')); ?></p>
    <p class="text-xs text-slate-500"><?php echo e(__('Enter one phone number per line.')); ?></p>
    <textarea
        class="erp-input w-full font-mono text-sm"
        rows="4"
        placeholder="+254712345678"
        x-model="manualPhones"
        :name="recipientSource === 'manual' ? 'manual_phones' : null"
    ></textarea>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\sms\campaigns\_import.blade.php ENDPATH**/ ?>