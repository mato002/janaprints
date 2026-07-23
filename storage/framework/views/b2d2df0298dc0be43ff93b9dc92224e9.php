<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('RFQ Response')); ?> — <?php echo e($rfq->rfq_number); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-2xl font-semibold"><?php echo e(__('Request For Quotation')); ?></h1>
        <p class="mt-1 text-sm text-slate-600"><?php echo e($rfq->rfq_number); ?> · <?php echo e($rfqVendor->vendor->vendor_name); ?></p>
        <?php if(session('status')): ?>
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e(session('status')); ?></p>
        <?php endif; ?>
        <form method="POST" action="<?php echo e(route('rfq.portal.submit', $rfqVendor->response_token)); ?>" enctype="multipart/form-data" class="mt-6 space-y-4 rounded-xl bg-white p-6 shadow-sm">
            <?php echo csrf_field(); ?>
            <?php $__currentLoopData = $rfq->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border-b border-slate-100 pb-4">
                    <p class="font-medium"><?php echo e($item->description); ?></p>
                    <p class="text-sm text-slate-500"><?php echo e(__('Qty')); ?>: <?php echo e($item->quantity); ?></p>
                    <input type="hidden" name="lines[<?php echo e($index); ?>][rfq_item_id]" value="<?php echo e($item->id); ?>">
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        <input type="number" step="0.01" name="lines[<?php echo e($index); ?>][quoted_price]" class="erp-input w-full" placeholder="<?php echo e(__('Unit price')); ?>" required>
                        <input type="number" name="lines[<?php echo e($index); ?>][lead_time_days]" class="erp-input w-full" placeholder="<?php echo e(__('Lead time (days)')); ?>">
                    </div>
                    <input type="text" name="lines[<?php echo e($index); ?>][warranty]" class="erp-input mt-2 w-full" placeholder="<?php echo e(__('Warranty')); ?>">
                    <input type="text" name="lines[<?php echo e($index); ?>][delivery_terms]" class="erp-input mt-2 w-full" placeholder="<?php echo e(__('Delivery terms')); ?>">
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <div>
                <label class="text-sm font-medium"><?php echo e(__('Attachment (optional)')); ?></label>
                <input type="file" name="attachment" class="mt-1 block w-full text-sm">
            </div>
            <button type="submit" class="erp-btn-primary"><?php echo e(__('Submit quotation')); ?></button>
        </form>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\procurement\rfq-portal\show.blade.php ENDPATH**/ ?>