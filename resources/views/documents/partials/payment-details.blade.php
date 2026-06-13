@if (! empty($paymentDetails))
    <div class="jp-doc__box">
        <p class="jp-doc__box-title">{{ __('Payment Details') }}</p>
        @foreach ($paymentDetails as $line)
            <p class="jp-doc__box-line"><strong>{{ $line['label'] }}:</strong> {{ $line['value'] }}</p>
        @endforeach
    </div>
@endif
