<?php
    $name = $active->display_name ?? $active->conversation_code;
    $initial = mb_strtoupper(mb_substr($name, 0, 1));
    $assignedLabel = $kpis['assigned_user'] !== __('Unassigned')
        ? __('Assigned: :name', ['name' => $kpis['assigned_user']])
        : __('Unassigned');
    $lastActivityLabel = $active->last_activity_at
        ? __('Last message: :time', ['time' => $active->last_activity_at->diffForHumans()])
        : __('No messages yet');
    $media = $workspaceData['media_library'] ?? ['images' => 0, 'files' => 0];
    $slaStatus = $kpis['sla_status'];
    $slaNeedsAttention = $slaStatus !== \App\Enums\InboxSlaStatus::Green;
    $statusLabel = $active->status->label();
?>

<header class="shared-inbox__thread-header">
    <div class="flex items-start gap-3">
        <div class="shared-inbox__thread-header-avatar"><?php echo e($initial); ?></div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="truncate text-base font-semibold text-slate-900"><?php echo e($name); ?></h2>
                <span class="inline-flex shrink-0 items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600">
                    <?php echo e($statusLabel); ?>

                </span>
            </div>
            <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1 font-medium text-indigo-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.625 9.75a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php echo e($lastChannel); ?>

                </span>
                <span aria-hidden="true" class="text-slate-300">·</span>
                <span><?php echo e($assignedLabel); ?></span>
                <span aria-hidden="true" class="text-slate-300">·</span>
                <span><?php echo e($lastActivityLabel); ?></span>
            </p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center justify-end gap-1.5">
            <?php if($slaNeedsAttention): ?>
                <button
                    type="button"
                    @click="$dispatch('open-manage-tab')"
                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold <?php echo e($slaStatus->badgeClass()); ?>"
                    title="<?php echo e(__('View SLA & assignment')); ?>"
                >
                    <?php echo e($slaStatus->label()); ?>

                </button>
            <?php endif; ?>
            <?php if(($media['images'] ?? 0) > 0): ?>
                <button
                    type="button"
                    @click="$dispatch('open-attachments-tab')"
                    class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-medium text-indigo-700 shadow-sm hover:bg-indigo-50"
                >
                    <?php echo e($media['images']); ?> <?php echo e(__('photos')); ?>

                </button>
            <?php endif; ?>
            <?php if($kpis['unread_count'] > 0): ?>
                <span class="shared-inbox__unread-badge"><?php echo e($kpis['unread_count']); ?></span>
            <?php endif; ?>
            <button
                type="button"
                @click="$dispatch('open-manage-tab')"
                class="shared-inbox__icon-btn"
                title="<?php echo e(__('Team, SLA & tags')); ?>"
                aria-label="<?php echo e(__('Settings')); ?>"
            >
                <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\thread-header.blade.php ENDPATH**/ ?>