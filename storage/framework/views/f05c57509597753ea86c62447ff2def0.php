<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<?php if(! WorkspaceEmbed::inWorkspaceContext()): ?>
<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2">
    <?php $__currentLoopData = [
        ['route' => 'admin.communications.sms.dashboard', 'label' => __('Dashboard')],
        ['route' => 'admin.communications.sms.campaigns.index', 'label' => __('Campaigns')],
        ['route' => 'admin.communications.templates.index', 'label' => __('Templates'), 'query' => ['channel' => 'sms']],
        ['route' => 'admin.communications.sms.queues.index', 'label' => __('Queues')],
        ['route' => 'admin.communications.sms.provider-logs.index', 'label' => __('Provider logs'), 'permission' => 'communications.sms.audit'],
        ['route' => 'admin.communications.sms.credits.index', 'label' => __('Credit ledger')],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(empty($link['permission']) || auth()->user()->can($link['permission'])): ?>
            <a
                href="<?php echo e(route($link['route'], $link['query'] ?? [])); ?>"
                data-turbo-frame="erp-main"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors <?php echo e(request()->routeIs($link['route'].'*') || (isset($link['query']) && request()->routeIs('admin.communications.templates.*')) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50'); ?>"
            >
                <?php echo e($link['label']); ?>

            </a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/communications/sms/partials/nav.blade.php ENDPATH**/ ?>