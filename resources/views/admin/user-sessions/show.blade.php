<x-admin-layout
    :title="__('Session Details')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('User Sessions'), 'url' => route('admin.security.sessions.index')],
        ['label' => __('Session #:id', ['id' => $session->id])],
    ]"
>
    <x-admin.page-header
        :title="__('Session #:id', ['id' => $session->id])"
        :description="__('Detailed sign-in context and lifecycle for this session.')"
    >
        <x-slot name="actions">
            <a href="{{ route('admin.security.sessions.index') }}" class="erp-btn-secondary">{{ __('Back to sessions') }}</a>
            @can('terminate', $session)
                @if ($session->status === \App\Enums\UserSessionStatus::Active)
                    <form method="POST" action="{{ route('admin.security.sessions.terminate', $session) }}" class="inline" onsubmit="return confirm(@js(__('Terminate this session?')))">
                        @csrf
                        <button type="submit" class="erp-btn-primary">{{ __('Terminate Session') }}</button>
                    </form>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Identity') }}</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('User') }}</dt><dd class="font-medium text-erp-primary">{{ $session->user?->name }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Email') }}</dt><dd>{{ $session->user?->email }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Role') }}</dt><dd>{{ $session->role_snapshot ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Company') }}</dt><dd>{{ $session->company?->name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Branch') }}</dt><dd>{{ $session->branch?->name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Status') }}</dt>
                    <dd><x-admin.status-badge :variant="$session->status->badgeVariant()">{{ $session->status->label() }}</x-admin.status-badge></dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Device & Network') }}</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('IP Address') }}</dt><dd class="font-mono text-xs">{{ $session->ip_address ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Device') }}</dt><dd>{{ $session->device ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Browser') }}</dt><dd>{{ $session->browser ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Platform') }}</dt><dd>{{ $session->platform ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Location') }}</dt><dd>{{ $session->location ?: __('Available after geo enrichment') }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ __('Laravel Session') }}</dt><dd class="font-mono text-xs break-all">{{ $session->laravel_session_id ?? '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Timeline') }}</h3>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                <div><dt class="text-slate-500">{{ __('Login Time') }}</dt><dd class="mt-1 font-medium">{{ $session->login_at?->format('M j, Y g:i A') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Last Activity') }}</dt><dd class="mt-1 font-medium">{{ $session->last_activity_at?->format('M j, Y g:i A') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Logged Out') }}</dt><dd class="mt-1 font-medium">{{ $session->logged_out_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Revoked') }}</dt><dd class="mt-1 font-medium">{{ $session->revoked_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
            </dl>
            @if ($session->revoked_by)
                <p class="mt-4 text-sm text-slate-600">
                    {{ __('Revoked by :name', ['name' => $session->revokedByUser?->name ?? __('Unknown')]) }}
                    @if ($session->revoke_reason)
                        · {{ $session->revoke_reason }}
                    @endif
                </p>
            @endif
            @if ($session->user_agent)
                <p class="mt-4 rounded-lg bg-slate-50 p-3 font-mono text-xs text-slate-600 break-all">{{ $session->user_agent }}</p>
            @endif
        </x-admin.card>
    </div>
</x-admin-layout>
