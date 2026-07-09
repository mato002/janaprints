<?php

namespace App\Console\Commands;

use App\Support\PublicHash\PublicHashCertificationService;
use Illuminate\Console\Command;

class PublicHashCertifyCommand extends Command
{
    protected $signature = 'public-hash:certify
                            {--strict : Exit non-zero when certification fails}
                            {--json : Output machine-readable JSON}';

    protected $description = 'Certify public hash readiness for all configured route-exposed models';

    public function handle(PublicHashCertificationService $certification): int
    {
        $result = $certification->run();

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return ($result['passed'] || ! $this->option('strict')) ? self::SUCCESS : self::FAILURE;
        }

        $this->info('Public Hash Certification');
        $this->newLine();

        $rows = collect($result['checks'])->map(fn (array $check) => [
            $check['key'],
            $check['passed'] ? 'PASS' : 'FAIL',
            $check['message'],
        ])->all();

        $this->table(['Check', 'Status', 'Message'], $rows);

        $this->newLine();
        $this->line('Models certified: '.$result['summary']['model_count']);
        $this->line('Checks: '.$result['summary']['check_count']);
        $this->line('Failed: '.$result['summary']['failed_count']);
        $this->line('Numeric fallback: '.($result['summary']['numeric_fallback_enabled'] ? 'enabled' : 'disabled'));

        if ($result['passed']) {
            $this->info('PUBLIC HASH SECURITY CERTIFIED');
        } else {
            $this->error('REMEDIATION REQUIRED');
        }

        if (! $result['passed'] && $this->option('strict')) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
