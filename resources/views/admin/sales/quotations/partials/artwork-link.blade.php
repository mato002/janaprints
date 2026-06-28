<x-admin.card class="mb-6">
    <h3 class="font-medium mb-3">{{ __('Artwork') }}</h3>

    @if ($artworkLink['linked'])
        <div class="mb-4 rounded-lg border border-erp-border bg-slate-50 p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-900">{{ $artworkLink['linked']['title'] }}</p>
                    <p class="text-xs text-slate-500">{{ $artworkLink['linked']['number'] }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-admin.enum-status-badge :status="$artworkLink['linked']['status']" />
                    <a href="{{ $artworkLink['linked']['url'] }}" class="erp-btn-ghost text-xs">{{ __('Open artwork') }}</a>
                </div>
            </div>
            @if (! $artworkLink['linked']['is_approved'])
                <p class="mt-2 text-sm text-amber-700">{{ __('Artwork must be approved before converting this quotation to a sales order.') }}</p>
            @endif
        </div>
    @else
        <p class="mb-4 text-sm text-slate-600">{{ __('Link artwork from the customer library or an approved artwork request before converting to a sales order.') }}</p>
    @endif

    @can('linkArtwork', $quotation)
        @if ($artworkLink['can_link'] && (count($artworkLink['library']) > 0 || count($artworkLink['requests']) > 0))
            <form method="POST" action="{{ route('admin.quotations.link-artwork', $quotation) }}" class="space-y-4" x-data="{ source: '{{ count($artworkLink['library']) > 0 ? 'library' : 'request' }}' }">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Artwork source') }}</label>
                    <select name="artwork_source" class="erp-input w-full max-w-md" x-model="source" required>
                        @if (count($artworkLink['library']) > 0)
                            <option value="library">{{ __('Customer artwork library') }}</option>
                        @endif
                        @if (count($artworkLink['requests']) > 0)
                            <option value="request">{{ __('Approved artwork request') }}</option>
                        @endif
                    </select>
                </div>

                @if (count($artworkLink['library']) > 0)
                    <div x-show="source === 'library'" x-cloak>
                        <label class="erp-label">{{ __('Library artwork') }}</label>
                        <select name="customer_artwork_id" class="erp-input w-full max-w-xl" :required="source === 'library'">
                            <option value="">{{ __('Select artwork') }}</option>
                            @foreach ($artworkLink['library'] as $item)
                                <option value="{{ $item['id'] }}">{{ $item['label'] }} — {{ $item['type'] }} @if ($item['uploaded_at']) ({{ $item['uploaded_at'] }}) @endif</option>
                            @endforeach
                        </select>
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
                        <a href="{{ route('admin.crm.customers.show', ['customer' => $quotation->customer, 'tab' => 'artwork']) }}" class="erp-btn-ghost text-sm">{{ __('Manage customer artwork') }}</a>
                    @endif
                </div>
            </form>
        @elseif ($artworkLink['can_link'])
            <div class="rounded-lg border border-dashed border-erp-border p-4 text-sm text-slate-600">
                <p>{{ __('No artwork is available for this customer yet.') }}</p>
                @if ($quotation->customer)
                    <a href="{{ route('admin.crm.customers.show', ['customer' => $quotation->customer, 'tab' => 'artwork']) }}" class="mt-2 inline-block text-erp-accent">{{ __('Upload artwork on customer profile') }}</a>
                @endif
            </div>
        @endif
    @endcan
</x-admin.card>
