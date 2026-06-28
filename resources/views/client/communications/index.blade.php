<x-layouts.client :title="__('Communications')" :heading="__('Messages')" :fullMobileChat="! $show_history">
    <div class="client-comms" data-client-comms>
        @unless ($show_history)
            <section
                class="client-chat"
                aria-label="{{ __('Team chat') }}"
                data-client-chat
                data-feed-url="{{ route('client.communications.feed') }}"
                data-feed-fingerprint="{{ $feed_fingerprint }}"
            >
                <header class="client-chat__head">
                    <div class="client-chat__identity">
                        <span class="client-chat__avatar" aria-hidden="true">JP</span>
                        <span class="client-chat__presence" title="{{ __('Team available') }}"></span>
                    </div>
                    <div class="client-chat__identity-text">
                        <h2 class="client-chat__title">{{ __('Jana Prints team') }}</h2>
                        <p class="client-chat__meta">{{ __('Typically replies within business hours') }}</p>
                    </div>
                    <div class="client-chat__head-actions">
                        <a
                            href="{{ route('client.communications.index', ['history' => 1]) }}"
                            class="client-chat__history-link"
                            title="{{ __('Notification history') }}"
                        >
                            <x-client.icon name="inbox" class="h-4 w-4" />
                            <span class="sr-only">{{ __('Notification history') }}</span>
                        </a>
                        <span class="client-chat__live" data-client-chat-live>{{ __('Live') }}</span>
                    </div>
                </header>

                <div class="client-chat__messages-pane">
                    <div class="client-chat__body" id="client-chat-scroll">
                        @include('client.communications.partials.chat-messages', ['events' => $feed])
                    </div>
                </div>

                @include('client.communications.partials.composer')
            </section>
        @else
            <div class="client-comms__toolbar">
                <a
                    href="{{ route('client.communications.index') }}"
                    class="client-comms__history-toggle"
                >
                    <x-client.icon name="arrow-left" class="h-4 w-4" />
                    <span>{{ __('Back to chat') }}</span>
                </a>
            </div>

            <section class="client-panel client-panel--flush" aria-label="{{ __('Notification history') }}">
                <div class="client-table-wrap">
                    <table class="client-table client-table--cards">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Subject') }}</th>
                                <th>{{ __('Channel') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td data-label="{{ __('Date') }}">{{ $log->created_at?->format('M j, Y H:i') }}</td>
                                    <td data-label="{{ __('Category') }}">{{ $communications->categoryLabel($log) }}</td>
                                    <td data-label="{{ __('Subject') }}">{{ $log->subject ?: \Illuminate\Support\Str::limit($log->message_body, 80) }}</td>
                                    <td data-label="{{ __('Channel') }}">{{ $log->channel?->label() ?? $log->channel }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="client-empty">{{ __('No notifications yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            </section>
        @endunless
    </div>
</x-layouts.client>
