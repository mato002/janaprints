@php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $embedded = WorkspaceEmbed::isEmbedded();
@endphp

<x-admin-layout
    :title="__('Company Email')"
    :breadcrumbs="$embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Company Email')],
    ]"
    :use-workspace-navigation="! $embedded"
>
    @unless ($embedded)
        @include('admin.settings.partials.hub-toolbar', [
            'title' => __('Company Email'),
            'description' => __('Create and manage company mailboxes through cPanel without leaving the dashboard.'),
            'backUrl' => $hubBackUrl,
        ])
    @endunless

    @include('admin.settings.partials.scope-selector', [
        'action' => route('admin.settings.company-email.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide default'),
    ])

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <x-admin.card class="xl:col-span-2">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-base font-semibold text-erp-primary">{{ __('cPanel connection') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Mailbox operations use the server credentials configured in your environment.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.company-email.test-connection', $scopeQuery) }}">
                    @csrf
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Test connection') }}</button>
                </form>
            </div>

            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">{{ __('Host') }}</dt>
                    <dd class="font-medium text-erp-primary">{{ $connection['host'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Domain') }}</dt>
                    <dd class="font-medium text-erp-primary">{{ $connection['domain'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Username') }}</dt>
                    <dd class="font-medium text-erp-primary">{{ $connection['username'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('API token') }}</dt>
                    <dd class="font-medium text-erp-primary">
                        {{ $connection['token_configured'] ? __('Configured') : __('Missing') }}
                    </dd>
                </div>
            </dl>

            @unless ($connection['configured'])
                <p class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    {{ __('Set CPANEL_HOST, CPANEL_USERNAME, CPANEL_API_TOKEN, and MAILBOX_DOMAIN in your environment to enable mailbox management.') }}
                </p>
            @endunless
        </x-admin.card>

        <x-admin.card>
            <h2 class="text-base font-semibold text-erp-primary">{{ __('Quick actions') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Default mailbox quota: :quota MB', ['quota' => number_format($defaultQuotaMb)]) }}</p>

            @if ($canManage && $connection['configured'])
                <a href="{{ route('admin.settings.company-email.create', $scopeQuery) }}" class="erp-btn-primary mt-4 inline-flex w-full justify-center">
                    {{ __('Create mailbox') }}
                </a>
            @else
                <p class="mt-4 text-sm text-slate-500">
                    {{ $canManage ? __('Complete cPanel configuration before creating mailboxes.') : __('You have view-only access.') }}
                </p>
            @endif
        </x-admin.card>
    </div>

    <x-admin.card>
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-erp-primary">{{ __('Company mailboxes') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Mailboxes provisioned on :domain', ['domain' => $connection['domain'] ?? __('your domain')]) }}</p>
            </div>
            <span class="rounded-full bg-erp-page px-2.5 py-1 text-xs font-medium text-slate-600">
                {{ trans_choice(':count mailbox|:count mailboxes', count($mailboxes), ['count' => count($mailboxes)]) }}
            </span>
        </div>

        @if ($loadError)
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                {{ __('Unable to load mailboxes: :message', ['message' => $loadError]) }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3">{{ __('Email address') }}</th>
                        <th class="py-3 px-2">{{ __('Usage') }}</th>
                        <th class="py-3 px-2">{{ __('Quota') }}</th>
                        <th class="py-3 px-2">{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mailboxes as $mailbox)
                        <tr>
                            <td class="py-3 pr-3 font-medium text-erp-primary">{{ $mailbox['email'] }}</td>
                            <td class="py-3 px-2 text-slate-600">
                                @if ($mailbox['disk_used_mb'] !== null)
                                    {{ number_format($mailbox['disk_used_mb'], 2) }} MB
                                    @if ($mailbox['disk_used_percent'] !== null)
                                        ({{ $mailbox['disk_used_percent'] }}%)
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-2 text-slate-600">
                                @if ($mailbox['quota_unlimited'] ?? false)
                                    {{ __('Unlimited') }}
                                @elseif ($mailbox['disk_quota_mb'] !== null)
                                    {{ number_format($mailbox['disk_quota_mb'], 0).' MB' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="py-3 px-2">
                                <x-admin.status-badge :variant="$mailbox['suspended'] ? 'danger' : 'success'">
                                    {{ $mailbox['suspended'] ? __('Suspended') : __('Active') }}
                                </x-admin.status-badge>
                            </td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :href="route('admin.settings.company-email.show', ['address' => $mailbox['email']] + $scopeQuery)">
                                        {{ __('Manage') }}
                                    </x-admin.table-row-action>
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">
                                @if ($connection['configured'])
                                    {{ __('No mailboxes found on this domain.') }}
                                @else
                                    {{ __('Configure cPanel credentials to list company mailboxes.') }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
