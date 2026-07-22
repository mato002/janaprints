<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<div
    x-show="panelOpen"
    x-cloak
    class="fixed inset-0 z-40 flex justify-end"
    @keydown.escape.window="closePanel()"
>
    <div class="absolute inset-0 bg-slate-900/30" @click="closePanel()"></div>
    <aside class="relative z-10 flex h-full w-full max-w-lg flex-col border-l border-erp-border bg-white shadow-xl production-operator-panel">
        <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo e(__('Job panel')); ?></p>
                <h2 class="text-lg font-semibold text-erp-primary" x-text="panel?.header?.job_number ?? '—'"></h2>
                <p class="text-sm font-medium text-slate-700" x-text="panel?.header?.status ?? ''"></p>
            </div>
            <button type="button" class="production-operator-btn erp-btn-secondary" @click="closePanel()"><?php echo e(__('Close')); ?></button>
        </div>

        <div class="production-operator-sticky border-b border-erp-border bg-slate-50 px-4 py-3" x-show="panel?.operator_actions?.length">
            <div class="flex flex-wrap gap-2">
                <template x-for="(action, idx) in panel.operator_actions ?? []" :key="idx">
                    <template x-if="action.type === 'post'">
                        <form :action="action.url" method="POST" <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>>
                            <input type="hidden" name="_token" :value="csrf">
                            <?php if($operatorMode): ?>
                                <input type="hidden" name="from" value="production-floor">
                            <?php endif; ?>
                            <button
                                type="submit"
                                class="production-operator-btn"
                                :class="action.variant === 'primary' ? 'erp-btn-primary' : (action.variant === 'ghost' ? 'erp-btn-ghost' : 'erp-btn-secondary')"
                                x-text="action.label"
                            ></button>
                        </form>
                    </template>
                    <template x-if="action.type === 'panel'">
                        <button
                            type="button"
                            class="production-operator-btn"
                            :class="action.variant === 'primary' ? 'erp-btn-primary' : (action.variant === 'ghost' ? 'erp-btn-ghost' : 'erp-btn-secondary')"
                            x-text="action.label"
                            @click="scrollToPanelSection(action.url)"
                        ></button>
                    </template>
                    <template x-if="action.type === 'link'">
                        <a
                            :href="action.url"
                            class="production-operator-btn erp-btn-secondary"
                            <?php if($operatorMode): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
                            x-text="action.label"
                        ></a>
                    </template>
                </template>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4" x-show="!panelLoading">
            <template x-if="panel">
                <div class="space-y-4">
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-slate-500"><?php echo e(__('Customer')); ?></dt><dd class="font-medium" x-text="panel.header.customer ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Product')); ?></dt><dd class="font-medium" x-text="panel.header.product ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Stage')); ?></dt><dd x-text="panel.header.stage ?? '—'"></dd></div>
                        <div><dt class="text-slate-500"><?php echo e(__('Required')); ?></dt><dd x-text="panel.header.required_date ?? '—'"></dd></div>
                    </dl>

                    <div class="flex flex-wrap gap-2">
                        <template x-if="panel.primary_action?.type === 'post'">
                            <form :action="panel.primary_action.url" method="POST" <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>>
                                <input type="hidden" name="_token" :value="csrf">
                                <?php if($operatorMode): ?>
                                    <input type="hidden" name="from" value="production-floor">
                                <?php endif; ?>
                                <button type="submit" class="erp-btn-primary text-sm" x-text="panel.primary_action.label"></button>
                            </form>
                        </template>
                        <template x-if="panel.primary_action?.type === 'panel'">
                            <button type="button" class="erp-btn-primary text-sm" x-text="panel.primary_action.label" @click="scrollToPanelSection(panel.primary_action.url)"></button>
                        </template>
                        <template x-if="panel.primary_action?.type === 'link'">
                            <a :href="panel.primary_action.url" class="erp-btn-primary text-sm" <?php if($operatorMode): ?> data-erp-modal-open <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?> x-text="panel.primary_action.label"></a>
                        </template>
                        <template x-if="panel.header?.label_url">
                            <a :href="panel.header.label_url" target="_blank" rel="noopener" class="erp-btn-secondary text-sm"><?php echo e(__('Print label')); ?></a>
                        </template>
                        <?php if($operatorMode): ?>
                            <template x-if="panel.links?.job">
                                <a :href="panel.links.job" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Preview job')); ?></a>
                            </template>
                            <template x-if="panel.links?.sales_order">
                                <a :href="panel.links.sales_order" class="erp-btn-secondary text-sm" data-erp-modal-open><?php echo e(__('Sales order')); ?></a>
                            </template>
                        <?php else: ?>
                            <template x-if="panel.links?.job">
                                <a :href="panel.links.job" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main"><?php echo e(__('Full job workspace')); ?></a>
                            </template>
                            <template x-if="panel.links?.sales_order">
                                <a :href="panel.links.sales_order" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main"><?php echo e(__('Sales order')); ?></a>
                            </template>
                        <?php endif; ?>
                    </div>

                    <template x-if="panel.blockers?.length">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                            <p class="mb-1 font-medium"><?php echo e(__('Before dispatch')); ?></p>
                            <ul class="list-disc space-y-0.5 pl-4">
                                <template x-for="blocker in panel.blockers" :key="blocker">
                                    <li x-text="blocker"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <section id="outsource" class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
                        <h3 class="mb-2 text-sm font-semibold text-violet-900"><?php echo e(__('At vendor')); ?></h3>
                        <template x-if="panel.outsource?.vendor">
                            <dl class="mb-3 grid grid-cols-2 gap-2 text-xs">
                                <div><dt class="text-slate-500"><?php echo e(__('Vendor')); ?></dt><dd class="font-medium" x-text="panel.outsource.vendor.vendor_name"></dd></div>
                                <div><dt class="text-slate-500"><?php echo e(__('Expected return')); ?></dt><dd x-text="panel.outsource.expected_return ?? '—'"></dd></div>
                                <div><dt class="text-slate-500"><?php echo e(__('Quoted')); ?></dt><dd x-text="panel.outsource.quoted_cost ?? '—'"></dd></div>
                                <div><dt class="text-slate-500"><?php echo e(__('Actual')); ?></dt><dd x-text="panel.outsource.actual_cost ?? '—'"></dd></div>
                            </dl>
                        </template>
                        <template x-if="panel.outsource?.can_outsource">
                            <form :action="panel.outsource.outsource_url" method="POST" class="grid grid-cols-1 gap-2 text-sm" <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>>
                                <input type="hidden" name="_token" :value="csrf">
                                <?php if($operatorMode): ?>
                                    <input type="hidden" name="from" value="production-floor">
                                <?php endif; ?>
                                <div>
                                    <label class="erp-label text-xs"><?php echo e(__('Production vendor')); ?></label>
                                    <select name="outsource_vendor_id" class="erp-select w-full" required>
                                        <template x-for="vendor in panel.outsource.production_vendors" :key="vendor.id">
                                            <option :value="vendor.id" x-text="vendor.vendor_name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="erp-label text-xs"><?php echo e(__('Issue date')); ?></label>
                                        <input type="date" name="outsource_issue_date" class="erp-input w-full" required :value="new Date().toISOString().slice(0, 10)">
                                    </div>
                                    <div>
                                        <label class="erp-label text-xs"><?php echo e(__('Expected return')); ?></label>
                                        <input type="date" name="outsource_expected_return" class="erp-input w-full">
                                    </div>
                                </div>
                                <div>
                                    <label class="erp-label text-xs"><?php echo e(__('Quoted cost')); ?></label>
                                    <input type="number" step="0.01" name="outsource_quoted_cost" class="erp-input w-full">
                                </div>
                                <div>
                                    <label class="erp-label text-xs"><?php echo e(__('Notes')); ?></label>
                                    <textarea name="outsource_notes" class="erp-input w-full" rows="2"></textarea>
                                </div>
                                <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Send to vendor')); ?></button>
                            </form>
                        </template>
                        <template x-if="panel.outsource?.can_return">
                            <form :action="panel.outsource.return_url" method="POST" class="flex flex-wrap items-end gap-2" <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>>
                                <input type="hidden" name="_token" :value="csrf">
                                <?php if($operatorMode): ?>
                                    <input type="hidden" name="from" value="production-floor">
                                <?php endif; ?>
                                <div class="min-w-[10rem] flex-1">
                                    <label class="erp-label text-xs"><?php echo e(__('Actual cost')); ?></label>
                                    <input type="number" step="0.01" name="outsource_actual_cost" class="erp-input w-full">
                                </div>
                                <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Mark returned')); ?></button>
                            </form>
                        </template>
                        <template x-if="!panel.outsource?.vendor && !panel.outsource?.can_outsource && !panel.outsource?.can_return">
                            <p class="text-xs text-slate-500"><?php echo e(__('Not currently at a vendor.')); ?></p>
                        </template>
                    </section>

                    <section id="fulfilment" class="rounded-lg border border-erp-border p-4">
                        <h3 class="mb-2 text-sm font-semibold text-erp-primary"><?php echo e(__('Fulfilment')); ?></h3>
                        <p class="mb-2 text-xs text-slate-600">
                            <?php echo e(__('Status')); ?>:
                            <span x-text="panel.fulfilment?.status_label ?? '—'"></span>
                        </p>
                        <?php if (! ($operatorMode)): ?>
                        <a
                            x-show="panel.links?.job"
                            :href="panel.links.job + '?tab=fulfilment'"
                            class="text-sm text-erp-accent hover:underline"
                            data-turbo-frame="erp-main"
                        ><?php echo e(__('Open fulfilment tab')); ?></a>
                        <?php endif; ?>
                    </section>

                    <section id="quality" class="rounded-lg border border-erp-border p-4">
                        <h3 class="mb-2 text-sm font-semibold text-erp-primary"><?php echo e(__('Quality & handoff')); ?></h3>
                        <p class="mb-2 text-xs text-slate-600">
                            <?php echo e(__('Fulfilment')); ?>:
                            <span x-text="panel.fulfilment?.status_label ?? '—'"></span>
                        </p>
                        <?php if (! ($operatorMode)): ?>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <a
                                x-show="panel.links?.job"
                                :href="panel.links.job + '?tab=quality'"
                                class="text-erp-accent hover:underline"
                                data-turbo-frame="erp-main"
                            ><?php echo e(__('Quality tab')); ?></a>
                            <a
                                x-show="panel.links?.job"
                                :href="panel.links.job + '?tab=fulfilment'"
                                class="text-erp-accent hover:underline"
                                data-turbo-frame="erp-main"
                            ><?php echo e(__('Fulfilment')); ?></a>
                        </div>
                        <?php endif; ?>
                    </section>
                </div>
            </template>
        </div>

        <div class="flex flex-1 items-center justify-center p-8" x-show="panelLoading">
            <p class="text-sm text-slate-500"><?php echo e(__('Loading job…')); ?></p>
        </div>
    </aside>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/floor/partials/job-panel.blade.php ENDPATH**/ ?>