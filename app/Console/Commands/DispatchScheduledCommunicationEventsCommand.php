<?php

namespace App\Console\Commands;

use App\Support\Communications\CommunicationScheduledEventScanner;
use Illuminate\Console\Command;

class DispatchScheduledCommunicationEventsCommand extends Command
{
    protected $signature = 'communications:dispatch-scheduled-events';

    protected $description = 'Dispatch scheduled domain communication events (overdue invoices, due follow-ups).';

    public function handle(CommunicationScheduledEventScanner $scanner): int
    {
        $results = $scanner->scan();

        $this->info(__('Dispatched :overdue overdue invoice event(s) and :followups follow-up due event(s).', [
            'overdue' => $results['invoice_overdue'],
            'followups' => $results['follow_up_due'],
        ]));

        return self::SUCCESS;
    }
}
