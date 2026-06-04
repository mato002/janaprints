<section class="exec-panel exec-inbox-cc__section-panel" aria-label="{{ __('Channel distribution') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Channel Distribution') }}</h2>
        <span class="exec-panel__meta">{{ __('Monitored thread mix') }}</span>
    </div>

    @if ($channelTotal === 0)
        <x-admin.exec-empty-state
            :title="__('No channel data yet')"
            :description="__('Last-used channel appears once threads receive messages.')"
            compact
        />
    @else
        <div class="exec-inbox-cc__channel-list">
            @foreach ($channelMix as $channel)
                <div class="exec-inbox-cc__channel-row">
                    <div class="exec-inbox-cc__channel-label">
                        <span>{{ $channel['label'] }}</span>
                        <span class="exec-inbox-cc__channel-pct">{{ $channel['percent'] }}%</span>
                    </div>
                    <div class="exec-progress__track" role="progressbar" aria-valuenow="{{ $channel['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="exec-progress__bar exec-inbox-cc__channel-bar exec-inbox-cc__channel-bar--{{ $channel['key'] }}" style="width: {{ max($channel['percent'], $channel['percent'] > 0 ? 4 : 0) }}%"></div>
                    </div>
                    <span class="exec-inbox-cc__channel-count">{{ trans_choice(':count thread|:count threads', $channel['count'], ['count' => $channel['count']]) }}</span>
                </div>
            @endforeach
        </div>
    @endif
</section>
