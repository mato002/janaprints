<table class="jp-doc__header" cellpadding="0" cellspacing="0" data-pdf-branding-header>
    <tr class="jp-doc__header-top">
        <td class="jp-doc__logo-cell">
            @if (! empty($document['logoDataUri']))
                <img src="{{ $document['logoDataUri'] }}" alt="{{ $document['company']['name'] ?? 'Jana Prints' }}" class="jp-doc__logo">
            @else
                <p class="jp-doc__party-name">{{ $document['company']['name'] ?? 'Jana Prints' }}</p>
            @endif
        </td>
        <td class="jp-doc__title-cell">
            <h1 class="jp-doc__title">{{ $document['title'] }}</h1>
            <p class="jp-doc__number">{{ __('No.') }} {{ $document['documentNumber'] }}</p>
            @if (! empty($document['headerHighlight']['value']))
                <p class="jp-doc__header-highlight-label">{{ $document['headerHighlight']['label'] }}</p>
                <p class="jp-doc__header-highlight-value">{{ $document['headerHighlight']['value'] }}</p>
            @endif
            @if (! empty($document['status']['label']))
                <div class="jp-doc__header-status">
                    @include('documents.partials.status-badge', ['status' => $document['status']])
                </div>
            @endif
        </td>
    </tr>
    <tr class="jp-doc__header-address">
        <td colspan="2">
            <div class="jp-doc__company-lines">
                @if (! empty($document['company']['name']))
                    <p class="jp-doc__company-line jp-doc__company-line--name">{{ $document['company']['name'] }}</p>
                @endif
                @if (! empty($document['company']['address']))
                    <p class="jp-doc__company-line">{{ $document['company']['address'] }}</p>
                @endif
                @if (! empty($document['company']['phone']))
                    <p class="jp-doc__company-line">{{ $document['company']['phone'] }}</p>
                @endif
                @if (! empty($document['company']['website']))
                    <p class="jp-doc__company-line">{{ $document['company']['website'] }}</p>
                @endif
                @if (! empty($document['company']['email']))
                    <p class="jp-doc__company-line">{{ $document['company']['email'] }}</p>
                @endif
            </div>
        </td>
    </tr>
</table>
