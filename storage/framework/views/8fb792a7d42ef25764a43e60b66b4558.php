<?php
    $draftKey = 'inbox-draft-'.$active->id;
    $templates = $workspaceData['templates'] ?? collect();
    $createMenu = $context['create_menu'] ?? [];
    $defaultChannel = $channelFilter ?? $active->last_channel ?? \App\Enums\InboxMessageChannel::WhatsApp->value;
?>

<div
    class="shared-inbox__composer"
    x-data="{
        draft: localStorage.getItem(<?php echo \Illuminate\Support\Js::from($draftKey)->toHtml() ?>) || '',
        menuOpen: false,
        sendChannel: <?php echo \Illuminate\Support\Js::from($defaultChannel)->toHtml() ?>,
        fileName: '',
        init() {
            <?php if(session('inbox_reply_sent') || session('inbox_attachment_sent')): ?>
                this.clearDraft();
                this.fileName = '';
            <?php endif; ?>
        },
        saveDraft() { localStorage.setItem(<?php echo \Illuminate\Support\Js::from($draftKey)->toHtml() ?>, this.draft); },
        clearDraft() { this.draft = ''; localStorage.removeItem(<?php echo \Illuminate\Support\Js::from($draftKey)->toHtml() ?>); },
        insertTemplate(body) { this.draft = body; this.saveDraft(); },
        onFilePick(e) {
            const f = e.target.files?.[0];
            this.fileName = f ? f.name : '';
            if (f && !this.draft.trim()) { this.draft = ''; }
        },
    }"
>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
        <form
            method="POST"
            action="<?php echo e(route('admin.communications.inbox.attachments.store', $active)); ?>"
            enctype="multipart/form-data"
            class="hidden"
            data-turbo-frame="<?php echo e($inboxTurboFrame); ?>"
            x-ref="uploadForm"
        >
            <?php echo csrf_field(); ?>
            <input type="hidden" name="channel" :value="sendChannel">
            <input type="hidden" name="caption" :value="draft">
            <input type="file" name="file" x-ref="fileInput" accept="image/*,.pdf,.doc,.docx" @change="onFilePick($event); $refs.uploadForm.submit()">
        </form>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reply', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
        <form method="POST" action="<?php echo e(route('admin.communications.inbox.reply', $active)); ?>" class="shared-inbox__composer-inner" data-turbo-frame="<?php echo e($inboxTurboFrame); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="channel" :value="sendChannel">

            <div class="relative shrink-0">
                <button type="button" @click="menuOpen = !menuOpen" class="shared-inbox__composer-tool text-base" aria-label="<?php echo e(__('More')); ?>">⋯</button>
                <div x-show="menuOpen" x-cloak @click.outside="menuOpen = false" class="absolute bottom-full left-0 z-30 mb-2 min-w-[11rem] rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-lg">
                    <p class="px-3 py-1 text-[10px] font-semibold uppercase text-slate-400"><?php echo e(__('Send via')); ?></p>
                    <?php $__currentLoopData = \App\Enums\InboxMessageChannel::cases(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex cursor-pointer items-center gap-2 px-3 py-1.5 hover:bg-slate-50">
                            <input type="radio" value="<?php echo e($ch->value); ?>" class="text-indigo-600" :checked="sendChannel === <?php echo \Illuminate\Support\Js::from($ch->value)->toHtml() ?>" @change="sendChannel = <?php echo \Illuminate\Support\Js::from($ch->value)->toHtml() ?>">
                            <?php echo e($ch->label()); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($templates->isNotEmpty()): ?>
                        <hr class="my-1 border-slate-100">
                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @click="insertTemplate(<?php echo \Illuminate\Support\Js::from($tpl->body)->toHtml() ?>); menuOpen = false"><?php echo e($tpl->name); ?></button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('notes', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
                        <hr class="my-1 border-slate-100">
                        <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @click="$dispatch('open-notes-tab'); menuOpen = false"><?php echo e(__('Internal note')); ?></button>
                    <?php endif; ?>
                    <button type="button" class="block w-full px-3 py-1.5 text-left hover:bg-slate-50" @click="$dispatch('open-attachments-tab'); menuOpen = false"><?php echo e(__('Media & files')); ?></button>
                </div>
            </div>

            <div class="flex min-w-0 flex-1 flex-col gap-1">
                <p x-show="fileName" x-cloak class="truncate text-[10px] text-indigo-700" x-text="fileName ? <?php echo \Illuminate\Support\Js::from(__('Sending: '))->toHtml() ?>.concat(fileName) : ''"></p>
                <div class="shared-inbox__composer-field">
                    <div class="shared-inbox__composer-tools pb-0">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('attachments', App\Models\Communications\Inbox\CommunicationConversation::class)): ?>
                            <button type="button" @click="$refs.fileInput.click()" class="shared-inbox__composer-tool" title="<?php echo e(__('Photo or file (add caption below)')); ?>" aria-label="<?php echo e(__('Attach')); ?>">📎</button>
                        <?php endif; ?>
                        <button type="button" class="shared-inbox__composer-tool" title="<?php echo e(__('Emoji')); ?>" aria-label="<?php echo e(__('Emoji')); ?>" tabindex="-1">😊</button>
                    </div>
                    <textarea
                        name="body"
                        rows="1"
                        class="max-h-28 min-h-[1.5rem] w-full flex-1 resize-none border-0 bg-transparent py-2 text-[14px] leading-snug text-slate-800 placeholder:text-slate-400 focus:ring-0"
                        placeholder="<?php echo e(__('Type a message…')); ?>"
                        x-model="draft"
                        @input.debounce.500ms="saveDraft()"
                        @keydown.enter.prevent="if (!$event.shiftKey && draft.trim()) { $el.form.requestSubmit(); }"
                    ></textarea>
                </div>
            </div>

            <button type="submit" class="shared-inbox__composer-send" :disabled="!draft.trim()" aria-label="<?php echo e(__('Send')); ?>">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.4 20.4l17.45-7.61c.81-.35.81-1.46 0-1.81L3.4 3.57c-.66-.29-1.39.2-1.39.91v4.07c0 .55.45 1 1 1h5.59c.55 0 1 .45 1 1v2.12c0 .55-.45 1-1 1H3c-.55 0-1 .45-1 1v4.07c0 .71.73.1.2 1.4.91z"/></svg>
            </button>
        </form>
        <p class="mt-1.5 text-center text-[10px] text-slate-400"><?php echo e(__('Tip: type a caption, then tap 📎 to send image + text together')); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\workspace\composer.blade.php ENDPATH**/ ?>