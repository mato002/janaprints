<x-admin-layout :title="__('Notification Center')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Notification Center')]]">
    <div class="notification-center min-w-0" x-data="notificationCenterWorkspace(@js($bootstrap))">
        <x-admin.page-header
            :title="__('Notification Center')"
            :description="__('Internal ERP alerts for approvals, production, accounting, HR, and system events. No SMS, email, or WhatsApp in this phase.')"
        />

        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <x-admin.stat-card :label="__('Total')" :value="$summary['total']" />
            <x-admin.stat-card :label="__('Unread')" :value="$summary['unread']" />
            <x-admin.stat-card :label="__('Critical')" :value="$summary['critical']" />
            <x-admin.stat-card :label="__('Archived')" :value="$summary['archived']" />
        </div>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach ([
                ['view' => null, 'label' => __('All')],
                ['view' => 'unread', 'label' => __('Unread')],
                ['view' => 'critical', 'label' => __('Critical')],
                ['view' => 'archived', 'label' => __('Archived')],
            ] as $tab)
                <a
                    href="{{ route('admin.communications.notifications.index', array_merge(request()->except('view'), $tab['view'] ? ['view' => $tab['view']] : [])) }}"
                    data-turbo-frame="erp-main"
                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors {{ (request('view') ?: null) === $tab['view'] ? 'border-erp-accent bg-erp-accent/5 text-erp-primary' : 'border-erp-border text-slate-600 hover:border-erp-accent/40' }}"
                >
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>

        <div class="grid gap-4 xl:grid-cols-12">
            <div class="space-y-4 xl:col-span-8">
                <form method="GET" action="{{ route('admin.communications.notifications.index') }}" class="erp-card" data-turbo-frame="erp-main">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="erp-label text-xs">{{ __('Status') }}</label>
                            <select name="status" class="erp-input w-full">
                                <option value="">{{ __('All') }}</option>
                                @foreach (\App\Enums\NotificationReadStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="erp-label text-xs">{{ __('Priority') }}</label>
                            <select name="priority" class="erp-input w-full">
                                <option value="">{{ __('All') }}</option>
                                @foreach (\App\Enums\NotificationPriority::cases() as $priority)
                                    <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="erp-label text-xs">{{ __('Type') }}</label>
                            <select name="type" class="erp-input w-full">
                                <option value="">{{ __('All') }}</option>
                                @foreach (\App\Enums\NotificationType::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($users->isNotEmpty())
                            <div>
                                <label class="erp-label text-xs">{{ __('User') }}</label>
                                <select name="user_id" class="erp-input w-full">
                                    <option value="">{{ __('All users') }}</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" @selected((int) request('user_id') === $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="erp-label text-xs">{{ __('From') }}</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="erp-input w-full">
                        </div>
                        <div>
                            <label class="erp-label text-xs">{{ __('To') }}</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="erp-input w-full">
                        </div>
                    </div>
                    @if (request('view'))
                        <input type="hidden" name="view" value="{{ request('view') }}">
                    @endif
                    <div class="mt-3 flex gap-2">
                        <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm">{{ __('Apply filters') }}</button>
                        <a href="{{ route('admin.communications.notifications.index') }}" class="erp-btn erp-btn--ghost erp-btn--sm" data-turbo-frame="erp-main">{{ __('Clear') }}</a>
                    </div>
                </form>

                <div class="erp-card overflow-hidden p-0">
                    @if ($bootstrap['can']['manage'])
                        <div class="flex flex-wrap items-center gap-2 border-b border-erp-border px-4 py-2">
                            <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="bulkRead()">{{ __('Mark selected read') }}</button>
                            <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="bulkDismiss()">{{ __('Dismiss selected') }}</button>
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="erp-table w-full">
                            <thead>
                                <tr>
                                    @if ($bootstrap['can']['manage'])
                                        <th class="w-8"><input type="checkbox" @change="toggleAll($event)"></th>
                                    @endif
                                    <th>{{ __('Notification') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Priority') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $notification)
                                    <tr class="hover:bg-slate-50/80">
                                        @if ($bootstrap['can']['manage'] && $notification->recipient_user_id === auth()->id())
                                            <td><input type="checkbox" value="{{ $notification->id }}" x-model="selectedIds"></td>
                                        @elseif ($bootstrap['can']['manage'])
                                            <td></td>
                                        @endif
                                        <td>
                                            <p class="font-medium text-erp-primary">{{ $notification->title }}</p>
                                            <p class="text-xs text-slate-600 line-clamp-1">{{ $notification->body }}</p>
                                            @if ($bootstrap['can']['admin'] && $notification->recipient)
                                                <p class="text-[10px] text-slate-400">{{ $notification->recipient->name }}</p>
                                            @endif
                                        </td>
                                        <td class="text-xs">{{ $notification->type->label() }}</td>
                                        <td>
                                            <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase {{ $notification->priority->badgeClass() }}">
                                                {{ $notification->priority->label() }}
                                            </span>
                                        </td>
                                        <td class="text-xs">{{ $notification->readState?->status->label() }}</td>
                                        <td class="text-xs tabular-nums text-slate-500">{{ $notification->created_at?->format('d M Y H:i') }}</td>
                                        <td class="text-right">
                                            @if ($notification->recipient_user_id === auth()->id() && $bootstrap['can']['manage'])
                                                <div class="flex justify-end gap-1">
                                                    @if ($notification->readState?->status === \App\Enums\NotificationReadStatus::Unread)
                                                        <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="markRead({{ $notification->id }})">{{ __('Read') }}</button>
                                                    @endif
                                                    <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="dismiss({{ $notification->id }})">{{ __('Dismiss') }}</button>
                                                    <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="archive({{ $notification->id }})">{{ __('Archive') }}</button>
                                                    @if ($notification->action_url)
                                                        <a href="{{ $notification->action_url }}" class="erp-btn erp-btn--ghost erp-btn--xs" data-turbo-frame="erp-main">{{ __('Open') }}</a>
                                                    @endif
                                                </div>
                                            @elseif ($notification->action_url)
                                                <a href="{{ $notification->action_url }}" class="erp-btn erp-btn--ghost erp-btn--xs" data-turbo-frame="erp-main">{{ __('Open') }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-500">{{ __('No notifications match your filters.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($notifications->hasPages())
                        <div class="border-t border-erp-border px-4 py-3">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-4 xl:col-span-4">
                @if ($prefs)
                    <div class="erp-card">
                        <h2 class="erp-card-title">{{ __('Alert preferences') }}</h2>
                        <p class="text-xs text-slate-500 mb-3">{{ __('Control which alert categories you receive.') }}</p>
                        <form @submit.prevent="savePreferences()" class="space-y-2">
                            @foreach ([
                                'commercial_alerts' => __('Commercial alerts'),
                                'production_alerts' => __('Production alerts'),
                                'accounting_alerts' => __('Accounting alerts'),
                                'hr_alerts' => __('HR alerts'),
                                'system_alerts' => __('System alerts'),
                            ] as $key => $label)
                                <label class="flex items-center justify-between gap-2 rounded-lg border border-erp-border px-3 py-2">
                                    <span class="text-sm text-erp-primary">{{ $label }}</span>
                                    <input type="checkbox" class="rounded border-erp-border text-erp-accent" x-model="preferences['{{ $key }}']">
                                </label>
                            @endforeach
                            <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm mt-2 w-full" :disabled="prefsSaving">
                                {{ __('Save preferences') }}
                            </button>
                        </form>
                    </div>
                @endif

                <div class="erp-card">
                    <h2 class="erp-card-title">{{ __('Recent activity') }}</h2>
                    <ul class="mt-2 space-y-2 text-xs text-slate-600">
                        @foreach ($notifications->take(5) as $notification)
                            <li class="border-b border-erp-border/50 pb-2 last:border-0">
                                <span class="font-medium text-erp-primary">{{ $notification->title }}</span>
                                <span class="text-slate-400"> · {{ $notification->created_at?->diffForHumans() }}</span>
                            </li>
                        @endforeach
                        @if ($notifications->isEmpty())
                            <li class="text-slate-500">{{ __('No recent notifications.') }}</li>
                        @endif
                    </ul>
                </div>

                @if ($bootstrap['can']['create'])
                    <div class="erp-card" x-show="can.create" x-cloak>
                        <h2 class="erp-card-title">{{ __('Send test alert') }}</h2>
                        <p class="text-xs text-slate-500 mb-3">{{ __('Internal delivery only — for verification.') }}</p>
                        <form @submit.prevent="sendTest()" class="space-y-2">
                            @if ($bootstrap['can']['admin'] && $users->isNotEmpty())
                                <div>
                                    <label class="erp-label text-xs">{{ __('Recipient') }}</label>
                                    <select class="erp-input w-full" x-model="testForm.recipient_user_id" required>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="erp-label text-xs">{{ __('Type') }}</label>
                                <select class="erp-input w-full" x-model="testForm.type" required>
                                    <template x-for="t in types" :key="t.value">
                                        <option :value="t.value" x-text="t.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label text-xs">{{ __('Title') }}</label>
                                <input type="text" class="erp-input w-full" x-model="testForm.title" required>
                            </div>
                            <div>
                                <label class="erp-label text-xs">{{ __('Body') }}</label>
                                <textarea class="erp-input w-full" rows="3" x-model="testForm.body" required></textarea>
                            </div>
                            <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm w-full">{{ __('Send') }}</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
