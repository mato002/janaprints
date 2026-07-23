<table class="jp-doc__bottom-row" cellpadding="0" cellspacing="0">
    <tr>
        <td class="jp-doc__bottom-notes">
            <?php echo $__env->make('documents.partials.notes-terms', ['notesTerms' => $notesTerms ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </td>
        <td class="jp-doc__bottom-totals">
            <?php echo $__env->make('documents.partials.totals', ['totals' => $totals ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </td>
    </tr>
</table>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\totals-notes-row.blade.php ENDPATH**/ ?>