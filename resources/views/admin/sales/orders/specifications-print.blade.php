<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $salesOrder->order_number }} — {{ __('Specifications') }}</title>
    @include('admin.production.job-cards.partials.job-sheet-styles')
    <style>
        .spec-line { page-break-inside: avoid; }
        .spec-line + .spec-line { border-top: 2px solid #2e3192; }
        .spec-line__head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px 6px;
            align-items: baseline;
        }
        .spec-line__name { font-size: 13px; font-weight: 800; color: #0f172a; }
        .spec-line__meta { font-size: 11px; color: #2e3192; }
        .spec-empty { padding: 10px 14px 14px; color: #64748b; font-style: italic; }
        .spec-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            padding: 0 14px 10px;
        }
        .spec-section { padding: 6px 8px 8px 0; }
        .spec-section h3 {
            color: #e91e8c;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0 0 4px;
        }
        .spec-section dl { margin: 0; }
        .spec-section div {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 11px;
            padding: 1px 0;
        }
        .spec-section dt { color: #64748b; }
        .spec-section dd { margin: 0; font-weight: 600; color: #0f172a; text-align: right; }
        @media print {
            .spec-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body @if ($autoPrint ?? true) onload="window.print()" @endif>
    <div class="no-print job-sheet-toolbar">
        <button type="button" onclick="window.print()">{{ __('Print') }}</button>
    </div>

    <div class="sheet">
        <div class="sheet__header">
            <div class="brand-wrap">
                <img src="{{ asset('images/logo-sidebar.png') }}" alt="" class="brand-logo">
                <div class="brand">
                    {{ $salesOrder->company?->name ?? config('app.name') }}
                    <small>{{ __('Printing & Branding') }}</small>
                </div>
            </div>
            <div class="title">{{ __('Specifications') }}</div>
            <div class="contact">
                @if ($salesOrder->company?->address){{ $salesOrder->company->address }}<br>@endif
                @if ($salesOrder->company?->phone){{ __('Tel') }}: {{ $salesOrder->company->phone }}@endif
                @if ($salesOrder->company?->phone && $salesOrder->company?->email) · @endif
                @if ($salesOrder->company?->email){{ __('Email') }}: {{ $salesOrder->company->email }}@endif
            </div>
        </div>

        <div class="meta">
            <div>
                <div class="meta__label">{{ __('Order') }}</div>
                <div class="meta__value">{{ $salesOrder->order_number }}</div>
            </div>
            <div>
                <div class="meta__label">{{ __('Date') }}</div>
                <div class="meta__value">{{ optional($salesOrder->order_date)->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
            </div>
            <div class="meta__customer">
                <div class="meta__label">{{ __('Customer') }}</div>
                <div class="meta__value">{{ $salesOrder->customer?->company_name ?? '—' }}</div>
            </div>
        </div>

        <div class="section-title">{{ __('Production specifications') }}</div>

        @forelse ($lines as $line)
            @php
                $item = $line['item'];
                $specData = $line['specification'];
                $sections = $specData['sections'] ?? [];
            @endphp
            <div class="spec-line">
                <div class="spec-line__head">
                    <div class="spec-line__name">{{ $item->item_name }}</div>
                    <div class="spec-line__meta">{{ __('Qty') }}: {{ $item->quantity }}</div>
                </div>

                @if (! ($specData['has_specification'] ?? false))
                    <p class="spec-empty">{{ $specData['message'] ?? __('No structured production specification yet.') }}</p>
                @else
                    <div class="spec-grid">
                        @foreach ($sections as $sectionKey => $fields)
                            @if (! empty($fields))
                                <div class="spec-section">
                                    <h3>
                                        {{ match ($sectionKey) {
                                            'product' => __('Product'),
                                            'dimensions' => __('Dimensions'),
                                            'materials' => __('Materials'),
                                            'print' => __('Print'),
                                            'finishing' => __('Finishing'),
                                            'imposition' => __('Imposition'),
                                            'artwork' => __('Artwork'),
                                            'notes' => __('Notes'),
                                            default => ucfirst(str_replace('_', ' ', $sectionKey)),
                                        } }}
                                    </h3>
                                    <dl>
                                        @foreach ($fields as $field)
                                            <div>
                                                <dt>{{ $field['label'] }}</dt>
                                                <dd>{{ $field['value'] ?? '—' }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="spec-empty">{{ __('No line items.') }}</p>
        @endforelse
    </div>
</body>
</html>
