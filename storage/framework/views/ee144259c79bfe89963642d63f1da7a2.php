<?php
    $inboxUnreadSummaryUrl = \Illuminate\Support\Facades\Route::has('admin.communications.inbox.unread-summary')
        ? route('admin.communications.inbox.unread-summary')
        : null;
    $currentView = $filters['view'] ?? 'all';
    $viewChips = [
        'all' => __('All'),
        'open' => __('Open'),
        'my' => __('Assigned'),
        'unassigned' => __('Unassigned'),
        'waiting_customer' => __('Waiting'),
        'closed' => __('Closed'),
    ];
    $extraViews = [
        'unread' => __('Unread'),
        'waiting_internal' => __('Waiting internal'),
        'escalated' => __('Escalated'),
        'overdue' => __('Overdue'),
    ];
    $chipQuery = fn (string $view) => $inboxEmbedUrl(route('admin.communications.inbox.index', array_merge(
        request()->except(['page']),
        array_filter([
            'view' => $view,
            'q' => $filters['q'] ?? null,
            'status' => $filters['status'] ?? null,
            'tag' => $filters['tag'] ?? null,
            'conversation' => $active?->id,
        ], fn ($v) => $v !== null && $v !== '')
    )));
?>

<aside
    class="shared-inbox__list-panel flex h-full min-h-0 w-full flex-col overflow-hidden"
    data-inbox-list-panel
    <?php if($inboxUnreadSummaryUrl): ?> data-inbox-unread-summary-url="<?php echo e($inboxUnreadSummaryUrl); ?>" <?php endif; ?>
>
    <div class="shared-inbox__list-header">
        <div x-show="newConvoOpen" x-cloak class="shared-inbox__new-panel">
            <?php echo $__env->make('admin.communications.inbox.partials.start-conversation', ['compact' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <div x-show="!newConvoOpen" class="text-[11px] text-slate-500">
            <button type="button" class="font-semibold text-indigo-700 hover:underline" @click="newConvoOpen = true">+ <?php echo e(__('New conversation')); ?></button>
        </div>

        <form method="GET" class="space-y-0" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php if($active): ?><input type="hidden" name="conversation" value="<?php echo e($active->id); ?>"><?php endif; ?>
            <input type="hidden" name="view" value="<?php echo e($currentView); ?>">
            <?php if(! empty($filters['status'])): ?><input type="hidden" name="status" value="<?php echo e($filters['status']); ?>"><?php endif; ?>
            <?php if(! empty($filters['tag'])): ?><input type="hidden" name="tag" value="<?php echo e($filters['tag']); ?>"><?php endif; ?>

            <div class="shared-inbox__search-wrap">
                <svg class="shared-inbox__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input
                    type="search"
                    name="q"
                    value="<?php echo e($filters['q'] ?? ''); ?>"
                    class="shared-inbox__search"
                    placeholder="<?php echo e(__('Search conversations…')); ?>"
                >
            </div>
        </form>

        <nav class="shared-inbox__filters" aria-label="<?php echo e(__('Conversation filters')); ?>">
            <?php $__currentLoopData = $viewChips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e($chipQuery($key)); ?>"
                    data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
                    class="shared-inbox__chip <?php echo e($currentView === $key ? 'shared-inbox__chip--active' : ''); ?>"
                ><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>

        <details class="shared-inbox__more-filters">
            <summary class="cursor-pointer font-medium hover:text-slate-700"><?php echo e(__('More filters')); ?></summary>
            <form method="GET" class="mt-2 space-y-2" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
                <?php if($active): ?><input type="hidden" name="conversation" value="<?php echo e($active->id); ?>"><?php endif; ?>
                <?php if(! empty($filters['q'])): ?><input type="hidden" name="q" value="<?php echo e($filters['q']); ?>"><?php endif; ?>
                <input type="hidden" name="view" value="<?php echo e($currentView); ?>">
                <div class="flex flex-wrap gap-1">
                    <?php $__currentLoopData = $extraViews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($chipQuery($key)); ?>" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>" class="shared-inbox__chip <?php echo e($currentView === $key ? 'shared-inbox__chip--active' : ''); ?>"><?php echo e($label); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <select name="status" class="erp-input w-full text-xs" onchange="this.form.submit()">
                    <option value=""><?php echo e(__('Status')); ?></option>
                    <?php $__currentLoopData = \App\Enums\InboxConversationStatus::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($st->value); ?>" <?php if(($filters['status'] ?? '') === $st->value): echo 'selected'; endif; ?>><?php echo e($st->label()); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <input type="text" name="tag" value="<?php echo e($filters['tag'] ?? ''); ?>" class="erp-input w-full text-xs" placeholder="<?php echo e(__('Tag #urgent')); ?>">
                <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs w-full"><?php echo e(__('Apply')); ?></button>
            </form>
        </details>
    </div>

    <div class="shared-inbox__list-scroll">
        <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $name = $conv->display_name ?? $conv->conversation_code;
                $initial = mb_strtoupper(mb_substr($name, 0, 1));
                $isActive = ($active?->id ?? null) === $conv->id;
                $timeLabel = $conv->last_activity_at?->isToday()
                    ? $conv->last_activity_at->format('H:i')
                    : ($conv->last_activity_at?->format('d/m') ?? '');
            ?>
            <a
                href="<?php echo e($inboxEmbedUrl(route('admin.communications.inbox.index', array_merge(request()->query(), ['conversation' => $conv->id])))); ?>"
                data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
                data-conversation-id="<?php echo e($conv->id); ?>"
                class="shared-inbox__conv-row <?php echo e($isActive ? 'shared-inbox__conv-row--active' : ''); ?>"
            >
                <div class="shared-inbox__avatar"><?php echo e($initial); ?></div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="truncate text-[15px] font-semibold text-slate-900"><?php echo e($name); ?></p>
                        <?php if($timeLabel): ?>
                            <span class="shrink-0 text-[11px] font-medium text-slate-400"><?php echo e($timeLabel); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mt-0.5 flex items-center justify-between gap-2">
                        <p class="truncate text-[13px] text-slate-500"><?php echo e($conv->last_message_preview ?? __('No messages')); ?></p>
                        <?php if($conv->unread_count > 0): ?>
                            <span
                                class="shared-inbox__unread-badge"
                                data-conversation-unread-badge
                                aria-label="<?php echo e(__(':count unread', ['count' => $conv->unread_count])); ?>"
                            ><?php echo e($conv->unread_count); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500"><?php echo e(__('No conversations match your filters.')); ?></p>
        <?php endif; ?>
    </div>
    <?php if($conversations->hasPages()): ?>
        <div class="shared-inbox-scrollbar shrink-0 border-t border-slate-200 p-2 text-xs"><?php echo e($conversations->links()); ?></div>
    <?php endif; ?>
</aside>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\partials\list-panel.blade.php ENDPATH**/ ?>