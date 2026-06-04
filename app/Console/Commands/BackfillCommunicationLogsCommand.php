<?php

namespace App\Console\Commands;

use App\Models\Communications\CommunicationLog;
use App\Models\Communications\ErpNotification;
use App\Models\Communications\SmsMessage;
use App\Support\Communications\CommunicationLogService;
use Illuminate\Console\Command;

class BackfillCommunicationLogsCommand extends Command
{
    protected $signature = 'communications:backfill-logs {--company= : Limit to company id}';

    protected $description = 'Backfill communication logs from existing SMS messages and notifications';

    public function handle(CommunicationLogService $logs): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;

        $smsQuery = SmsMessage::query()->with(['campaign.template', 'recipient']);
        $notifQuery = ErpNotification::query()->with('recipient');

        if ($companyId) {
            $smsQuery->where('company_id', $companyId);
            $notifQuery->where('company_id', $companyId);
        }

        $smsCount = 0;
        $smsQuery->orderBy('id')->chunkById(100, function ($messages) use ($logs, &$smsCount) {
            foreach ($messages as $message) {
                if (CommunicationLog::query()
                    ->where('source_type', SmsMessage::class)
                    ->where('source_id', $message->id)
                    ->exists()) {
                    continue;
                }
                $logs->recordFromSmsMessage($message);
                $smsCount++;
            }
        });

        $notifCount = 0;
        $notifQuery->orderBy('id')->chunkById(100, function ($notifications) use ($logs, &$notifCount) {
            foreach ($notifications as $notification) {
                if (CommunicationLog::query()
                    ->where('source_type', ErpNotification::class)
                    ->where('source_id', $notification->id)
                    ->exists()) {
                    continue;
                }
                $logs->recordFromNotification($notification);
                $notifCount++;
            }
        });

        $this->info("Backfilled {$smsCount} SMS log(s) and {$notifCount} notification log(s).");

        return self::SUCCESS;
    }
}
