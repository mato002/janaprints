<?php echo $__env->make('documents.partials.header', ['document' => $document], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('documents.partials.parties', [
    'customer' => $document['customer'],
    'customerLabel' => $document['customerLabel'] ?? __('Bill To'),
    'dates' => $document['dates'] ?? [],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('documents.partials.commercial-meta', ['meta' => $document['meta'] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('documents.partials.items-table', [
    'columns' => $document['columns'],
    'items' => $document['items'],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\quotation\content.blade.php ENDPATH**/ ?>