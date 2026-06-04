@php
    $collapsed = $collapsed ?? false;
    $sections = [
        'quotations' => __('Quotations'),
        'sales_orders' => __('Sales orders'),
        'artwork' => __('Artwork approvals'),
        'jobs' => __('Production jobs'),
        'invoices' => __('Invoices'),
        'payments' => __('Payments'),
        'credit_notes' => __('Credit notes'),
        'deliveries' => __('Deliveries'),
    ];
@endphp
<div class="space-y-1">
    @foreach ($sections as $key => $title)
        @if (! empty($records[$key]) && count($records[$key]) > 0)
            @if ($collapsed)
                <details class="rounded-lg border border-erp-border bg-white">
                    <summary class="cursor-pointer px-3 py-2 text-xs font-semibold text-slate-700">
                        {{ $title }} <span class="font-normal text-slate-400">({{ count($records[$key]) }})</span>
                    </summary>
                    <ul class="border-t border-erp-border px-3 py-2 space-y-1 text-xs">
                        @foreach ($records[$key] as $row)
                            <li>
                                <a href="{{ $row['view_url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $row['number'] }}</a>
                                <span class="text-slate-400"> · {{ $row['status'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </details>
            @else
                <div class="erp-card p-3">
                    <h4 class="text-xs font-semibold uppercase text-slate-600">{{ $title }}</h4>
                    <ul class="mt-2 space-y-1 text-xs">
                        @foreach ($records[$key] as $row)
                            <li>
                                <a href="{{ $row['view_url'] }}" class="font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ $row['number'] }}</a>
                                <span class="text-slate-400"> · {{ $row['status'] }} · {{ $row['date'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    @endforeach
</div>
