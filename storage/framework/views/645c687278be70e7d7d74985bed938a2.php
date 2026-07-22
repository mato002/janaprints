<?php
    $alert = $dashboard['quote_requests_alert'] ?? ['visible' => false, 'count' => 0];
?>

<?php if(! empty($alert['visible'])): ?>
    <section
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'exec-quote-alert',
            'exec-quote-alert--active' => ($alert['has_action'] ?? false),
            'exec-quote-alert--clear' => ! ($alert['has_action'] ?? false),
        ]); ?>"
        aria-label="<?php echo e(__('New quote requests')); ?>"
    >
        <div class="exec-quote-alert__main">
            <?php if($alert['has_action'] ?? false): ?>
                <span class="exec-quote-alert__pulse" aria-hidden="true"></span>
                <span class="exec-quote-alert__ribbon"><?php echo e(__('Action Required')); ?></span>
            <?php endif; ?>
            <h2 class="exec-quote-alert__title"><?php echo e($alert['label'] ?? __('New Quote Requests')); ?></h2>
            <p class="exec-quote-alert__count"><?php echo e(number_format((int) ($alert['count'] ?? 0))); ?></p>
            <p class="exec-quote-alert__subtext"><?php echo e($alert['subtext'] ?? ''); ?></p>
        </div>

        <?php if(! empty($alert['route'])): ?>
            <a
                href="<?php echo e($alert['route']); ?>"
                data-turbo-frame="erp-main"
                class="exec-quote-alert__cta"
            >
                <?php echo e($alert['cta'] ?? __('Review Requests')); ?>

            </a>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\quote-requests-alert.blade.php ENDPATH**/ ?>