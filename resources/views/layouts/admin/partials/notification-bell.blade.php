@php
    $bell = $notificationBellBootstrap ?? ['enabled' => false];
@endphp

@if ($bell['enabled'] ?? false)
    <div
        class="relative"
        x-data="erpNotificationBell(@js($bell))"
        @keydown.escape.window="close()"
    >
        <button
            type="button"
            class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-erp-page hover:text-slate-700"
            :class="open ? 'bg-erp-page text-erp-primary' : ''"
            title="{{ __('Notifications') }}"
            aria-label="{{ __('Notifications') }}"
            aria-expanded="false"
            :aria-expanded="open"
            @click="toggle()"
        >
            <x-admin.icon name="bell" class="w-5 h-5" />
            <span
                x-show="unreadCount > 0"
                x-cloak
                class="absolute -right-0.5 -top-0.5 flex min-h-[1.125rem] min-w-[1.125rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white ring-2 ring-white"
                x-text="unreadCount > 99 ? '99+' : unreadCount"
            ></span>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition
            @click.outside="close()"
            class="absolute right-0 z-50 mt-2 w-[min(100vw-2rem,22rem)] overflow-hidden rounded-xl border border-erp-border bg-white shadow-card-hover sm:w-96"
        >
            <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                <h2 class="text-sm font-semibold text-erp-primary">{{ __('Notifications') }}</h2>
                <button
                    type="button"
                    class="text-xs font-medium text-erp-accent hover:underline"
                    x-show="unreadCount > 0"
                    @click="markAllRead()"
                >
                    {{ __('Mark all read') }}
                </button>
            </div>

            <div class="max-h-[min(60vh,20rem)] overflow-y-auto">
                <template x-if="loading">
                    <p class="px-4 py-6 text-center text-sm text-slate-500">{{ __('Loading…') }}</p>
                </template>
                <template x-if="!loading && items.length === 0">
                    <p class="px-4 py-6 text-center text-sm text-slate-500">{{ __('No notifications') }}</p>
                </template>
                <template x-for="item in items" :key="item.id">
                    <div
                        class="border-b border-erp-border/60 px-4 py-3 transition-colors hover:bg-slate-50/80"
                        :class="item.is_unread ? 'bg-erp-accent/[0.03]' : ''"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <button type="button" class="min-w-0 flex-1 text-left" @click="openNotification(item)">
                                <p class="text-sm font-medium text-erp-primary line-clamp-1" x-text="item.title"></p>
                                <p class="mt-0.5 text-xs text-slate-600 line-clamp-2" x-text="item.body"></p>
                            </button>
                            <span
                                class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase"
                                :class="item.priority_badge"
                                x-text="item.priority_label"
                            ></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <span class="text-[10px] text-slate-500" x-text="formatDate(item.created_at)"></span>
                            <button
                                type="button"
                                class="text-[10px] font-medium text-erp-accent hover:underline"
                                x-show="item.is_unread"
                                @click.stop="markRead(item)"
                            >
                                {{ __('Mark read') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="border-t border-erp-border bg-slate-50/50 px-4 py-2.5">
                <a
                    :href="routes.center"
                    data-turbo-frame="erp-main"
                    class="block text-center text-xs font-semibold text-erp-accent hover:underline"
                    @click="close()"
                >
                    {{ __('View all notifications') }}
                </a>
            </div>
        </div>
    </div>
@else
    <button type="button" class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-erp-page hover:text-slate-700" title="{{ __('Notifications') }}" aria-label="{{ __('Notifications') }}" disabled>
        <x-admin.icon name="bell" class="w-5 h-5" />
    </button>
@endif
