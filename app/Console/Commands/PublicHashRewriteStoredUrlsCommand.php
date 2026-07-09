<?php

namespace App\Console\Commands;

use App\Support\PublicHash\PublicHashGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicHashRewriteStoredUrlsCommand extends Command
{
    protected $signature = 'public-hash:rewrite-stored-urls
                            {--apply : Persist rewritten URLs (default is dry-run)}
                            {--limit=5000 : Maximum rows to scan}';

    protected $description = 'Rewrite stored notification action URLs from numeric IDs to public hashes (Tier 1/2 patterns only)';

    /**
     * @var array<string, array{pattern: string, model: class-string<Model>}>
     */
    protected array $patterns = [
        'admin_quotation' => [
            'pattern' => '#/admin/quotations/list/(\d+)(?:/|$)#',
            'model' => \App\Models\Sales\Quotation::class,
        ],
        'admin_sales_order' => [
            'pattern' => '#/admin/sales-orders/list/(\d+)(?:/|$)#',
            'model' => \App\Models\Sales\SalesOrder::class,
        ],
        'admin_invoice' => [
            'pattern' => '#/admin/invoices/(\d+)(?:/|$)#',
            'model' => \App\Models\Sales\CustomerInvoice::class,
        ],
        'admin_payment' => [
            'pattern' => '#/admin/payments/(\d+)(?:/|$)#',
            'model' => \App\Models\Sales\CustomerPayment::class,
        ],
        'admin_job_card' => [
            'pattern' => '#/admin/production/job-cards/(\d+)(?:/|$)#',
            'model' => \App\Models\Production\ProductionJobCard::class,
        ],
        'admin_customer' => [
            'pattern' => '#/admin/crm/customers/(\d+)(?:/|$)#',
            'model' => \App\Models\Crm\Customer::class,
        ],
        'client_quotation' => [
            'pattern' => '#/client/quotations/(\d+)(?:/|$)#',
            'model' => \App\Models\Sales\Quotation::class,
        ],
        'client_order' => [
            'pattern' => '#/client/orders/(\d+)(?:/|$)#',
            'model' => \App\Models\Sales\SalesOrder::class,
        ],
        'client_job' => [
            'pattern' => '#/client/jobs/(\d+)(?:/|$)#',
            'model' => \App\Models\Production\ProductionJobCard::class,
        ],
        'client_invoice' => [
            'pattern' => '#/client/invoices/(\d+)(?:/|$)#',
            'model' => \App\Models\Sales\CustomerInvoice::class,
        ],
    ];

    public function handle(PublicHashGenerator $generator): int
    {
        $dryRun = ! $this->option('apply');
        $this->info($dryRun
            ? 'Stored URL rewrite (dry-run — no database changes)'
            : 'Stored URL rewrite (APPLY mode)');

        if (! Schema::hasTable('erp_notifications') || ! Schema::hasColumn('erp_notifications', 'action_url')) {
            $this->warn('erp_notifications.action_url not available.');

            return self::SUCCESS;
        }

        $scanned = 0;
        $rewritable = 0;
        $rewritten = 0;
        $skipped = 0;

        $rows = DB::table('erp_notifications')
            ->whereNotNull('action_url')
            ->where('action_url', '!=', '')
            ->orderByDesc('id')
            ->limit((int) $this->option('limit'))
            ->get(['id', 'action_url']);

        foreach ($rows as $row) {
            $scanned++;
            $original = (string) $row->action_url;
            $updated = $this->rewriteUrl($original);

            if ($updated === null || $updated === $original) {
                $skipped++;

                continue;
            }

            $rewritable++;

            if ($dryRun) {
                $this->line("Would rewrite #{$row->id}: {$original} -> {$updated}");
            } else {
                DB::table('erp_notifications')
                    ->where('id', $row->id)
                    ->update(['action_url' => $updated]);
                $rewritten++;
            }
        }

        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Scanned', $scanned],
                ['Rewritable', $rewritable],
                ['Skipped / unchanged', $skipped],
                ['Rewritten', $dryRun ? 0 : $rewritten],
            ],
        );

        if ($dryRun && $rewritable > 0) {
            $this->warn('Re-run with --apply to persist changes.');
        }

        return self::SUCCESS;
    }

    protected function rewriteUrl(string $url): ?string
    {
        if (str_contains($url, 'signature=')) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?? $url;

        foreach ($this->patterns as $config) {
            if (! preg_match($config['pattern'], $path, $matches)) {
                continue;
            }

            $numericId = $matches[1];
            /** @var Model|null $model */
            $model = $config['model']::query()->find($numericId);

            if ($model === null || empty($model->public_id)) {
                return null;
            }

            $newPath = preg_replace(
                '/'.preg_quote((string) $numericId, '/').'/',
                (string) $model->public_id,
                $path,
                1,
            );

            return $this->rebuildUrl($url, $newPath);
        }

        return null;
    }

    protected function rebuildUrl(string $original, string $path): string
    {
        $query = parse_url($original, PHP_URL_QUERY);
        $fragment = parse_url($original, PHP_URL_FRAGMENT);
        $scheme = parse_url($original, PHP_URL_SCHEME);
        $host = parse_url($original, PHP_URL_HOST);
        $port = parse_url($original, PHP_URL_PORT);

        if ($host) {
            $rebuilt = ($scheme ? $scheme.'://' : '').$host.($port ? ':'.$port : '').$path;
        } else {
            $rebuilt = $path;
        }

        if ($query) {
            $rebuilt .= '?'.$query;
        }

        if ($fragment) {
            $rebuilt .= '#'.$fragment;
        }

        return $rebuilt;
    }
}
