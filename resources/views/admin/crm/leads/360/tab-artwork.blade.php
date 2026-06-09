<div class="crm-360__tab-stack">
    @if (! empty($acquisition['attachments']))
        <section class="crm-360__card">
            <h2 class="crm-360__card-title">{{ __('Storefront artwork') }}</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($acquisition['attachments'] as $attachment)
                    <li class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                        <span class="font-medium">{{ $attachment['name'] }}</span>
                        @if (! empty($attachment['preview_url']))
                            <a href="{{ $attachment['preview_url'] }}" class="text-erp-accent hover:underline" target="_blank" rel="noopener">{{ __('Preview') }}</a>
                        @endif
                        @if (! empty($attachment['download_url']))
                            <a href="{{ $attachment['download_url'] }}" class="text-erp-accent hover:underline">{{ __('Download') }}</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Artwork from linked quotations') }}</h2>
        @can('artwork.view')
            <table class="erp-table text-sm w-full">
                <thead>
                    <tr>
                        <th>{{ __('Request') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($artwork as $item)
                        <tr>
                            <td>{{ $item['number'] }}</td>
                            <td>{{ $item['status'] }}</td>
                            <td>{{ $item['date']?->format('d M Y') }}</td>
                            <td>
                                <a href="{{ $item['url'] }}" class="text-erp-accent hover:underline text-sm" data-turbo-frame="erp-main">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500 py-4">{{ __('No artwork requests linked via quotations yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <p class="crm-360__empty-inline">{{ __('You do not have permission to view artwork') }}</p>
        @endcan
    </section>
</div>
