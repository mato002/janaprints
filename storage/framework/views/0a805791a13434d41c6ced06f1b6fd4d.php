<section class="space-y-4">
    <div class="ess-card flex items-start gap-4">
        <?php if($overview['photo_url']): ?>
            <img src="<?php echo e($overview['photo_url']); ?>" alt="" class="h-20 w-20 shrink-0 rounded-full object-cover ring-2 ring-erp-border">
        <?php else: ?>
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-erp-surface text-xl font-semibold text-erp-muted">
                <?php echo e(strtoupper(substr($overview['name'], 0, 1))); ?>

            </div>
        <?php endif; ?>
        <div class="min-w-0 flex-1">
            <h2 class="text-xl font-semibold"><?php echo e($overview['name']); ?></h2>
            <p class="text-sm text-erp-muted"><?php echo e($overview['job_title'] ?? __('Employee')); ?></p>
            <p class="mt-1 text-sm"><?php echo e($overview['employee_number']); ?></p>
        </div>
    </div>

    <?php if($overview['show_onboarding']): ?>
        <div class="ess-card border-amber-200 bg-amber-50">
            <p class="text-sm font-medium text-amber-900"><?php echo e(__('Onboarding in progress')); ?></p>
            <a href="<?php echo e(route('ess.dashboard', ['tab' => 'onboarding'])); ?>" class="ess-btn ess-btn--primary mt-3 w-full"><?php echo e(__('View onboarding tracker')); ?></a>
        </div>
    <?php endif; ?>

    <div class="grid gap-3 sm:grid-cols-2">
        <article class="ess-widget">
            <p class="ess-widget__label"><?php echo e(__('Latest payslip')); ?></p>
            <?php if($dashboard['latest_payslip']): ?>
                <p class="ess-widget__value">KES <?php echo e(number_format((float) $dashboard['latest_payslip']->net_pay, 0)); ?></p>
                <a href="<?php echo e(route('ess.payslips.download', $dashboard['latest_payslip'])); ?>" class="ess-btn ess-btn--primary mt-3 w-full"><?php echo e(__('Download PDF')); ?></a>
            <?php else: ?>
                <p class="text-sm text-erp-muted"><?php echo e(__('No payslips released yet.')); ?></p>
            <?php endif; ?>
        </article>

        <article class="ess-widget">
            <p class="ess-widget__label"><?php echo e(__('Employment')); ?></p>
            <p class="text-sm"><?php echo e($dashboard['employment']['department'] ?? '—'); ?></p>
            <p class="text-sm text-erp-muted"><?php echo e($dashboard['employment']['status'] ?? '—'); ?></p>
        </article>

        <article class="ess-widget">
            <p class="ess-widget__label"><?php echo e(__('Account status')); ?></p>
            <p class="ess-widget__value text-base"><?php echo e($dashboard['account_status']); ?></p>
        </article>

        <article class="ess-widget">
            <p class="ess-widget__label"><?php echo e(__('Recent documents')); ?></p>
            <p class="ess-widget__value text-base"><?php echo e($dashboard['recent_documents']->count()); ?></p>
            <a href="<?php echo e(route('ess.dashboard', ['tab' => 'documents'])); ?>" class="ess-btn ess-btn--ghost mt-3 w-full"><?php echo e(__('Open documents')); ?></a>
        </article>
    </div>

    <div class="ess-card">
        <h3 class="ess-section-title"><?php echo e(__('Employment details')); ?></h3>
        <dl class="ess-dl">
            <div><dt><?php echo e(__('Department')); ?></dt><dd><?php echo e($overview['department'] ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Branch')); ?></dt><dd><?php echo e($overview['branch'] ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Supervisor')); ?></dt><dd><?php echo e($overview['supervisor'] ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Employment status')); ?></dt><dd><?php echo e(ucfirst(str_replace('_', ' ', $overview['employment_status'] ?? '—'))); ?></dd></div>
            <div><dt><?php echo e(__('Employment date')); ?></dt><dd><?php echo e($overview['hire_date']?->format('d M Y') ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Corporate email')); ?></dt><dd class="break-all"><?php echo e($overview['corporate_email'] ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Phone')); ?></dt><dd><?php echo e($overview['phone'] ?? '—'); ?></dd></div>
            <div><dt><?php echo e(__('Mailbox status')); ?></dt><dd><?php echo e($overview['mailbox_status'] ?? '—'); ?></dd></div>
        </dl>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\overview.blade.php ENDPATH**/ ?>