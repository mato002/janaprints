<div
    x-data="{
        open: false,
        url: '',
        title: '',
        isPdf: false,
        show(url, title, isPdf = false) {
            this.url = url;
            this.title = title;
            this.isPdf = isPdf;
            this.open = true;
        },
        close() {
            this.open = false;
            this.url = '';
        },
    }"
    @keydown.escape.window="if (open) close()"
    <?php echo e($attributes); ?>

>
    <?php echo e($slot); ?>


    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-900/60 p-4 sm:p-8"
        @click="close()"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="flex max-h-[min(85vh,720px)] w-full max-w-3xl flex-col overflow-hidden rounded-xl border border-erp-border bg-white shadow-2xl"
            @click.stop
        >
            <div class="flex items-center justify-between gap-3 border-b border-erp-border px-4 py-3">
                <p class="truncate text-sm font-medium text-slate-900" x-text="title"></p>
                <button type="button" class="erp-btn-ghost text-sm" @click="close()"><?php echo e(__('Close')); ?></button>
            </div>
            <div class="flex flex-1 items-center justify-center overflow-auto bg-slate-50 p-4">
                <img
                    x-show="open && !isPdf"
                    :src="url"
                    :alt="title"
                    class="max-h-[min(70vh,600px)] max-w-full object-contain"
                >
                <iframe
                    x-show="open && isPdf"
                    :src="url"
                    class="h-[min(70vh,600px)] w-full border-0 bg-white"
                    :title="title"
                ></iframe>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/artwork-preview-lightbox.blade.php ENDPATH**/ ?>