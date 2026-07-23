<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($document['title']); ?> <?php echo e($document['documentNumber']); ?></title>
    <?php echo $__env->make('documents.partials.styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>
        body { background: #f8fafc; margin: 0; padding: 24px; }
        .jp-doc-public-wrap { background: #fff; border: 1px solid #e2e8f0; margin: 0 auto; max-width: 820px; padding: 24px; }
    </style>
</head>
<body>
    <div class="jp-doc-public-wrap jp-doc">
        <?php echo $__env->make('documents.receipt.content', ['document' => $document], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\receipt\public.blade.php ENDPATH**/ ?>