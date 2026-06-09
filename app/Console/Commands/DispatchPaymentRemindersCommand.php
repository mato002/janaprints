<?php

namespace App\Console\Commands;

use App\Support\Communications\CommunicationScheduledEventScanner;
use Illuminate\Console\Command;

class DispatchPaymentRemindersCommand extends Command
{
    protected $signature = 'communications:payment-reminders';

    protected $description = 'Dispatch payment reminder domain events for overdue invoices (COM-H5).';

    public function handle(CommunicationScheduledEventScanner $scanner): int
    {
        if (! config('customer_journey_communications.payment_reminders.enabled', true)) {
            $this->warn(__('Payment reminders are disabled in configuration.'));

            return self::SUCCESS;
        }

        $results = $scanner->scan(['invoice_overdue']);

        $this->info(__('Dispatched :count overdue invoice reminder event(s).', [
            'count' => $results['invoice_overdue'],
        ]));

        return self::SUCCESS;
    }
}
