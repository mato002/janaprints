<section class="space-y-4">
    <div class="ess-card">
        <h2 class="ess-section-title">{{ __('Account overview') }}</h2>
        <dl class="ess-dl">
            <div><dt>{{ __('Corporate email') }}</dt><dd class="break-all">{{ $security['corporate_email'] }}</dd></div>
            <div><dt>{{ __('Account status') }}</dt><dd>{{ $security['account_status'] }}</dd></div>
            <div><dt>{{ __('Last login') }}</dt><dd>{{ $security['last_login']?->format('d M Y H:i') ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="ess-card">
        <h2 class="ess-section-title">{{ __('Change password') }}</h2>
        <form method="POST" action="{{ route('ess.security.password.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="ess-label" for="current_password">{{ __('Current password') }}</label>
                <input type="password" id="current_password" name="current_password" required class="ess-input w-full" autocomplete="current-password">
            </div>

            <div>
                <label class="ess-label" for="password">{{ __('New password') }}</label>
                <input type="password" id="password" name="password" required class="ess-input w-full" autocomplete="new-password">
            </div>

            <div>
                <label class="ess-label" for="password_confirmation">{{ __('Confirm password') }}</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="ess-input w-full" autocomplete="new-password">
            </div>

            <button type="submit" class="ess-btn ess-btn--primary w-full">{{ __('Update password') }}</button>
        </form>
    </div>

    <div class="ess-card">
        <h2 class="ess-section-title">{{ __('Active sessions') }}</h2>
        <ul class="space-y-2">
            @forelse ($security['sessions'] as $session)
                <li class="rounded-lg border border-erp-border px-3 py-2 text-sm">
                    <p>{{ $session->ip_address ?? __('Unknown IP') }}</p>
                    <p class="text-erp-muted">{{ $session->last_activity_at?->diffForHumans() }}</p>
                </li>
            @empty
                <li class="text-sm text-erp-muted">{{ __('No session records.') }}</li>
            @endforelse
        </ul>

        <form method="POST" action="{{ route('ess.security.sessions.destroy-others') }}" class="mt-4">
            @csrf
            <button type="submit" class="ess-btn ess-btn--ghost w-full">{{ __('Logout other sessions') }}</button>
        </form>
    </div>
</section>
