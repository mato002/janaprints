@if (! empty($paymentDetails) || ! empty($paymentQrPlaceholder))
    <table class="jp-doc__payment-area" cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 4mm;">
        <tr>
            <td style="width: 68%; vertical-align: top; padding-right: 3mm;">
                @if (! empty($paymentDetails))
                    <div class="jp-doc__box">
                        <p class="jp-doc__box-title">{{ __('Payment Details') }}</p>
                        @foreach ($paymentDetails as $line)
                            <p class="jp-doc__box-line"><strong>{{ $line['label'] }}:</strong> {{ $line['value'] }}</p>
                        @endforeach
                    </div>
                @endif
            </td>
            <td style="width: 32%; vertical-align: top; padding-left: 3mm;">
                @if (! empty($paymentQrPlaceholder))
                    <div class="jp-doc__qr-placeholder">
                        <p class="jp-doc__qr-placeholder-text">{{ $paymentQrPlaceholder }}</p>
                    </div>
                @endif
            </td>
        </tr>
    </table>
@endif
