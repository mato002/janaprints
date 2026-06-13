@if (! empty($paymentFooter))
    <div class="jp-doc__payment-footer">
        <p class="jp-doc__payment-footer-title">{{ __('Payment Details') }}:</p>
        @foreach ($paymentFooter as $line)
            <p class="jp-doc__payment-footer-line">{{ $line }}</p>
        @endforeach
    </div>
@endif
