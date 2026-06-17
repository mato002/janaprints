<section class="space-y-4">
    <div class="ess-card flex items-start gap-4">
        @if ($overview['photo_url'])
            <img src="{{ $overview['photo_url'] }}" alt="" class="h-20 w-20 shrink-0 rounded-full object-cover ring-2 ring-erp-border">
        @else
            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-erp-surface text-xl font-semibold text-erp-muted">
                {{ strtoupper(substr($overview['name'], 0, 1)) }}
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <h2 class="text-xl font-semibold">{{ $overview['name'] }}</h2>
            <p class="text-sm text-erp-muted">{{ $overview['job_title'] ?? __('Employee') }}</p>
            <p class="mt-1 text-sm">{{ $overview['employee_number'] }}</p>
        </div>
    </div>

    @if ($overview['show_onboarding'])
        <div class="ess-card border-amber-200 bg-amber-50">
            <p class="text-sm font-medium text-amber-900">{{ __('Onboarding in progress') }}</p>
            <a href="{{ route('ess.dashboard', ['tab' => 'onboarding']) }}" class="ess-btn ess-btn--primary mt-3 w-full">{{ __('View onboarding tracker') }}</a>
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2">
        <article class="ess-widget">
            <p class="ess-widget__label">{{ __('Latest payslip') }}</p>
            @if ($dashboard['latest_payslip'])
                <p class="ess-widget__value">KES {{ number_format((float) $dashboard['latest_payslip']->net_pay, 0) }}</p>
                <a href="{{ route('ess.payslips.download', $dashboard['latest_payslip']) }}" class="ess-btn ess-btn--primary mt-3 w-full">{{ __('Download PDF') }}</a>
            @else
                <p class="text-sm text-erp-muted">{{ __('No payslips released yet.') }}</p>
            @endif
        </article>

        <article class="ess-widget">
            <p class="ess-widget__label">{{ __('Employment') }}</p>
            <p class="text-sm">{{ $dashboard['employment']['department'] ?? '—' }}</p>
            <p class="text-sm text-erp-muted">{{ $dashboard['employment']['status'] ?? '—' }}</p>
        </article>

        <article class="ess-widget">
            <p class="ess-widget__label">{{ __('Account status') }}</p>
            <p class="ess-widget__value text-base">{{ $dashboard['account_status'] }}</p>
        </article>

        <article class="ess-widget">
            <p class="ess-widget__label">{{ __('Recent documents') }}</p>
            <p class="ess-widget__value text-base">{{ $dashboard['recent_documents']->count() }}</p>
            <a href="{{ route('ess.dashboard', ['tab' => 'documents']) }}" class="ess-btn ess-btn--ghost mt-3 w-full">{{ __('Open documents') }}</a>
        </article>
    </div>

    <div class="ess-card">
        <h3 class="ess-section-title">{{ __('Employment details') }}</h3>
        <dl class="ess-dl">
            <div><dt>{{ __('Department') }}</dt><dd>{{ $overview['department'] ?? '—' }}</dd></div>
            <div><dt>{{ __('Branch') }}</dt><dd>{{ $overview['branch'] ?? '—' }}</dd></div>
            <div><dt>{{ __('Supervisor') }}</dt><dd>{{ $overview['supervisor'] ?? '—' }}</dd></div>
            <div><dt>{{ __('Employment status') }}</dt><dd>{{ ucfirst(str_replace('_', ' ', $overview['employment_status'] ?? '—')) }}</dd></div>
            <div><dt>{{ __('Employment date') }}</dt><dd>{{ $overview['hire_date']?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt>{{ __('Corporate email') }}</dt><dd class="break-all">{{ $overview['corporate_email'] ?? '—' }}</dd></div>
            <div><dt>{{ __('Phone') }}</dt><dd>{{ $overview['phone'] ?? '—' }}</dd></div>
            <div><dt>{{ __('Mailbox status') }}</dt><dd>{{ $overview['mailbox_status'] ?? '—' }}</dd></div>
        </dl>
    </div>
</section>
