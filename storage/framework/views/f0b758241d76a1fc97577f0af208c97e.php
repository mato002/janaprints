<?php
    $itemsExpression = $itemsExpression ?? 'actionModalPanel?.quality?.checklist_items';
?>

<div x-show="(<?php echo e($itemsExpression); ?> ?? []).length > 0" x-cloak>
    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600"><?php echo e(__('Checklist')); ?></h4>
    <div class="overflow-x-auto rounded border border-erp-border">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th><?php echo e(__('Item')); ?></th>
                    <th class="w-16 text-center"><?php echo e(__('Pass')); ?></th>
                    <th class="w-16 text-center"><?php echo e(__('Fail')); ?></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in <?php echo e($itemsExpression); ?> ?? []" :key="`${index}-${item.label}`">
                    <tr>
                        <td>
                            <span x-text="item.label"></span>
                            <input type="hidden" :name="`checklist[${index}][line_id]`" :value="item.line_id ?? ''">
                            <input type="hidden" :name="`checklist[${index}][label]`" :value="item.label">
                        </td>
                        <td class="text-center">
                            <input type="radio" :name="`checklist[${index}][passed]`" value="1" class="rounded-full border-slate-300">
                        </td>
                        <td class="text-center">
                            <input type="radio" :name="`checklist[${index}][passed]`" value="0" class="rounded-full border-slate-300">
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/floor/partials/qc-checklist-table.blade.php ENDPATH**/ ?>