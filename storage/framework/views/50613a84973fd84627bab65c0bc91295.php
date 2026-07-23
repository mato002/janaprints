<section class="shared-inbox__empty">
    <div class="shared-inbox__empty-art" aria-hidden="true">
        <svg class="h-24 w-24 text-indigo-200" fill="none" viewBox="0 0 120 120">
            <rect x="12" y="20" width="72" height="52" rx="12" class="fill-white stroke-indigo-200" stroke-width="2"/>
            <path d="M24 44h48M24 56h32" class="stroke-indigo-300" stroke-width="2" stroke-linecap="round"/>
            <circle cx="88" cy="72" r="22" class="fill-indigo-50 stroke-indigo-300" stroke-width="2"/>
            <path d="M78 72h20M88 62v20" class="stroke-indigo-400" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </div>
    <h2 class="shared-inbox__empty-title"><?php echo e(__('Select a conversation')); ?></h2>
    <p class="shared-inbox__empty-desc">
        <?php echo e(__('Choose a customer thread from the left panel to view messages, files and activity.')); ?>

    </p>
    <p class="mt-3 flex items-center justify-center gap-1.5 text-xs text-slate-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.625 12a3.375 3.375 0 106.75 0 3.375 3.375 0 00-6.75 0zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <?php echo e(__('Or use :new above the list', ['new' => __('New conversation')])); ?>

    </p>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\empty-state.blade.php ENDPATH**/ ?>