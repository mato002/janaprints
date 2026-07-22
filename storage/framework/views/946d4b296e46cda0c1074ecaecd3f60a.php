<?php if(! empty($notesTerms['body'])): ?>
    <div class="jp-doc__notes">
        <p class="jp-doc__notes-title"><?php echo e($notesTerms['title'] ?? __('Notes')); ?></p>
        <p class="jp-doc__notes-body"><?php echo e($notesTerms['body']); ?></p>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\documents\partials\notes-terms.blade.php ENDPATH**/ ?>