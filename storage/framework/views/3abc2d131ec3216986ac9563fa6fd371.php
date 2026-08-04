<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'quote',
    'role',
    'location',
]));

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

foreach (array_filter(([
    'quote',
    'role',
    'location',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article <?php echo e($attributes->merge(['class' => 'public-trust-testimonial'])); ?> data-testimonial-slide>
    <div class="public-trust-testimonial__stars" aria-label="5 out of 5 stars">
        <?php for($i = 0; $i < 5; $i++): ?>
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        <?php endfor; ?>
    </div>
    <blockquote class="public-trust-testimonial__quote">
        &ldquo;<?php echo e($quote); ?>&rdquo;
    </blockquote>
    <footer class="public-trust-testimonial__author">
        <cite class="public-trust-testimonial__role"><?php echo e($role); ?></cite>
        <span class="public-trust-testimonial__location"><?php echo e($location); ?></span>
    </footer>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\public\testimonial-card.blade.php ENDPATH**/ ?>