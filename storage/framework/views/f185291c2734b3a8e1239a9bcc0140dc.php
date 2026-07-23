<?php $crm = $dashboard['crm']; $hr = $dashboard['hr']; ?>
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('CRM Pulse')); ?></h2></div>
    <div class="exec-metric-grid exec-metric-grid--2">
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Open Leads')); ?></span><span class="exec-metric__value"><?php echo e($crm['open_leads']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Customers Added')); ?></span><span class="exec-metric__value"><?php echo e($crm['customers_added']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Quotes Sent')); ?></span><span class="exec-metric__value"><?php echo e($crm['quotes_sent']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Conversion')); ?></span><span class="exec-metric__value"><?php echo e($crm['conversion_rate']); ?></span></div>
        <div class="exec-metric col-span-2"><span class="exec-metric__label"><?php echo e(__('Lost Opportunities')); ?></span><span class="exec-metric__value"><?php echo e($crm['lost_opportunities']); ?></span></div>
    </div>
</section>
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('HR Pulse')); ?></h2></div>
    <div class="exec-metric-grid exec-metric-grid--2">
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Staff Present')); ?></span><span class="exec-metric__value"><?php echo e($hr['present']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('On Leave')); ?></span><span class="exec-metric__value"><?php echo e($hr['on_leave']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Contract Expiry')); ?></span><span class="exec-metric__value"><?php echo e($hr['contract_expiry']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Performance Alerts')); ?></span><span class="exec-metric__value"><?php echo e($hr['performance_alerts']); ?></span></div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\crm-hr-pulse.blade.php ENDPATH**/ ?>