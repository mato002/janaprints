<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve(['title' => __('Shared Inbox'),'breadcrumbs' => [['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Inbox')]],'compactPage' => true] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'shared-inbox-page',
            'shared-inbox-page--standalone' => ! \App\Support\Navigation\WorkspaceEmbed::rendersEmbeddedFragment(),
        ]); ?>"
        x-data="{
            mobilePanel: 'thread',
            drawerOpen: false,
            newConvoOpen: false,
            ctxTab: 'summary',
            openDrawer(tab) {
                if (tab) { this.ctxTab = tab; }
                this.drawerOpen = true;
                this.mobilePanel = 'context';
            },
            closeDrawer() {
                this.drawerOpen = false;
                this.mobilePanel = 'thread';
            },
        }"
    >
        <div class="shared-inbox h-full min-h-0">
            <?php echo $__env->make('admin.communications.inbox.partials.top-action-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php if($active && $workspaceData): ?>
                <div class="mb-0 flex shrink-0 gap-1 border-b border-slate-100 px-3 py-2 lg:hidden">
                    <button type="button" @click="mobilePanel='list'; closeDrawer()" class="shared-inbox__chip" :class="mobilePanel==='list' && 'shared-inbox__chip--active'"><?php echo e(__('Chats')); ?></button>
                    <button type="button" @click="mobilePanel='thread'; closeDrawer()" class="shared-inbox__chip" :class="mobilePanel==='thread' && 'shared-inbox__chip--active'"><?php echo e(__('Messages')); ?></button>
                    <button type="button" @click="openDrawer()" class="shared-inbox__chip" :class="drawerOpen && 'shared-inbox__chip--active'"><?php echo e(__('Customer info')); ?></button>
                </div>

                <div class="shared-inbox__layout shared-inbox__layout--workspace relative min-h-0 flex-1">
                    <div class="shared-inbox__list-col border-b lg:border-b-0" :class="mobilePanel !== 'list' && 'hidden lg:flex'">
                        <?php echo $__env->make('admin.communications.inbox.partials.list-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <div
                        class="shared-inbox__thread-col relative border-b lg:border-b-0"
                        :class="[
                            (mobilePanel !== 'thread' && mobilePanel !== 'context') && 'hidden lg:flex',
                            drawerOpen && 'shared-inbox__thread-col--drawer-open',
                        ]"
                        @open-attachments-tab.window="openDrawer('files')"
                        @open-notes-tab.window="openDrawer('notes')"
                        @open-manage-tab.window="openDrawer('manage')"
                    >
                        <button
                            type="button"
                            class="shared-inbox__drawer-toggle"
                            @click="drawerOpen ? closeDrawer() : openDrawer()"
                            x-text="drawerOpen ? <?php echo \Illuminate\Support\Js::from(__('Hide customer info'))->toHtml() ?> : <?php echo \Illuminate\Support\Js::from(__('Customer info'))->toHtml() ?>"
                        ></button>
                        <?php echo $__env->make('admin.communications.inbox.partials.thread-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <div
                            x-show="drawerOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="shared-inbox__drawer-backdrop"
                            @click="closeDrawer()"
                            aria-hidden="true"
                        ></div>
                        <div
                            x-show="drawerOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="translate-x-full"
                            x-transition:enter-end="translate-x-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="translate-x-0"
                            x-transition:leave-end="translate-x-full"
                            class="shared-inbox__drawer-col"
                            @keydown.escape.window="closeDrawer()"
                        >
                            <?php echo $__env->make('admin.communications.inbox.partials.context-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="shared-inbox__layout shared-inbox__layout--split min-h-0 flex-1">
                    <div class="shared-inbox__list-col border-b lg:border-b-0">
                        <?php echo $__env->make('admin.communications.inbox.partials.list-panel', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <?php echo $__env->make('admin.communications.inbox.partials.empty-state', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\communications\inbox\index.blade.php ENDPATH**/ ?>