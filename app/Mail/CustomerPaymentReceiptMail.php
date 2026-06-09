<?php

namespace App\Mail;

use App\Models\Sales\CustomerPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $receipt
     */
    public function __construct(
        public CustomerPayment $payment,
        public array $receipt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Payment receipt :number', ['number' => $this->receipt['receipt_number']]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.customer-payment-receipt',
            with: [
                'payment' => $this->payment,
                'receipt' => $this->receipt,
            ],
        );
    }
}
