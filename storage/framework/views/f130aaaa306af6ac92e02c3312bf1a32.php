<?php if (isset($component)) { $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.client','data' => ['title' => __('Repeat order'),'heading' => __('Request repeat order')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.client'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Repeat order')),'heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Request repeat order'))]); ?>
  <?php if(session('status')): ?>
    <p class="client-flash mb-4"><?php echo e(session('status')); ?></p>
  <?php endif; ?>

  <p class="client-lead mb-6"><?php echo e(__('Select a previous job and submit a repeat request. Our team will review and confirm before any new order is created.')); ?></p>

  <div class="client-table-wrap">
    <table class="client-table">
      <thead>
        <tr>
          <th><?php echo e(__('Order')); ?></th>
          <th><?php echo e(__('Date')); ?></th>
          <th><?php echo e(__('Total')); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <tr>
            <td><?php echo e($order->order_number); ?></td>
            <td><?php echo e($order->order_date?->format('M j, Y')); ?></td>
            <td>KES <?php echo e(number_format((float) $order->total_amount, 0)); ?></td>
            <td>
              <form method="post" action="<?php echo e(route('client.repeat-orders.store', $order)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="client-button client-button--small"><?php echo e(__('Request repeat')); ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <tr><td colspan="4" class="client-empty"><?php echo e(__('No eligible orders yet.')); ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php echo e($orders->links()); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $attributes = $__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__attributesOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77)): ?>
<?php $component = $__componentOriginalc1a79cbe563a13156ed4a05a5df23f77; ?>
<?php unset($__componentOriginalc1a79cbe563a13156ed4a05a5df23f77); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\repeat-orders\index.blade.php ENDPATH**/ ?>