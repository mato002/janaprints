<?php echo $__env->make('documents.partials.header', ['document' => $document], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('documents.partials.parties', [
    'customer' => $document['customer'],
    'customerLabel' => $document['customerLabel'] ?? __('Received From'),
    'dates' => $document['dates'] ?? [],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('documents.partials.allocations-table', ['allocations' => $document['allocations'] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('documents.partials.totals-notes-row', [
    'notesTerms' => $document['notesTerms'] ?? [],
    'totals' => $document['totals'],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('documents.partials.payment-footer', [
    'paymentFooter' => $document['paymentFooter'] ?? [],
    'documentFooter' => $document['documentFooter'] ?? [],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php if(empty($document['paymentFooter'])): ?>
    <?php echo $__env->make('documents.partials.footer', ['documentFooter' => $document['documentFooter'] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\receipt\content.blade.php ENDPATH**/ ?>