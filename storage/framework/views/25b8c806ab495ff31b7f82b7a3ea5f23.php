<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<div
    x-show="selectedKey"
    x-cloak
    class="designer-desk-workspace"
>
    <div x-show="panelLoading" class="rounded-xl border border-erp-border bg-white px-6 py-16 text-center text-sm text-slate-500">
        <?php echo e(__('Loading job…')); ?>

    </div>

    <template x-if="panel && !panelLoading">
        <?php if (isset($component)) { $__componentOriginal0de0e52b643095bf2659e655794f27e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0de0e52b643095bf2659e655794f27e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.artwork-preview-lightbox','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.artwork-preview-lightbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
            <div class="overflow-hidden rounded-xl border border-erp-border bg-white shadow-sm">
                <div class="border-b border-erp-border bg-slate-50/80 px-4 py-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Selected job')); ?></p>
                            <h2 class="font-mono text-xl font-bold text-erp-primary" x-text="panel.header?.request_number"></h2>
                            <p class="truncate text-sm text-slate-700" x-text="panel.header?.title"></p>
                        </div>
                        <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800 lg:hidden" @click="clearSelection()"><?php echo e(__('Close')); ?></button>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs sm:grid-cols-4">
                        <div>
                            <dt class="text-[10px] uppercase tracking-wide text-slate-400"><?php echo e(__('Customer')); ?></dt>
                            <dd class="font-semibold text-slate-900" x-text="panel.context?.customer ?? '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase tracking-wide text-slate-400"><?php echo e(__('Product')); ?></dt>
                            <dd class="font-medium text-slate-800" x-text="panel.context?.product ?? '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase tracking-wide text-slate-400"><?php echo e(__('Due')); ?></dt>
                            <dd
                                class="font-semibold"
                                :class="panel.header?.is_late ? 'text-rose-700' : (panel.header?.is_due_today ? 'text-amber-700' : 'text-slate-800')"
                                x-text="panel.header?.due_display ?? '—'"
                            ></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] uppercase tracking-wide text-slate-400"><?php echo e(__('Status')); ?></dt>
                            <dd class="font-medium text-slate-800" x-text="panel.header?.status"></dd>
                        </div>
                    </dl>
                </div>

                <template x-if="panel.guidance">
                    <div class="border-b border-amber-100 bg-amber-50 px-4 py-2 text-xs text-amber-950" x-text="panel.guidance"></div>
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-3">
                    <div class="space-y-0 border-b border-erp-border lg:col-span-2 lg:border-b-0 lg:border-r">
                        <section id="designer-desk-files" class="border-b border-erp-border p-4">
                            <h3 class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500"><?php echo e(__('Artwork & files')); ?></h3>

                            <div class="mb-3">
                                <p class="mb-1 text-[10px] font-semibold uppercase text-slate-400"><?php echo e(__('Versions')); ?></p>
                                <template x-if="!(panel.files?.versions?.length)">
                                    <p class="text-sm text-slate-500"><?php echo e(__('No artwork uploaded yet.')); ?></p>
                                </template>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="version in panel.files?.versions ?? []" :key="version.number">
                                        <div
                                            class="flex min-w-[4.5rem] flex-col items-center rounded-lg border px-2.5 py-2 text-center"
                                            :class="version.is_current ? 'border-erp-accent/40 bg-erp-accent/5' : 'border-slate-200'"
                                        >
                                            <span class="text-xs font-bold" x-text="'V' + version.number"></span>
                                            <div class="mt-1 flex gap-1">
                                                <button
                                                    x-show="version.previewable"
                                                    type="button"
                                                    class="text-[10px] font-semibold text-erp-accent"
                                                    :data-preview-url="version.preview_url"
                                                    :data-preview-title="version.name"
                                                    :data-preview-pdf="version.is_pdf ? '1' : '0'"
                                                    @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                                                ><?php echo e(__('View')); ?></button>
                                                <a x-show="version.download_url" :href="version.download_url" class="text-[10px] font-semibold text-slate-500" download>↓</a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div id="designer-desk-upload">
                                <template x-if="panel.files?.can_upload_version">
                                    <form
                                        :action="panel.files.upload_version_url"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        class="rounded-lg border border-dashed border-slate-300 bg-slate-50/80 p-3"
                                        <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>
                                    >
                                        <input type="hidden" name="_token" :value="csrf">
                                        <?php if($operatorMode): ?>
                                            <input type="hidden" name="from" value="designer-desk">
                                        <?php endif; ?>
                                        <p class="mb-2 text-xs font-semibold text-slate-800"><?php echo e(__('Upload artwork')); ?></p>
                                        <input type="file" name="file" class="erp-input mb-2 w-full text-xs" accept=".pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg" required>
                                        <input type="text" name="notes" class="erp-input mb-2 w-full text-xs" placeholder="<?php echo e(__('Version notes')); ?>">
                                        <button type="submit" class="erp-btn-primary w-full text-xs"><?php echo e(__('Upload version')); ?></button>
                                    </form>
                                </template>
                            </div>

                            <template x-if="panel.files?.customer?.length">
                                <div class="mt-3">
                                    <p class="mb-1 text-[10px] font-semibold uppercase text-slate-400"><?php echo e(__('Customer files')); ?></p>
                                    <ul class="space-y-1">
                                        <template x-for="file in panel.files?.customer ?? []" :key="file.id">
                                            <li class="flex items-center justify-between gap-2 rounded bg-slate-50 px-2 py-1.5 text-xs">
                                                <span class="truncate font-medium" x-text="file.name"></span>
                                                <a x-show="file.download_url" :href="file.download_url" class="shrink-0 font-semibold text-erp-accent" download><?php echo e(__('Download')); ?></a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                        </section>

                        <section class="border-b border-erp-border p-4">
                            <h3 class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500"><?php echo e(__('Specification')); ?></h3>
                            <template x-if="!(panel.specifications?.length)">
                                <p class="text-sm text-slate-500"><?php echo e(__('No print specification linked.')); ?></p>
                            </template>
                            <dl class="grid grid-cols-2 gap-2 text-sm">
                                <template x-for="spec in panel.specifications ?? []" :key="spec.label">
                                    <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400" x-text="spec.label"></dt>
                                        <dd class="mt-0.5 font-medium text-slate-800" x-text="spec.value"></dd>
                                    </div>
                                </template>
                            </dl>
                            <template x-if="panel.context?.description">
                                <p class="mt-2 rounded-lg bg-slate-50 px-2.5 py-2 text-xs text-slate-700" x-text="panel.context.description"></p>
                            </template>
                        </section>

                        <section class="p-4">
                            <h3 class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500"><?php echo e(__('Notes & comments')); ?></h3>
                            <div class="space-y-2 text-xs">
                                <template x-for="(note, idx) in panel.revision_notes?.customer ?? []" :key="'c-' + idx">
                                    <p class="rounded-lg bg-amber-50 px-2.5 py-2 text-slate-800" x-text="note"></p>
                                </template>
                                <template x-for="(note, idx) in panel.revision_notes?.sales ?? []" :key="'s-' + idx">
                                    <p class="rounded-lg bg-blue-50 px-2.5 py-2 text-slate-800" x-text="note"></p>
                                </template>
                                <template x-for="(note, idx) in panel.revision_notes?.internal ?? []" :key="'i-' + idx">
                                    <p class="rounded-lg bg-slate-50 px-2.5 py-2 text-slate-800" x-text="note"></p>
                                </template>
                                <template x-if="!(panel.revision_notes?.customer?.length || panel.revision_notes?.sales?.length || panel.revision_notes?.internal?.length)">
                                    <p class="text-slate-500"><?php echo e(__('No notes yet.')); ?></p>
                                </template>
                            </div>
                        </section>
                    </div>

                    
                    <aside class="flex flex-col bg-slate-50/50 p-4">
                        <h3 class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500"><?php echo e(__('Actions')); ?></h3>
                        <div class="flex flex-col gap-2">
                            <template x-for="(action, idx) in panel.primary_actions ?? []" :key="'a-' + idx">
                                <template x-if="action.type === 'post'">
                                    <form :action="action.url" method="POST" <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>>
                                        <input type="hidden" name="_token" :value="csrf">
                                        <?php if($operatorMode): ?>
                                            <input type="hidden" name="from" value="designer-desk">
                                        <?php endif; ?>
                                        <button
                                            type="submit"
                                            class="designer-desk-action-btn w-full"
                                            :class="action.variant === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary'"
                                            x-text="action.label"
                                        ></button>
                                    </form>
                                </template>
                                <template x-if="action.type === 'scroll'">
                                    <button
                                        type="button"
                                        class="designer-desk-action-btn erp-btn-secondary w-full"
                                        @click="scrollToSection(action.target)"
                                        x-text="action.label"
                                    ></button>
                                </template>
                                <template x-if="action.type === 'badge'">
                                    <span class="inline-flex items-center justify-center rounded-full bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-800" x-text="action.label"></span>
                                </template>
                            </template>
                        </div>

                        <template x-if="panel.links?.length">
                            <div class="mt-4 border-t border-slate-200 pt-3">
                                <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Open')); ?></p>
                                <ul class="space-y-1">
                                    <template x-for="(link, idx) in panel.links ?? []" :key="'l-' + idx">
                                        <li>
                                            <a :href="link.url" class="block text-xs font-semibold text-erp-accent hover:underline" data-turbo-frame="erp-main" x-text="link.label"></a>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <template x-if="panel.timeline?.length">
                            <div class="mt-4 border-t border-slate-200 pt-3">
                                <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Timeline')); ?></p>
                                <ul class="space-y-2">
                                    <template x-for="(event, idx) in panel.timeline ?? []" :key="'t-' + idx">
                                        <li class="flex gap-2 text-xs">
                                            <span class="w-14 shrink-0 font-mono tabular-nums text-slate-400" x-text="event.time"></span>
                                            <span class="text-slate-700" x-text="event.label"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </aside>
                </div>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $attributes = $__attributesOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__attributesOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0de0e52b643095bf2659e655794f27e9)): ?>
<?php $component = $__componentOriginal0de0e52b643095bf2659e655794f27e9; ?>
<?php unset($__componentOriginal0de0e52b643095bf2659e655794f27e9); ?>
<?php endif; ?>
    </template>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/partials/workspace.blade.php ENDPATH**/ ?>