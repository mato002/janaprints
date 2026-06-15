<x-admin-layout :title="__('Email Identity')" :breadcrumbs="[['label' => __('Administration')], ['label' => __('Integrations')], ['label' => __('Email Identity')]]">
    <x-admin.page-header
        :title="__('Email Identity')"
        :description="__('Corporate mailbox configuration, sender addresses, and production readiness for employee onboarding.')"
    />

    @if (config('email_local_testing.enabled') && config('email_local_testing.show_admin_banner'))
        <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            <strong>{{ __('Local email testing mode is ON.') }}</strong>
            {{ __('Onboarding invitations send through :address via Gmail/dev SMTP. Corporate addresses are still generated as :domain — set EMAIL_LOCAL_TESTING=false before production.', [
                'address' => config('email_local_testing.from_address'),
                'domain' => config('mailboxes.domain'),
            ]) }}
        </div>
    @endif

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @php
        $overall = $readinessSummary['overall'] ?? 'warning';
        $overallClasses = match ($overall) {
            'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            'missing' => 'border-red-200 bg-red-50 text-red-900',
            default => 'border-amber-200 bg-amber-50 text-amber-900',
        };
    @endphp

    <div class="mb-6 rounded-lg border px-4 py-3 text-sm {{ $overallClasses }}">
        <strong>{{ __('Production readiness:') }}</strong>
        {{ ucfirst($overall) }}
        · {{ __(':ready ready, :warning warnings, :missing missing', $readinessSummary) }}
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="text-base font-semibold text-gray-900">{{ __('Readiness checklist') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Verify environment configuration before onboarding employees in production.') }}</p>

            <ul class="mt-4 space-y-3">
                @foreach ($readinessChecks as $check)
                    @php
                        $badge = match ($check['status']) {
                            'ready' => 'bg-emerald-100 text-emerald-800',
                            'missing' => 'bg-red-100 text-red-800',
                            default => 'bg-amber-100 text-amber-800',
                        };
                    @endphp
                    <li class="flex items-start justify-between gap-4 rounded-md border border-gray-100 px-3 py-2">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $check['label'] }}</div>
                            <div class="text-xs text-gray-500">{{ $check['detail'] }}</div>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $badge }}">{{ ucfirst($check['status']) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="text-base font-semibold text-gray-900">{{ __('cPanel readiness') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Credential presence only — secrets are never displayed.') }}</p>

            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ __('Host configured') }}</dt>
                    <dd class="font-medium">{{ $cpanelStatus['host_configured'] ? __('Yes') : __('No') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ __('Username configured') }}</dt>
                    <dd class="font-medium">{{ $cpanelStatus['username_configured'] ? __('Yes') : __('No') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ __('API token configured') }}</dt>
                    <dd class="font-medium">{{ $cpanelStatus['api_token_configured'] ? __('Yes') : __('No') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ __('Mock mode') }}</dt>
                    <dd class="font-medium">{{ $cpanelStatus['mock_mode'] ? __('Active') : __('Off') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">{{ __('Default mailbox quota') }}</dt>
                    <dd class="font-medium">{{ $cpanelStatus['mailbox_quota_mb'] }} MB</dd>
                </div>
            </dl>

            @can('integrations.manage')
                <form method="POST" action="{{ route('admin.email-identity.test-cpanel') }}" class="mt-4">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Test cPanel connection') }}</x-secondary-button>
                </form>
            @endcan
        </section>
    </div>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="text-base font-semibold text-gray-900">{{ __('Queue readiness') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Onboarding emails are queued on the emails queue.') }}</p>

        <dl class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-gray-500">{{ __('Current queue connection') }}</dt>
                <dd class="font-medium text-gray-900">{{ $queueGuidance['connection'] }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('Required queue name') }}</dt>
                <dd class="font-medium text-gray-900">{{ $queueGuidance['required_queue'] }}</dd>
            </div>
        </dl>

        @if ($queueGuidance['sync_warning'])
            <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                {{ __('Warning: QUEUE_CONNECTION is sync in production. Onboarding emails will send synchronously and may block requests.') }}
            </p>
        @endif

        <div class="mt-4 rounded-md bg-gray-50 px-3 py-2 text-sm font-mono text-gray-800">
            {{ $queueGuidance['worker_command'] }}
        </div>
    </section>

    <section class="mt-6 rounded-lg border border-gray-200 bg-white p-5">
        <h2 class="text-base font-semibold text-gray-900">{{ __('Department & system mailboxes') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Configured sender addresses from environment — no automatic cPanel provisioning.') }}</p>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Purpose') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Email address') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Configured') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Fallback') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Recommended use') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($mailboxes as $mailbox)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $mailbox['label'] }}</td>
                            <td class="px-3 py-2 text-gray-700">{{ $mailbox['address'] ?: '—' }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-0.5 text-xs {{ $mailbox['configured'] ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $mailbox['configured'] ? __('Yes') : __('No') }}
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                @if ($mailbox['used_fallback'])
                                    <span class="text-amber-700">{{ __('Yes') }}</span>
                                @else
                                    {{ __('No') }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ $mailbox['recommended_use'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
