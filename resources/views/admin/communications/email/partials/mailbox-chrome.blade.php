@php
    $mailbox = $mailbox ?? ['sent' => 0, 'drafts' => 0, 'queued' => 0, 'needs_attention' => 0];
    $activeFolder = $activeFolder ?? 'sent';
    $searchAction = match ($activeFolder) {
        'drafts' => route('admin.communications.email.drafts.index'),
        'queued' => route('admin.communications.email.queue.index'),
        'needs_attention' => route('admin.communications.email.inbox.index'),
        'customers' => route('admin.communications.email.customers.index'),
        'templates' => route('admin.communications.email.templates.index'),
        default => route('admin.communications.email.sent.index'),
    };
    $folders = [
        ['key' => 'sent', 'label' => __('Sent'), 'route' => 'admin.communications.email.sent.index', 'count' => $mailbox['sent']],
        ['key' => 'drafts', 'label' => __('Drafts'), 'route' => 'admin.communications.email.drafts.index', 'count' => $mailbox['drafts']],
        ['key' => 'queued', 'label' => __('Queued'), 'route' => 'admin.communications.email.queue.index', 'count' => $mailbox['queued']],
        ['key' => 'needs_attention', 'label' => __('Needs attention'), 'route' => 'admin.communications.email.inbox.index', 'count' => $mailbox['needs_attention']],
        ['key' => 'customers', 'label' => __('Customers'), 'route' => 'admin.communications.email.customers.index', 'count' => null],
        ['key' => 'templates', 'label' => __('Templates'), 'route' => 'admin.communications.email.templates.index', 'count' => null],
    ];
@endphp

<div class="mb-4 flex flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-erp-primary">{{ __('Email') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Compose, track, and follow up with customers.') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('create', App\Models\Communications\EmailCampaign::class)
                <a href="{{ route('admin.communications.email.compose') }}" data-turbo-frame="erp-main" class="erp-btn erp-btn--primary">
                    {{ __('+ Compose Email') }}
                </a>
            @endcan
            @if (! in_array($activeFolder, ['templates'], true))
                <form method="GET" action="{{ $searchAction }}" class="min-w-[14rem] flex-1 sm:flex-none">
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        class="erp-input w-full sm:w-64"
                        placeholder="{{ __('Search emails…') }}"
                        aria-label="{{ __('Search emails') }}"
                        data-erp-auto-search
                    >
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <div class="rounded-lg border border-erp-border bg-white px-3 py-2">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Sent') }}</p>
            <p class="text-lg font-semibold tabular-nums text-erp-primary">{{ $mailbox['sent'] }}</p>
        </div>
        <div class="rounded-lg border border-erp-border bg-white px-3 py-2">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Drafts') }}</p>
            <p class="text-lg font-semibold tabular-nums text-erp-primary">{{ $mailbox['drafts'] }}</p>
        </div>
        <div class="rounded-lg border border-erp-border bg-white px-3 py-2">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Queued') }}</p>
            <p class="text-lg font-semibold tabular-nums text-erp-primary">{{ $mailbox['queued'] }}</p>
        </div>
        <div class="rounded-lg border border-erp-border bg-white px-3 py-2">
            <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Needs attention') }}</p>
            <p class="text-lg font-semibold tabular-nums {{ $mailbox['needs_attention'] > 0 ? 'text-red-700' : 'text-erp-primary' }}">{{ $mailbox['needs_attention'] }}</p>
        </div>
    </div>

    <nav class="flex flex-wrap gap-1 border-b border-erp-border pb-px" aria-label="{{ __('Email folders') }}">
        @foreach ($folders as $folder)
            <a
                href="{{ route($folder['route']) }}"
                data-turbo-frame="erp-main"
                @class([
                    'inline-flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium transition-colors',
                    'border-erp-accent text-erp-accent' => $activeFolder === $folder['key'],
                    'border-transparent text-slate-600 hover:text-erp-primary' => $activeFolder !== $folder['key'],
                ])
            >
                {{ $folder['label'] }}
                @if ($folder['count'] !== null)
                    <span @class([
                        'rounded px-1.5 py-0.5 text-[10px] font-semibold tabular-nums',
                        'bg-erp-accent/10 text-erp-accent' => $activeFolder === $folder['key'],
                        'bg-slate-100 text-slate-600' => $activeFolder !== $folder['key'],
                        'bg-red-100 text-red-700' => $folder['key'] === 'needs_attention' && $folder['count'] > 0 && $activeFolder !== $folder['key'],
                    ])>{{ $folder['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</div>
