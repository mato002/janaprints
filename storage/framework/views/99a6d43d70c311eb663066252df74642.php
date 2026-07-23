<div class="comm-log-360__grid comm-log-360__grid--overview">
    <section class="comm-log-360__card comm-log-360__card--message">
        <h2 class="comm-log-360__card-title"><?php echo e(__('Message')); ?></h2>
        <?php if($log->subject && $bubbleTone === 'email'): ?>
            <p class="comm-log-360__email-subject"><?php echo e($log->subject); ?></p>
        <?php endif; ?>
        <div class="comm-log-360__bubble-wrap">
            <div class="comm-log-360__bubble comm-log-360__bubble--<?php echo e($bubbleTone); ?>">
                <?php if($log->message_body): ?>
                    <div class="comm-log-360__bubble-body"><?php echo $messageBodyHtml; ?></div>
                <?php else: ?>
                    <p class="comm-log-360__bubble-empty"><?php echo e(__('No message body recorded')); ?></p>
                <?php endif; ?>
                <p class="comm-log-360__bubble-meta"><?php echo e($log->created_at?->format('d M Y • H:i')); ?></p>
            </div>
        </div>
        <?php if($log->attachments->isNotEmpty()): ?>
            <div class="comm-log-360__attachments">
                <p class="comm-log-360__attachments-label"><?php echo e(__('Attachments')); ?></p>
                <ul class="comm-log-360__attachments-list" role="list">
                    <?php $__currentLoopData = $log->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="comm-log-360__attachment-item">
                            <span class="comm-log-360__attachment-icon" aria-hidden="true">📎</span>
                            <span><?php echo e($attachment->attachment_type->label()); ?> — <?php echo e($attachment->label); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
    </section>

    <section class="comm-log-360__card">
        <h2 class="comm-log-360__card-title"><?php echo e(__('Communication summary')); ?></h2>
        <dl class="comm-log-360__dl">
            <div>
                <dt><?php echo e(__('Channel')); ?></dt>
                <dd><?php echo e($log->channel->label()); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Status')); ?></dt>
                <dd><span class="comm-log-360__badge <?php echo e($log->status->badgeClass()); ?>"><?php echo e($log->status->label()); ?></span></dd>
            </div>
            <div>
                <dt><?php echo e(__('Type')); ?></dt>
                <dd><?php echo e($log->communication_type->label()); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Template')); ?></dt>
                <dd><?php echo e($log->template_code ?? $log->template?->name ?? '—'); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Created by')); ?></dt>
                <dd><?php echo e($log->creator?->name ?? '—'); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Sent by')); ?></dt>
                <dd><?php echo e($log->sentByUser?->name ?? '—'); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Created at')); ?></dt>
                <dd><?php echo e($log->created_at?->format('d M Y H:i') ?? '—'); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Sent at')); ?></dt>
                <dd><?php echo e($log->sent_at?->format('d M Y H:i') ?? '—'); ?></dd>
            </div>
            <?php if($log->branch): ?>
                <div>
                    <dt><?php echo e(__('Branch')); ?></dt>
                    <dd><?php echo e($log->branch->name); ?></dd>
                </div>
            <?php endif; ?>
            <?php if($log->priority): ?>
                <div>
                    <dt><?php echo e(__('Priority')); ?></dt>
                    <dd><span class="comm-log-360__badge <?php echo e($log->priority->badgeClass()); ?>"><?php echo e($log->priority->label()); ?></span></dd>
                </div>
            <?php endif; ?>
        </dl>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\logs\360\tab-overview.blade.php ENDPATH**/ ?>