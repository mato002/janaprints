@if (! empty($status['label']))
    <div style="text-align: right;">
        <span class="jp-doc__badge jp-doc__badge--{{ $status['variant'] ?? 'neutral' }}">{{ $status['label'] }}</span>
    </div>
@endif
