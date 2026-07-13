<x-admin-layout
    :title="__('Shared Inbox')"
    :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Inbox')]]"
    :compact-page="true"
>
    <div
        @class([
            'shared-inbox-page',
            'shared-inbox-page--standalone' => ! \App\Support\Navigation\WorkspaceEmbed::rendersEmbeddedFragment(),
        ])
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
            @include('admin.communications.inbox.partials.top-action-bar')

            @if ($active && $workspaceData)
                <div class="mb-0 flex shrink-0 gap-1 border-b border-slate-100 px-3 py-2 lg:hidden">
                    <button type="button" @click="mobilePanel='list'; closeDrawer()" class="shared-inbox__chip" :class="mobilePanel==='list' && 'shared-inbox__chip--active'">{{ __('Chats') }}</button>
                    <button type="button" @click="mobilePanel='thread'; closeDrawer()" class="shared-inbox__chip" :class="mobilePanel==='thread' && 'shared-inbox__chip--active'">{{ __('Messages') }}</button>
                    <button type="button" @click="openDrawer()" class="shared-inbox__chip" :class="drawerOpen && 'shared-inbox__chip--active'">{{ __('Customer info') }}</button>
                </div>

                <div class="shared-inbox__layout shared-inbox__layout--workspace relative min-h-0 flex-1">
                    <div class="shared-inbox__list-col border-b lg:border-b-0" :class="mobilePanel !== 'list' && 'hidden lg:flex'">
                        @include('admin.communications.inbox.partials.list-panel')
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
                            x-text="drawerOpen ? @js(__('Hide customer info')) : @js(__('Customer info'))"
                        ></button>
                        @include('admin.communications.inbox.partials.thread-panel')
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
                            @include('admin.communications.inbox.partials.context-panel')
                        </div>
                    </div>
                </div>
            @else
                <div class="shared-inbox__layout shared-inbox__layout--split min-h-0 flex-1">
                    <div class="shared-inbox__list-col border-b lg:border-b-0">
                        @include('admin.communications.inbox.partials.list-panel')
                    </div>
                    @include('admin.communications.inbox.partials.empty-state')
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
