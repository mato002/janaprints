<header class="crm-360__header mb-4">
    <div class="crm-360__header-main">
        <div class="crm-360__identity">
            <a href="{{ route('admin.public-quote-requests.index') }}" class="crm-360__back" data-turbo-frame="erp-main">
                ← {{ __('Back to Quote Requests') }}
            </a>
            <h1 class="crm-360__title">{{ __('Quote Request') }} #{{ $workspace['reference'] }}</h1>
            <p class="crm-360__subtitle">{{ $quoteRequest->service_needed }} · {{ $quoteRequest->name }}</p>
            <span class="crm-360__status crm-360__status--{{ $quoteRequest->status->value === 'pending' ? 'prospect' : ($quoteRequest->status->value === 'quoted' ? 'active' : 'inactive') }}">
                {{ $quoteRequest->status->workspaceLabel() }}
            </span>
        </div>

        <div class="crm-360__action-bar flex-wrap">
            @foreach ($workspace['workflow_actions'] as $action)
                @if (! empty($action['onclick']))
                    <button type="button" class="crm-360__btn crm-360__btn--ghost" onclick="{{ $action['onclick'] }}">{{ $action['label'] }}</button>
                @else
                    <a
                        href="{{ $action['url'] }}"
                        class="crm-360__btn {{ match ($action['variant'] ?? 'outline') {
                            'primary' => 'crm-360__btn--primary',
                            'ghost' => 'crm-360__btn--ghost',
                            default => 'crm-360__btn--outline',
                        } }}"
                        @if (str_starts_with($action['url'], 'mailto:')) target="_blank" rel="noopener" @endif
                        data-turbo-frame="erp-main"
                    >{{ $action['label'] }}</a>
                @endif
            @endforeach
        </div>
    </div>
</header>
