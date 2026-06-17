<?php

namespace App\Console\Commands;

use App\Support\Communications\Email\EmailAttachmentIntegrityInspector;
use Illuminate\Console\Command;

class InspectEmailAttachmentsCommand extends Command
{
    protected $signature = 'communications:inspect-attachments {--company= : Limit inspection to a company ID}';

    protected $description = 'Inspect email attachment storage integrity (read-only)';

    public function handle(EmailAttachmentIntegrityInspector $inspector): int
    {
        $companyId = $this->option('company') ? (int) $this->option('company') : null;

        $this->info(__('Email Attachment Integrity Inspection'));
        $this->newLine();

        $report = $inspector->inspect($companyId);

        $this->line(__('Total attachments with paths: :count', ['count' => $report['total']]));
        $this->line(__('Document PDFs: :count', ['count' => $report['document_pdfs']]));
        $this->line(__('Missing files: :count', ['count' => $report['missing_files']]));
        $this->line(__('Broken morph links: :count', ['count' => $report['broken_morphs']]));
        $this->newLine();

        foreach ($report['issues'] as $issue) {
            $this->warn(sprintf('[#%d] %s — %s', $issue['attachment_id'], $issue['type'], $issue['detail']));
        }

        if ($report['issues'] === []) {
            $this->info(__('No integrity issues detected.'));
        }

        $this->newLine();
        $this->info($report['healthy'] ? __('Status: HEALTHY') : __('Status: ISSUES FOUND'));

        return $report['healthy'] ? self::SUCCESS : self::FAILURE;
    }
}
