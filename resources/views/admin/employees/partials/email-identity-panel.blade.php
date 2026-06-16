@php
    $activationStatus = $activationStatus ?? 'none';
    $latestActivation = $latestActivation ?? null;
    $assignedRole = $employee->user?->getRoleNames()->first();
@endphp

<div class="mt-8 rounded-lg border border-gray-200 bg-gray-50 p-5">
    <h3 class="text-sm font-semibold text-gray-900">{{ __('Account activation') }}</h3>

    <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-gray-500">{{ __('Login email') }}</dt>
            <dd class="font-medium text-gray-900">{{ $employee->email ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Activation status') }}</dt>
            <dd class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $activationStatus)) }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Intended system role') }}</dt>
            <dd class="font-medium text-gray-900">{{ $employee->activation_role ?: __('Config fallback') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Assigned ERP role') }}</dt>
            <dd class="font-medium text-gray-900">{{ $assignedRole ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Last invitation sent') }}</dt>
            <dd class="font-medium text-gray-900">
                {{ $latestActivation?->last_invitation_sent_at?->format('Y-m-d H:i') ?: '—' }}
            </dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Activation expires') }}</dt>
            <dd class="font-medium text-gray-900">
                {{ $latestActivation?->expires_at?->format('Y-m-d H:i') ?: '—' }}
            </dd>
        </div>
    </dl>

    @if ($employee->activation_status?->value !== 'activated' && filled($employee->email))
        <div class="mt-4 flex flex-wrap gap-2">
            @if ($activationStatus === 'pending')
                <form method="POST" action="{{ route('admin.employees.resend-activation', $employee) }}">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Resend activation') }}</x-secondary-button>
                </form>
            @endif
            @if (in_array($activationStatus, ['expired', 'pending', 'none'], true))
                <form method="POST" action="{{ route('admin.employees.regenerate-activation', $employee) }}">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Regenerate activation link') }}</x-secondary-button>
                </form>
            @endif
        </div>
    @endif

    @if ($employee->activation_status?->value === 'activated' && ! $assignedRole)
        <p class="mt-4 text-sm text-amber-700">
            {{ __('Activation completed without an ERP role assignment. Assign a role from Users administration.') }}
        </p>
    @endif

    @include('admin.employees.partials.email-readiness-panel', ['readinessChecks' => $readinessChecks ?? []])
</div>
