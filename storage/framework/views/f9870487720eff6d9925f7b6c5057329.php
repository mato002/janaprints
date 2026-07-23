<?php
    use App\Support\Crm\CrmDeskViews;
    use App\Models\Crm\Customer;
    use App\Models\Crm\Lead;
    use App\Models\Crm\CustomerSegment;
    use App\Models\Crm\CustomerActivity;

    $active = CrmDeskViews::normalize($activeCrmView ?? request('view'));
    $user = auth()->user();
    $modes = collect([
        [
            'key' => CrmDeskViews::CUSTOMERS,
            'label' => __('Customers'),
            'url' => CrmDeskViews::customersUrl(),
            'visible' => $user?->can('viewAny', Customer::class) ?? false,
        ],
        [
            'key' => CrmDeskViews::LEADS,
            'label' => __('Leads'),
            'url' => route('admin.crm.leads.index'),
            'visible' => $user?->can('viewAny', Lead::class) ?? false,
        ],
        [
            'key' => CrmDeskViews::ACTIVITIES,
            'label' => __('Activities'),
            'url' => route('admin.commercial.activities.index'),
            'visible' => $user?->can('viewAny', CustomerActivity::class) ?? false,
        ],
        [
            'key' => CrmDeskViews::SEGMENTS,
            'label' => __('Segments'),
            'url' => route('admin.crm.segments.index'),
            'visible' => $user?->can('viewAny', CustomerSegment::class) ?? false,
        ],
    ])->where('visible', true)->values();
?>

<?php if($modes->count() > 1): ?>
    <nav class="mb-4 flex flex-wrap gap-1.5" aria-label="<?php echo e(__('CRM desk modes')); ?>">
        <?php $__currentLoopData = $modes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e($mode['url']); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'erp-filter-pill',
                    'erp-filter-pill--active' => $mode['key'] === $active,
                ]); ?>"
                data-turbo-frame="<?php echo e(\App\Support\Navigation\WorkspaceEmbed::turboFrame()); ?>"
            ><?php echo e($mode['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/crm/partials/desk-mode-nav.blade.php ENDPATH**/ ?>