<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<div
    x-show="selectedKey"
    x-cloak
    class="designer-desk-workspace"
>
    <div x-show="panelLoading" class="artwork-detail-card py-16 text-center text-sm text-slate-500">
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
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-none">
                
                <div class="border-b border-slate-200 bg-slate-50/50 px-5 py-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Selected job')); ?></p>
                            <h2 class="artwork-detail-header__number" x-text="panel.header?.request_number"></h2>
                            <p class="artwork-detail-header__title truncate" x-text="panel.header?.title"></p>
                            <p class="artwork-detail-header__customer truncate" x-text="panel.context?.customer"></p>
                        </div>
                        <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800 lg:hidden" @click="clearSelection()"><?php echo e(__('Close')); ?></button>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                            :class="{
                                'bg-slate-100 text-slate-700 ring-slate-500/20': panel.header?.status_value === 'requested',
                                'bg-blue-50 text-blue-700 ring-blue-600/20': panel.header?.status_value === 'in_design',
                                'bg-indigo-50 text-indigo-700 ring-indigo-600/20': panel.header?.status_value === 'submitted',
                                'bg-emerald-50 text-emerald-700 ring-emerald-600/20': panel.header?.status_value === 'approved',
                                'bg-amber-50 text-amber-800 ring-amber-600/20': panel.header?.status_value === 'revision_requested',
                                'bg-red-50 text-red-700 ring-red-600/20': panel.header?.status_value === 'rejected',
                            }"
                        >
                            <span x-show="panel.header?.status_value === 'in_design'" class="mr-1">●</span>
                            <span x-text="panel.header?.status"></span>
                        </span>
                        <span class="text-xs tabular-nums text-slate-500" x-text="'v' + (panel.header?.version ?? 0)"></span>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs sm:grid-cols-3">
                        <div>
                            <dt class="text-[10px] uppercase tracking-wide text-slate-400"><?php echo e(__('Designer')); ?></dt>
                            <dd class="font-medium text-slate-800" x-text="panel.header?.designer || '<?php echo e(__('Unclaimed')); ?>'"></dd>
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
                            <dt class="text-[10px] uppercase tracking-wide text-slate-400"><?php echo e(__('Priority')); ?></dt>
                            <dd class="font-medium capitalize text-slate-800" x-text="panel.header?.priority_value ?? '—'"></dd>
                        </div>
                    </dl>
                </div>

                
                <template x-if="panel.guidance">
                    <div class="border-b border-erp-accent/15 bg-gradient-to-r from-erp-accent/[0.06] to-transparent px-5 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500"><?php echo e(__('Workflow')); ?></p>
                        <p class="mt-1 text-sm text-slate-700" x-text="panel.guidance"></p>
                    </div>
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-3">
                    <div class="space-y-0 border-b border-slate-200 lg:col-span-2 lg:border-b-0 lg:border-r">
                        
                        <section id="designer-desk-files" class="border-b border-slate-200 p-5">
                            <h3 class="artwork-detail-card__title"><?php echo e(__('Artwork & files')); ?></h3>

                            <p class="mb-2 text-[10px] font-semibold uppercase text-slate-400"><?php echo e(__('Versions')); ?></p>

                            <template x-if="!(panel.files?.versions?.length)">
                                <div class="artwork-detail-empty mb-4 py-6">
                                    <span class="artwork-detail-empty__icon" aria-hidden="true">↑</span>
                                    <p class="artwork-detail-empty__title"><?php echo e(__('No versions yet')); ?></p>
                                    <p class="artwork-detail-empty__hint"><?php echo e(__('Upload the first artwork version to begin the approval workflow.')); ?></p>
                                </div>
                            </template>

                            <div class="mb-4 flex flex-wrap gap-2">
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

                            <div id="designer-desk-upload">
                                <template x-if="panel.files?.can_upload_version">
                                    <form
                                        :action="panel.files.upload_version_url"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        class="artwork-detail-upload-section !mt-0 !border-t-0 !pt-0"
                                        data-erp-desk-form
                                    >
                                        <input type="hidden" name="_token" :value="csrf">
                                        <input type="hidden" name="from" value="designer-desk">
                                        <p class="artwork-detail-upload-section__title"><?php echo e(__('Upload version')); ?></p>
                                        <div
                                            x-data="{ fileName: '' }"
                                            class="artwork-detail-file-upload"
                                            :class="{ 'artwork-detail-file-upload--has-file': fileName !== '' }"
                                            @click="$refs.deskFileInput.click()"
                                            role="button"
                                            tabindex="0"
                                        >
                                            <input
                                                x-ref="deskFileInput"
                                                type="file"
                                                name="file"
                                                class="artwork-detail-file-upload__input"
                                                accept=".pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg"
                                                required
                                                @change="fileName = $event.target.files?.[0]?.name ?? ''"
                                            >
                                            <svg class="artwork-detail-file-upload__icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            <p class="artwork-detail-file-upload__label" x-show="!fileName"><?php echo e(__('Choose artwork file')); ?></p>
                                            <p class="artwork-detail-file-upload__hint" x-show="!fileName"><?php echo e(__('PDF, AI, PSD, PNG, JPG…')); ?></p>
                                            <p class="artwork-detail-file-upload__name" x-show="fileName" x-text="fileName"></p>
                                        </div>
                                        <input type="text" name="notes" class="erp-input w-full text-sm" placeholder="<?php echo e(__('Version notes')); ?>">
                                        <button
                                            type="submit"
                                            class="erp-btn-primary w-full text-sm"
                                            :class="{ 'erp-btn-secondary': (panel.files?.versions?.length ?? 0) > 0 }"
                                        ><?php echo e(__('Upload version')); ?></button>
                                    </form>
                                </template>
                            </div>

                            <template x-if="panel.files?.customer?.length">
                                <div class="mt-4 border-t border-slate-100 pt-4">
                                    <p class="mb-2 text-[10px] font-semibold uppercase text-slate-400"><?php echo e(__('Customer files')); ?></p>
                                    <ul class="space-y-1">
                                        <template x-for="file in panel.files?.customer ?? []" :key="file.id">
                                            <li class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-2.5 py-1.5 text-xs">
                                                <span class="truncate font-medium" x-text="file.name"></span>
                                                <a x-show="file.download_url" :href="file.download_url" class="shrink-0 font-semibold text-erp-accent" download><?php echo e(__('Download')); ?></a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                        </section>

                        <section class="border-b border-slate-200 p-5">
                            <h3 class="artwork-detail-card__title"><?php echo e(__('Specification')); ?></h3>
                            <template x-if="!(panel.specifications?.length)">
                                <p class="text-sm text-slate-500"><?php echo e(__('No print specification linked.')); ?></p>
                            </template>
                            <dl class="artwork-detail-meta-grid !grid-cols-2">
                                <template x-for="spec in panel.specifications ?? []" :key="spec.label">
                                    <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400" x-text="spec.label"></dt>
                                        <dd class="mt-0.5 text-sm font-medium text-slate-800" x-text="spec.value"></dd>
                                    </div>
                                </template>
                            </dl>
                            <template x-if="panel.context?.description">
                                <p class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-700" x-text="panel.context.description"></p>
                            </template>
                        </section>

                        <section class="p-5">
                            <h3 class="artwork-detail-card__title"><?php echo e(__('Notes & comments')); ?></h3>
                            <div class="space-y-2 text-sm">
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

                    
                    <aside class="flex flex-col bg-slate-50/40 p-5">
                        <h3 class="artwork-detail-card__title"><?php echo e(__('Actions')); ?></h3>
                        <div class="flex flex-col gap-2">
                            <template x-for="(action, idx) in panel.primary_actions ?? []" :key="'a-' + idx">
                                <template x-if="action.type === 'post'">
                                    <form :action="action.url" method="POST" data-erp-desk-form>
                                        <input type="hidden" name="_token" :value="csrf">
                                        <input type="hidden" name="from" value="designer-desk">
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
                                        class="designer-desk-action-btn w-full"
                                        :class="action.variant === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary'"
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