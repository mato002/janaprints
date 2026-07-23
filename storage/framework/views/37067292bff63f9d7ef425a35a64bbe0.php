<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $nav = [
        ['label' => __('Overview'), 'route' => 'admin.printing-intelligence.overview', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Artwork Analysis'), 'route' => 'admin.printing-intelligence.artwork-analysis.index', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Machine Intelligence'), 'route' => 'admin.printing-intelligence.machines', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Ink Intelligence'), 'route' => 'admin.printing-intelligence.ink', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Material Intelligence'), 'route' => 'admin.printing-intelligence.material', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Cost Intelligence'), 'route' => 'admin.printing-intelligence.cost', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Quotation Intelligence'), 'route' => 'admin.printing-intelligence.quotations', 'permission' => 'printing.intelligence.view'],
        ['label' => __('Estimate vs Actual'), 'route' => 'admin.printing-intelligence.estimate-vs-actual', 'permission' => 'printing.estimate-actual.view'],
        ['label' => __('Cost Accuracy Governance'), 'route' => 'admin.printing-intelligence.calibration-governance', 'permission' => 'printing.calibration.view'],
        ['label' => __('Production Profitability'), 'route' => 'admin.printing-intelligence.production-profitability', 'permission' => 'printing.profitability.view'],
        ['label' => __('Executive Intelligence'), 'route' => 'admin.printing-intelligence.executive-intelligence', 'permission' => 'printing.executive.view'],
        ['label' => __('Operations Advisor'), 'route' => 'admin.printing-intelligence.operations-advisor', 'permission' => 'printing.advisor.view'],
        ['label' => __('Configuration'), 'route' => 'admin.printing-intelligence.configuration', 'permission' => 'printing.intelligence.configure'],
    ];
?>

<?php if (! (WorkspaceEmbed::isEmbedded())): ?>
    <?php if (isset($component)) { $__componentOriginalaf09cf0e3994b3eea4e08edef5123a35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-nav','data' => ['links' => $nav,'variant' => 'compact','class' => 'mb-4 rounded-lg border border-slate-200 bg-white p-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['links' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($nav),'variant' => 'compact','class' => 'mb-4 rounded-lg border border-slate-200 bg-white p-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35)): ?>
<?php $attributes = $__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35; ?>
<?php unset($__attributesOriginalaf09cf0e3994b3eea4e08edef5123a35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalaf09cf0e3994b3eea4e08edef5123a35)): ?>
<?php $component = $__componentOriginalaf09cf0e3994b3eea4e08edef5123a35; ?>
<?php unset($__componentOriginalaf09cf0e3994b3eea4e08edef5123a35); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\partials\nav.blade.php ENDPATH**/ ?>