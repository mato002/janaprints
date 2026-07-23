<?php $reviews = config('testimonials.reviews'); ?>

<div class="public-review-marquee" data-review-marquee>
    <div class="public-review-marquee__track">
        <?php $__currentLoopData = array_merge($reviews, $reviews); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="public-review-marquee__item">
                <span class="public-review-marquee__stars" aria-hidden="true">★★★★★</span>
                <?php echo e($review); ?>

            </span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/review-marquee.blade.php ENDPATH**/ ?>