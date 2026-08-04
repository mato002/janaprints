<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<?php if(! WorkspaceEmbed::inWorkspaceContext()): ?>
<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2">
    <?php $__currentLoopData = [
        ['route' => 'admin.communications.email.dashboard', 'label' => __('Dashboard')],
        ['route' => 'admin.communications.email.compose', 'label' => __('Compose'), 'permission' => 'communications.email.send'],
        ['route' => 'admin.communications.email.inbox.index', 'label' => __('Failed')],
        ['route' => 'admin.communications.email.queue.index', 'label' => __('Queued')],
        ['route' => 'admin.communications.email.sent.index', 'label' => __('Sent')],
        ['route' => 'admin.communications.email.reports.index', 'label' => __('Reports')],
        ['route' => 'admin.communications.email.campaigns.index', 'label' => __('Campaigns')],
        ['route' => 'admin.communications.email.templates.index', 'label' => __('Templates')],
        ['route' => 'admin.communications.templates.index', 'label' => __('COM-1 Templates'), 'query' => ['channel' => 'email']],
        ['route' => 'admin.communications.email.delivery.index', 'label' => __('Delivery tracking'), 'permission' => 'communications.email.audit'],
        ['route' => 'admin.communications.email.analytics', 'label' => __('Analytics')],
        ['route' => 'admin.communications.email.settings', 'label' => __('Settings'), 'permission' => 'communications.email.manage'],
        ['route' => 'admin.communications.email.certification', 'label' => __('Certification'), 'permission' => 'communications.email.manage'],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(empty($link['permission']) || auth()->user()->can($link['permission'])): ?>
            <a href="<?php echo e(route($link['route'], $link['query'] ?? [])); ?>" data-turbo-frame="erp-main" class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors <?php echo e(request()->routeIs($link['route'].'*') || request()->routeIs($link['route']) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50'); ?>"><?php echo e($link['label']); ?></a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/communications/email/partials/nav.blade.php ENDPATH**/ ?>