<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<?php if(! WorkspaceEmbed::inWorkspaceContext()): ?>
<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2">
    <?php $__currentLoopData = [
        ['route' => 'admin.communications.logs.dashboard', 'label' => __('Dashboard')],
        ['route' => 'admin.communications.logs.timeline', 'label' => __('Timeline')],
        ['route' => 'admin.communications.logs.search', 'label' => __('Search')],
        ['route' => 'admin.communications.logs.analytics', 'label' => __('Analytics')],
        ['route' => 'admin.communications.logs.failures', 'label' => __('Failures')],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e(route($link['route'])); ?>"
            data-turbo-frame="erp-main"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors <?php echo e(request()->routeIs($link['route'].'*') || request()->routeIs($link['route']) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50'); ?>"
        >
            <?php echo e($link['label']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('export', App\Models\Communications\CommunicationLog::class)): ?>
        <a href="<?php echo e(route('admin.communications.logs.export')); ?>" class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50"><?php echo e(__('Export')); ?></a>
    <?php endif; ?>
</nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\partials\nav.blade.php ENDPATH**/ ?>