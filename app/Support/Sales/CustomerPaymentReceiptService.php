<?php

namespace App\Support\Sales;

use App\Enums\CustomerPaymentStatus;
use App\Enums\SmsCampaignSendMode;
use App\Enums\SmsCampaignStatus;
use App\Enums\SmsDeliveryStatus;
use App\Enums\SmsMessageQueueStatus;
use App\Enums\SmsRecipientSource;
use App\Mail\CustomerPaymentReceiptMail;
use App\Models\Communications\SmsCampaign;
use App\Models\Communications\SmsMessage;
use App\Models\Communications\SmsRecipient;
use App\Models\Sales\CustomerPayment;
use App\Support\Communications\CommunicationLogService;
use App\Support\Communications\Sms\SmsCreditService;
use App\Support\Communications\Sms\SmsProviderGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerPaymentReceiptService
{
    public function __construct(
        protected CustomerLedgerService $ledger,
    ) {}

    public function assignReceiptNumber(CustomerPayment $payment): CustomerPayment
    {
        if ($payment->receipt_number) {
            return $payment;
        }

        $payment->update([
            'receipt_number' => 'RCP-'.$payment->payment_number,
        ]);

        return $payment->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function build(CustomerPayment $payment): array
    {
        $this->assertReceiptable($payment);

        $payment->loadMissing(['customer', 'allocations.invoice', 'branch', 'company']);

        if (! $payment->receipt_number) {
            $payment = $this->assignReceiptNumber($payment);
        }

        $invoicesSettled = $payment->allocations->map(fn ($allocation) => [
            'invoice_number' => $allocation->invoice->invoice_number,
            'amount_applied' => round((float) $allocation->amount, 2),
            'balance_remaining' => round((float) $allocation->invoice->balance_due, 2),
        ])->values()->all();

        $customerBalanceRemaining = $this->customerBalanceRemaining($payment);

        return [
            'receipt_number' => $payment->receipt_number,
            'payment_number' => $payment->payment_number,
            'payment_date' => $payment->payment_date->toDateString(),
            'customer_name' => $payment->customer?->company_name ?? __('Unknown'),
            'customer_code' => $payment->customer?->customer_code,
            'payment_method' => $payment->payment_method->label(),
            'amount' => round((float) $payment->amount, 2),
            'currency' => $payment->currency,
            'allocated_amount' => round((float) $payment->allocated_amount, 2),
            'unallocated_amount' => round((float) $payment->unallocated_amount, 2),
            'is_deposit' => $payment->is_deposit,
            'invoices_settled' => $invoicesSettled,
            'balance_remaining' => $customerBalanceRemaining,
            'company_name' => $payment->company?->name ?? config('app.name'),
            'branch_name' => $payment->branch?->name,
            'reference' => $payment->reference ?? $payment->bank_reference ?? $payment->mpesa_reference,
            'public_url' => $this->signedPublicUrl($payment),
        ];
    }

    public function signedPublicUrl(CustomerPayment $payment): string
    {
        return URL::temporarySignedRoute(
            'public.payment-receipt.show',
            now()->addDays(30),
            ['payment' => $payment->id],
        );
    }

    public function downloadPdf(CustomerPayment $payment): StreamedResponse
    {
        $receipt = $this->build($payment);

        $html = view('admin.sales.payments.receipt-pdf', [
            'payment' => $payment,
            'receipt' => $receipt,
        ])->render();

        $filename = $receipt['receipt_number'].'.html';

        return response()->streamDownload(fn () => print($html), $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    public function sendEmail(CustomerPayment $payment): bool
    {
        $this->assertReceiptable($payment);

        $email = $payment->customer?->email;

        if (! $email) {
            throw ValidationException::withMessages([
                'email' => __('Customer does not have an email address on file.'),
            ]);
        }

        $receipt = $this->build($payment);

        Mail::to($email)->send(new CustomerPaymentReceiptMail($payment, $receipt));

        $payment->update(['receipt_emailed_at' => now()]);

        return true;
    }

    public function sendSmsLink(CustomerPayment $payment): bool
    {
        $this->assertReceiptable($payment);

        $phone = $payment->customer?->phone;

        if (! $phone) {
            throw ValidationException::withMessages([
                'phone' => __('Customer does not have a phone number on file.'),
            ]);
        }

        $receipt = $this->build($payment);
        $body = __('Jana Prints: Payment receipt :number for :amount :currency. View: :link', [
            'number' => $receipt['receipt_number'],
            'amount' => number_format($receipt['amount'], 2),
            'currency' => $receipt['currency'],
            'link' => $receipt['public_url'],
        ]);

        $segments = (int) ceil(strlen($body) / 160);
        $actorId = $payment->posted_by ?? $payment->created_by;
        $balance = app(SmsCreditService::class)->balanceFor($payment->company_id);

        $message = DB::transaction(function () use ($payment, $phone, $body, $segments, $actorId, $balance, $receipt) {
            $campaign = SmsCampaign::query()->create([
                'company_id' => $payment->company_id,
                'branch_id' => $payment->branch_id,
                'campaign_code' => $this->nextSmsCampaignCode($payment->company_id),
                'name' => __('Payment receipt :number', ['number' => $receipt['receipt_number']]),
                'message_template' => $body,
                'send_mode' => SmsCampaignSendMode::Immediate,
                'status' => SmsCampaignStatus::Sending,
                'recipient_source' => SmsRecipientSource::Customers,
                'character_count' => strlen($body),
                'estimated_segments' => $segments,
                'cost_per_sms' => $balance->cost_per_sms,
                'total_recipients' => 1,
                'created_by' => $actorId,
                'sent_by' => $actorId,
                'started_at' => now(),
                'sent_at' => now(),
            ]);

            $recipient = SmsRecipient::query()->create([
                'sms_campaign_id' => $campaign->id,
                'source_type' => 'customer',
                'source_id' => $payment->customer_id,
                'phone_number' => $phone,
                'display_name' => $payment->customer?->company_name,
            ]);

            return SmsMessage::query()->create([
                'sms_campaign_id' => $campaign->id,
                'sms_recipient_id' => $recipient->id,
                'company_id' => $payment->company_id,
                'branch_id' => $payment->branch_id,
                'phone_number' => $phone,
                'message_body' => $body,
                'queue_status' => SmsMessageQueueStatus::Queued,
                'delivery_status' => SmsDeliveryStatus::Queued,
                'segments_count' => $segments,
                'character_count' => strlen($body),
                'credit_cost' => (float) $balance->cost_per_sms * $segments,
            ]);
        });

        $result = app(SmsProviderGateway::class)->send($message);

        $message->update([
            'queue_status' => SmsMessageQueueStatus::Sent,
            'delivery_status' => $result['delivery_status'],
            'sent_at' => now(),
            'delivered_at' => $result['delivery_status'] === SmsDeliveryStatus::Delivered ? now() : null,
        ]);

        $message->campaign->update([
            'status' => SmsCampaignStatus::Completed,
            'completed_at' => now(),
            'sent_count' => $result['success'] ? 1 : 0,
            'failed_count' => $result['success'] ? 0 : 1,
            'actual_cost' => $result['success'] ? (float) $message->credit_cost : 0,
        ]);

        app(CommunicationLogService::class)->recordFromSmsMessage($message->fresh());

        $payment->update(['receipt_sms_sent_at' => now()]);

        return $result['success'];
    }

    protected function customerBalanceRemaining(CustomerPayment $payment): float
    {
        if (! $payment->customer_id) {
            return 0.0;
        }

        return $this->ledger->closingBalance($payment->customer_id);
    }

    protected function assertReceiptable(CustomerPayment $payment): void
    {
        if ($payment->status !== CustomerPaymentStatus::Posted) {
            throw ValidationException::withMessages([
                'payment' => __('Receipts are only available for posted payments.'),
            ]);
        }
    }

    protected function nextSmsCampaignCode(int $companyId): string
    {
        $count = SmsCampaign::query()->where('company_id', $companyId)->count() + 1;

        return 'SMS-'.now()->format('ymd').'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
