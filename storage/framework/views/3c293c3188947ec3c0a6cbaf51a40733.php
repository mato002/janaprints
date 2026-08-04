<?php
    $parsed = \App\Support\Client\ClientChatMessagePresenter::splitQuote((string) ($body ?? ''));
    $isOutgoing = (bool) ($outgoing ?? false);
?>

<?php if($parsed['quoted'] || $parsed['quoted_author']): ?>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'client-chat__quote',
        'client-chat__quote--out' => $isOutgoing,
        'client-chat__quote--in' => ! $isOutgoing,
    ]); ?>" role="note" aria-label="<?php echo e(__('Quoted message')); ?>">
        <?php if($parsed['quoted_author']): ?>
            <p class="client-chat__quote-author"><?php echo e($parsed['quoted_author']); ?></p>
        <?php endif; ?>
        <?php if($parsed['quoted']): ?>
            <p class="client-chat__quote-text"><?php echo e($parsed['quoted']); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($parsed['body'] !== ''): ?>
    <p class="client-chat__text"><?php echo e($parsed['body']); ?></p>
<?php elseif(! $parsed['quoted'] && ! $parsed['quoted_author']): ?>
    <p class="client-chat__text"><?php echo e($body); ?></p>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\communications\partials\message-body.blade.php ENDPATH**/ ?>