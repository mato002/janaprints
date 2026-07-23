<h3 class="mt-4 font-medium"><?php echo e(__('Journal lines')); ?></h3>
<p class="mb-2 text-[11px] text-slate-500"><?php echo e(__('Total debits must equal total credits. Each line is debit OR credit.')); ?></p>
<div class="space-y-2">
    <div class="grid grid-cols-12 gap-2 text-[11px] font-medium uppercase text-slate-400">
        <span class="col-span-5"><?php echo e(__('Account')); ?></span>
        <span class="col-span-2"><?php echo e(__('Debit')); ?></span>
        <span class="col-span-2"><?php echo e(__('Credit')); ?></span>
        <span class="col-span-3"><?php echo e(__('Line note')); ?></span>
    </div>
    <?php $lineCount = max(4, count($journal?->lines ?? [])); ?>
    <?php for($i = 0; $i < $lineCount; $i++): ?>
        <?php
            $line = $journal?->lines?->get($i);
            $oldLine = old('lines.'.$i, []);
        ?>
        <div class="grid grid-cols-12 gap-2">
            <select name="lines[<?php echo e($i); ?>][gl_account_id]" class="erp-input col-span-5" required>
                <option value=""><?php echo e(__('— Account —')); ?></option>
                <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($account->id); ?>" <?php if(($oldLine['gl_account_id'] ?? $line?->gl_account_id) == $account->id): echo 'selected'; endif; ?>>
                        <?php echo e($account->code); ?> — <?php echo e($account->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="number" step="0.01" min="0" name="lines[<?php echo e($i); ?>][debit]" class="erp-input col-span-2" value="<?php echo e($oldLine['debit'] ?? $line?->debit ?? ''); ?>" placeholder="0.00">
            <input type="number" step="0.01" min="0" name="lines[<?php echo e($i); ?>][credit]" class="erp-input col-span-2" value="<?php echo e($oldLine['credit'] ?? $line?->credit ?? ''); ?>" placeholder="0.00">
            <input type="text" name="lines[<?php echo e($i); ?>][description]" class="erp-input col-span-3" value="<?php echo e($oldLine['description'] ?? $line?->description ?? ''); ?>" placeholder="<?php echo e(__('Optional')); ?>">
        </div>
    <?php endfor; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\accounting\journals\partials\lines-form.blade.php ENDPATH**/ ?>