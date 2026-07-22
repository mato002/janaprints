<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<?php if(! WorkspaceEmbed::inWorkspaceContext()): ?>
<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2">
    <?php $__currentLoopData = [
        ['route' => 'admin.communications.whatsapp.inbox', 'label' => __('Inbox')],
        ['route' => 'admin.communications.whatsapp.conversations.index', 'label' => __('Conversations')],
        ['route' => 'admin.communications.whatsapp.templates.index', 'label' => __('Templates')],
        ['route' => 'admin.communications.templates.index', 'label' => __('COM-1 Templates'), 'query' => ['channel' => 'whatsapp']],
        ['route' => 'admin.communications.whatsapp.queue.index', 'label' => __('Queue')],
        ['route' => 'admin.communications.whatsapp.delivery.index', 'label' => __('Delivery status'), 'permission' => 'communications.whatsapp.audit'],
        ['route' => 'admin.communications.whatsapp.analytics', 'label' => __('Analytics')],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(empty($link['permission']) || auth()->user()->can($link['permission'])): ?>
            <a
                href="<?php echo e(route($link['route'], $link['query'] ?? [])); ?>"
                data-turbo-frame="erp-main"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors <?php echo e(request()->routeIs($link['route'].'*') || request()->routeIs($link['route']) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50'); ?>"
            >
                <?php echo e($link['label']); ?>

            </a>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\whatsapp\partials\nav.blade.php ENDPATH**/ ?>