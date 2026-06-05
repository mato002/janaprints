<x-admin-layout :title="__('My Sessions')" :breadcrumbs="[['label' => __('Profile'), 'url' => route('profile.edit')], ['label' => __('My Sessions')]]">
    <x-admin.page-header
        :title="__('My Sessions')"
        :description="__('Review devices signed in to your account and sign out remotely when needed.')"
    >
        <x-slot name="actions">
            <a href="{{ route('profile.edit') }}" class="erp-btn-secondary">{{ __('Profile') }}</a>
            @if ($sessions->where('status', \App\Enums\UserSessionStatus::Active)->count() > 1)
                <form method="POST" action="{{ route('profile.sessions.destroy-others') }}" class="inline" onsubmit="return confirm(@js(__('Log out all other devices?')))">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Logout Other Devices') }}</button>
                </form>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($sessions as $session)
            @php $isCurrent = $session->isCurrentSession($currentSessionId); @endphp
            <x-admin.card>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold text-erp-primary">{{ $session->device ?? __('Unknown device') }}</h2>
                            @if ($isCurrent)
                                <span class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700">{{ __('Current Session') }}</span>
                            @endif
                            <x-admin.status-badge :variant="$session->status->badgeVariant()">{{ $session->status->label() }}</x-admin.status-badge>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $session->browser }} · {{ $session->platform }} · {{ $session->ip_address }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ __('Login') }}: {{ $session->login_at?->format('M j, Y g:i A') }}
                            · {{ __('Last activity') }}: {{ $session->last_activity_at?->diffForHumans() }}
                        </p>
                    </div>
                    @if (! $isCurrent && $session->status === \App\Enums\UserSessionStatus::Active)
                        <form method="POST" action="{{ route('profile.sessions.destroy', $session) }}" onsubmit="return confirm(@js(__('Terminate this session?')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="erp-btn-secondary text-sm">{{ __('Logout') }}</button>
                        </form>
                    @endif
                </div>
            </x-admin.card>
        @empty
            <x-admin.empty-state icon="clock" :title="__('No sessions recorded yet')" />
        @endforelse
    </div>
</x-admin-layout>
