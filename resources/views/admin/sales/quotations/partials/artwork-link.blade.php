@php
    $panel = ($variant ?? 'card') === 'panel';
@endphp

<x-admin.card @class(['mb-6' => ! $panel])>
    @if ($panel)
        <h2 class="mb-4 font-medium text-slate-900">{{ __('Artwork & print specifications') }}</h2>
    @else
        <h3 class="font-medium mb-3">{{ __('Artwork') }}</h3>
    @endif

    @if ($artworkLink['linked'])
        <dl class="mb-4 space-y-3 text-sm">
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Linked artwork') }}</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $artworkLink['linked']['title'] }}</dd>
                <dd class="text-xs text-slate-500">{{ $artworkLink['linked']['number'] }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ __('Status') }}</dt>
                <dd class="mt-1">
                    <x-admin.enum-status-badge :status="$artworkLink['linked']['status']" />
                </dd>
            </div>
        </dl>

        @if (! $artworkLink['linked']['is_approved'])
            <p class="mb-4 text-sm text-amber-700">{{ __('Artwork must be approved before converting this quotation to a sales order.') }}</p>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $artworkLink['linked']['url'] }}" class="erp-btn-secondary text-xs">{{ __('Open artwork') }}</a>
        </div>
    @else
        <p class="mb-4 text-sm text-slate-600">{{ __('Link artwork from a print specification or an approved artwork request before converting to a sales order.') }}</p>
    @endif

    @can('linkArtwork', $quotation)
        @if ($artworkLink['can_link'] && (count($artworkLink['library']) > 0 || count($artworkLink['requests']) > 0))
            <form method="POST" action="{{ route('admin.quotations.link-artwork', $quotation) }}" class="mt-4 space-y-4 border-t border-erp-border pt-4" x-data="{ source: @js(count($artworkLink['library']) > 0 ? 'library' : 'request') }">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Artwork source') }}</label>
                    <select name="artwork_source" class="erp-input w-full max-w-md" x-model="source" required>
                        @if (count($artworkLink['library']) > 0)
                            <option value="library">{{ __('Artwork version (print specification)') }}</option>
                        @endif
                        @if (count($artworkLink['requests']) > 0)
                            <option value="request">{{ __('Approved artwork request') }}</option>
                        @endif
                    </select>
                </div>

                @if (count($artworkLink['library']) > 0)
                    <div x-show="source === 'library'" x-cloak>
                        @include('admin.sales.quotations.partials.artwork-picker-field', [
                            'scopedCustomerId' => $quotation->customer_id,
                        ])
                    </div>
                @endif

                @if (count($artworkLink['requests']) > 0)
                    <div x-show="source === 'request'" x-cloak>
                        <label class="erp-label">{{ __('Artwork request') }}</label>
                        <select name="artwork_request_id" class="erp-input w-full max-w-xl" :required="source === 'request'">
                            <option value="">{{ __('Select artwork request') }}</option>
                            @foreach ($artworkLink['requests'] as $item)
                                <option value="{{ $item['id'] }}">{{ $item['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" class="erp-btn-primary">{{ $artworkLink['linked'] ? __('Change linked artwork') : __('Link artwork') }}</button>
                    @if ($quotation->customer)
                        <a href="{{ route('admin.crm.customers.show', ['customer' => $quotation->customer, 'tab' => 'print-specifications']) }}" class="erp-btn-ghost text-sm">{{ __('Manage print specifications') }}</a>
                    @endif
                </div>
            </form>
        @elseif ($artworkLink['can_link'])
            <form method="POST" action="{{ route('admin.quotations.link-artwork', $quotation) }}" class="mt-4 space-y-4 border-t border-erp-border pt-4">
                @csrf
                <input type="hidden" name="artwork_source" value="library">
                @if ($quotation->customer)
                    @include('admin.sales.quotations.partials.artwork-picker-field', [
                        'scopedCustomerId' => $quotation->customer_id,
                    ])
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="erp-btn-primary">{{ __('Link artwork') }}</button>
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-erp-border p-4 text-sm text-slate-600">
                        <p>{{ __('No artwork is available for this customer yet.') }}</p>
                    </div>
                @endif
            </form>
        @endif
    @endcan
</x-admin.card>
