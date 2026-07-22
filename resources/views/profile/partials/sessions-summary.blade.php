@php
    use App\Enums\UserSessionStatus;
@endphp

<section>
    <x-admin.form-section
        :title="__('Active devices & sessions')"
        :description="__('Devices currently signed in to your account.')"
    >
        <div class="md:col-span-2 space-y-4">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">
                    {{ trans_choice(':count active session|:count active sessions', $activeSessionCount, ['count' => $activeSessionCount]) }}
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('profile.sessions.index') }}" class="erp-btn-secondary text-sm">{{ __('Manage all sessions') }}</a>
                    @if ($activeSessionCount > 1)
                        <form method="POST" action="{{ route('profile.sessions.destroy-others') }}" class="inline" onsubmit="return confirm(@js(__('Log out all other devices?')))">
                            @csrf
                            <button type="submit" class="erp-btn-primary text-sm">{{ __('Logout other devices') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="space-y-2">
                @forelse ($activeSessions->take(5) as $session)
                    @php $isCurrent = $session->isCurrentSession($currentSessionId); @endphp
                    <div class="rounded-lg border border-erp-border px-4 py-3">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-sm font-semibold text-erp-primary">{{ $session->device ?? __('Unknown device') }}</p>
                                    @if ($isCurrent)
                                        <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700">{{ __('This device') }}</span>
                                    @endif
                                    <x-admin.status-badge :variant="$session->status->badgeVariant()">{{ $session->status->label() }}</x-admin.status-badge>
                                </div>
                                <p class="mt-1 text-xs text-slate-600">
                                    {{ $session->browser ?? __('Unknown browser') }}
                                    · {{ $session->platform ?? __('Unknown platform') }}
                                    · {{ $session->ip_address ?? '—' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ __('Last activity') }}: {{ $session->last_activity_at?->diffForHumans() ?? '—' }}
                                </p>
                            </div>
                            @if (! $isCurrent && $session->status === UserSessionStatus::Active)
                                <form method="POST" action="{{ route('profile.sessions.destroy', $session) }}" onsubmit="return confirm(@js(__('Terminate this session?')))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Logout') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('No active sessions recorded yet. Sign out and back in to start session tracking.') }}</p>
                @endforelse
            </div>

            @if ($activeSessions->count() > 5)
                <p class="text-xs text-slate-500">
                    {{ __('Showing 5 of :count active sessions.', ['count' => $activeSessions->count()]) }}
                    <a href="{{ route('profile.sessions.index') }}" class="erp-link">{{ __('View all') }}</a>
                </p>
            @endif
        </div>
    </x-admin.form-section>
</section>
