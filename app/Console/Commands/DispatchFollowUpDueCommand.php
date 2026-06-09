<?php

namespace App\Console\Commands;

use App\Support\Communications\CommunicationScheduledEventScanner;
use Illuminate\Console\Command;

class DispatchFollowUpDueCommand extends Command
{
    protected $signature = 'communications:follow-up-due';

    protected $description = 'Dispatch follow-up due events with staff alerts and optional customer reminders (COM-H6).';

    public function handle(CommunicationScheduledEventScanner $scanner): int
    {
        if (! config('customer_journey_communications.follow_up_due.enabled', true)) {
            $this->warn(__('Follow-up automation is disabled in configuration.'));

            return self::SUCCESS;
        }

        $results = $scanner->scan(['follow_up_due']);

        $this->info(__('Dispatched :count follow-up due event(s).', [
            'count' => $results['follow_up_due'],
        ]));

        return self::SUCCESS;
    }
}
