<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($document['title']); ?> <?php echo e($document['documentNumber']); ?></title>
    <?php echo $__env->make('documents.partials.styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('documents.partials.pdf-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class="jp-doc jp-doc--pdf">
    <div class="jp-doc__pdf-body">
        <?php echo $__env->make('documents.invoice.content', ['document' => $document], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\invoice\pdf.blade.php ENDPATH**/ ?>