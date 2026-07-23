<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['events']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['events']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $dotClasses = [
        'slate' => 'bg-slate-400 ring-slate-400/10',
        'blue' => 'bg-blue-500 ring-blue-500/10',
        'indigo' => 'bg-indigo-500 ring-indigo-500/10',
        'emerald' => 'bg-emerald-500 ring-emerald-500/10',
        'red' => 'bg-red-500 ring-red-500/10',
        'amber' => 'bg-amber-500 ring-amber-500/10',
        'sky' => 'bg-sky-500 ring-sky-500/10',
        'violet' => 'bg-violet-500 ring-violet-500/10',
        'pink' => 'bg-pink-500 ring-pink-500/10',
        'orange' => 'bg-orange-500 ring-orange-500/10',
        'rose' => 'bg-rose-500 ring-rose-500/10',
    ];

    $categoryLabels = [
        'notes' => __('Notes'),
        'activities' => __('Activities'),
        'files' => __('Files'),
        'quotations' => __('Quotations'),
        'orders' => __('Orders'),
        'artwork' => __('Artwork'),
        'production' => __('Production'),
        'quality' => __('Quality'),
        'dispatch' => __('Dispatch'),
        'accounting' => __('Accounting'),
        'operations' => __('Operations'),
        'materials' => __('Materials'),
        'traceability' => __('Traceability'),
        'communications' => __('Communications'),
    ];
?>

<ul class="c360-timeline-feed space-y-0" role="list">
    <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $title = is_array($event) ? ($event['title'] ?? '') : $event->title;
            $description = is_array($event) ? ($event['description'] ?? null) : $event->description;
            $eventDatetime = is_array($event)
                ? \Illuminate\Support\Carbon::parse($event['event_datetime'] ?? now())
                : $event->eventDatetime;
            $actorName = is_array($event) ? ($event['actor_name'] ?? null) : $event->actorName;
            $sourceUrl = is_array($event) ? ($event['source_url'] ?? null) : $event->sourceUrl;
            $color = is_array($event) ? ($event['color'] ?? 'slate') : $event->color;
            $category = is_array($event) ? ($event['category'] ?? 'all') : $event->category;
            $dotClass = $dotClasses[$color] ?? $dotClasses['slate'];
            $categoryLabel = $categoryLabels[$category] ?? str($category)->replace('_', ' ')->title();
        ?>
        <li class="relative flex gap-4 py-4 pl-6">
            <span class="absolute left-0 top-4 flex h-3 w-3 items-center justify-center">
                <span class="h-2 w-2 rounded-full ring-4 <?php echo e($dotClass); ?>"></span>
            </span>
            <?php if(! $loop->last): ?>
                <span class="absolute left-[5px] top-5 h-full w-px bg-erp-border" aria-hidden="true"></span>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <?php if($sourceUrl): ?>
                            <a href="<?php echo e($sourceUrl); ?>" data-turbo-frame="erp-main" class="text-sm font-semibold text-erp-primary hover:text-erp-accent">
                                <?php echo e($title); ?>

                            </a>
                        <?php else: ?>
                            <p class="text-sm font-semibold text-erp-primary"><?php echo e($title); ?></p>
                        <?php endif; ?>
                        <?php if($description): ?>
                            <p class="mt-1 text-sm text-slate-600 line-clamp-2"><?php echo e($description); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if($category !== 'all'): ?>
                        <span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                            <?php echo e($categoryLabel); ?>

                        </span>
                    <?php endif; ?>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">
                    <?php if($actorName): ?>
                        <?php echo e($actorName); ?>

                        ·
                    <?php endif; ?>
                    <time datetime="<?php echo e($eventDatetime->toIso8601String()); ?>"><?php echo e($eventDatetime->diffForHumans()); ?></time>
                    ·
                    <?php echo e($eventDatetime->format('M j, Y H:i')); ?>

                </p>
            </div>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="py-8 text-center text-sm text-slate-500"><?php echo e(__('No timeline events yet.')); ?></li>
    <?php endif; ?>
</ul>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\customer-timeline-feed.blade.php ENDPATH**/ ?>