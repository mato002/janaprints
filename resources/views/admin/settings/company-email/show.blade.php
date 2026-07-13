@php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $indexUrl = route('admin.settings.company-email.index', $scopeQuery);
    $embedded = WorkspaceEmbed::isEmbedded();
@endphp

<x-admin-layout
    :title="$mailbox['email']"
    :breadcrumbs="$embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Configuration')],
        ['label' => __('Company Email')],
        ['label' => $mailbox['email']],
    ]"
    :use-workspace-navigation="! $embedded"
>
    @unless ($embedded)
        @include('admin.settings.partials.hub-toolbar', [
            'title' => $mailbox['email'],
            'description' => __('Manage password, storage quota, and lifecycle for this company mailbox.'),
            'backUrl' => $indexUrl,
            'backLabel' => __('Company Email'),
        ])
    @endunless

<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <x-admin.card>
            <h2 class="text-base font-semibold text-erp-primary">{{ __('Mailbox details') }}</h2>

            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Email address') }}</dt>
                    <dd class="font-medium text-erp-primary">{{ $mailbox['email'] }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Login') }}</dt>
                    <dd class="font-medium text-erp-primary">{{ $mailbox['login'] }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Disk usage') }}</dt>
                    <dd class="font-medium text-erp-primary">
                        @if ($mailbox['disk_used_mb'] !== null)
                            {{ number_format($mailbox['disk_used_mb'], 2) }} MB
                            @if ($mailbox['disk_used_percent'] !== null)
                                ({{ $mailbox['disk_used_percent'] }}%)
                            @endif
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Quota') }}</dt>
                    <dd class="font-medium text-erp-primary">
                        @if ($mailbox['quota_unlimited'] ?? false)
                            {{ __('Unlimited') }}
                        @elseif ($mailbox['disk_quota_mb'] !== null)
                            {{ number_format($mailbox['disk_quota_mb'], 0).' MB' }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Status') }}</dt>
                    <dd>
                        <x-admin.status-badge :variant="$mailbox['suspended'] ? 'danger' : 'success'">
                            {{ $mailbox['suspended'] ? __('Suspended') : __('Active') }}
                        </x-admin.status-badge>
                    </dd>
                </div>
            </dl>
        </x-admin.card>

        @if ($canManage)
            <div class="space-y-6">
                <x-admin.card>
                    <h2 class="text-base font-semibold text-erp-primary">{{ __('Update password') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Set a new mailbox password in cPanel.') }}</p>

                    <form method="POST" action="{{ route('admin.settings.company-email.update-password', $scopeQuery) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="address" value="{{ $mailbox['email'] }}">

                        <x-admin.password-input
                            id="password"
                            name="password"
                            :label="__('New password')"
                            required
                        />

                        <x-admin.password-input
                            id="password_confirmation"
                            name="password_confirmation"
                            :label="__('Confirm password')"
                            required
                        />

                        <button type="submit" class="erp-btn-primary">{{ __('Update password') }}</button>
                    </form>
                </x-admin.card>

                <x-admin.card>
                    <h2 class="text-base font-semibold text-erp-primary">{{ __('Storage quota') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Adjust the mailbox storage limit in cPanel.') }}</p>

                    @php
                        $quotaUnlimited = (bool) old('unlimited_quota', $mailbox['quota_unlimited'] ?? false);
                        $currentQuotaMb = old('quota_mb', $mailbox['disk_quota_mb'] !== null ? (int) round($mailbox['disk_quota_mb']) : (int) config('mailboxes.cpanel.default_quota_mb', 250));
                    @endphp

                    <form
                        method="POST"
                        action="{{ route('admin.settings.company-email.update-quota', $scopeQuery) }}"
                        class="mt-4 space-y-4"
                        x-data="{ unlimited: @js($quotaUnlimited) }"
                    >
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="address" value="{{ $mailbox['email'] }}">

                        <div>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="unlimited_quota"
                                    value="1"
                                    class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                    x-model="unlimited"
                                    @checked($quotaUnlimited)
                                >
                                {{ __('Unlimited storage') }}
                            </label>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Matches cPanel unlimited quota (0 MB limit).') }}</p>
                        </div>

                        <div x-show="! unlimited" x-cloak>
                            <label for="quota_mb" class="erp-label">{{ __('Quota (MB)') }}</label>
                            <input
                                type="number"
                                name="quota_mb"
                                id="quota_mb"
                                value="{{ $currentQuotaMb }}"
                                min="1"
                                max="10240"
                                class="erp-input mt-1 w-full"
                                :required="! unlimited"
                                :disabled="unlimited"
                            >
                            @error('quota_mb')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                {{ __('Current usage: :usage', [
                                    'usage' => $mailbox['disk_used_mb'] !== null
                                        ? number_format($mailbox['disk_used_mb'], 2).' MB'
                                        : __('Unknown'),
                                ]) }}
                            </p>
                        </div>

                        <button type="submit" class="erp-btn-primary">{{ __('Update quota') }}</button>
                    </form>
                </x-admin.card>
            </div>
        @endif
    </div>

    @if ($canManage)
        <x-admin.card class="mt-6 border-red-200">
            <h2 class="text-base font-semibold text-red-700">{{ __('Danger zone') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Deleting a mailbox removes it permanently from cPanel.') }}</p>

            <form
                method="POST"
                action="{{ route('admin.settings.company-email.destroy', $scopeQuery) }}"
                class="mt-4"
                onsubmit="return confirm(@js(__('Delete :email permanently?', ['email' => $mailbox['email']])))"
            >
                @csrf
                @method('DELETE')
                <input type="hidden" name="address" value="{{ $mailbox['email'] }}">
                <button type="submit" class="erp-btn-danger">{{ __('Delete mailbox') }}</button>
            </form>
            @error('address')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </x-admin.card>
    @endif
</x-admin-layout>
