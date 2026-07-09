<?php

namespace App\Console\Commands;

use App\Support\PublicHash\PublicHashGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicHashAuditStoredUrlsCommand extends Command
{
    protected $signature = 'public-hash:audit-stored-urls
                            {--strict : Exit with failure when numeric leaks are found}';

    protected $description = 'Report-only audit of stored notification/action URLs for numeric ID leaks on migrated routes';

    /**
     * @var list<string>
     */
    protected array $externalNumericPatterns = [
        '#/client/quotations/\d+#',
        '#/client/orders/\d+#',
        '#/client/jobs/\d+#',
        '#/client/invoices/\d+#',
        '#/client/artwork/\d+#',
        '#/payment-receipt/\d+#',
        '#/admin/quotations/list/\d+#',
        '#/admin/sales-orders/list/\d+#',
        '#/admin/invoices/\d+#',
        '#/admin/payments/\d+#',
        '#/admin/production/job-cards/\d+#',
        '#/admin/artwork/requests/\d+#',
        '#/admin/dispatch/delivery-notes/\d+#',
    ];

    public function handle(PublicHashGenerator $generator): int
    {
        $this->info('Stored URL numeric leak audit (report-only; no database rewrites)');
        $this->newLine();

        $issues = 0;
        $rows = [];

        if (Schema::hasTable('erp_notifications') && Schema::hasColumn('erp_notifications', 'action_url')) {
            $notifications = DB::table('erp_notifications')
                ->whereNotNull('action_url')
                ->where('action_url', '!=', '')
                ->select(['id', 'action_url', 'created_at'])
                ->orderByDesc('id')
                ->limit(5000)
                ->get();

            foreach ($notifications as $row) {
                $match = $this->detectNumericLeak((string) $row->action_url);

                if ($match === null) {
                    continue;
                }

                $issues++;
                $rows[] = [
                    'erp_notifications',
                    (string) $row->id,
                    $match,
                    (string) $row->created_at,
                ];
            }
        } else {
            $this->warn('erp_notifications.action_url not available — skipping notification scan.');
        }

        if ($rows === []) {
            $this->line('No numeric leaks detected in sampled stored notification URLs.');
        } else {
            $this->table(['Source', 'Record ID', 'Pattern', 'Created'], array_slice($rows, 0, 50));

            if (count($rows) > 50) {
                $this->warn('Additional matches truncated. Historical numeric URLs may remain during fallback window.');
            }
        }

        $this->newLine();
        $this->line("Issues found: {$issues}");
        $this->line('New notifications should emit public hashes via route() on migrated models.');

        if ($issues > 0 && $this->option('strict')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function detectNumericLeak(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        foreach ($this->externalNumericPatterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return $pattern;
            }
        }

        return null;
    }
}
