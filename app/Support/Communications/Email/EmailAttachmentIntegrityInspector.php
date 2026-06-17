<?php

namespace App\Support\Communications\Email;

use App\Models\Communications\EmailAttachment;
use Illuminate\Support\Facades\Storage;

class EmailAttachmentIntegrityInspector
{
    /**
     * @return array{
     *     total: int,
     *     missing_files: int,
     *     broken_morphs: int,
     *     document_pdfs: int,
     *     issues: list<array{type: string, attachment_id: int, detail: string}>,
     *     healthy: bool,
     * }
     */
    public function inspect(?int $companyId = null): array
    {
        $disk = (string) config('communications.email_attachment_disk', 'local');
        $query = EmailAttachment::query()
            ->with(['message:id,company_id,subject'])
            ->whereNotNull('file_path');

        if ($companyId !== null) {
            $query->whereHas('message', fn ($q) => $q->where('company_id', $companyId));
        }

        $attachments = $query->get();
        $issues = [];
        $missingFiles = 0;
        $brokenMorphs = 0;
        $documentPdfs = 0;

        foreach ($attachments as $attachment) {
            $type = $attachment->attachment_type?->value ?? (string) $attachment->attachment_type;

            if (in_array($type, ['quotation_pdf', 'invoice_pdf', 'document'], true)) {
                $documentPdfs++;
            }

            if ($attachment->file_path && ! Storage::disk($disk)->exists($attachment->file_path)) {
                $missingFiles++;
                $issues[] = [
                    'type' => 'missing_file',
                    'attachment_id' => $attachment->id,
                    'detail' => __('File missing on disk: :path', ['path' => $attachment->file_path]),
                ];
            }

            if ($attachment->attachable_type && $attachment->attachable_id) {
                $model = $attachment->attachable;

                if ($model === null) {
                    $brokenMorphs++;
                    $issues[] = [
                        'type' => 'broken_morph',
                        'attachment_id' => $attachment->id,
                        'detail' => __('Attachable record not found: :type #:id', [
                            'type' => class_basename($attachment->attachable_type),
                            'id' => $attachment->attachable_id,
                        ]),
                    ];
                }
            }
        }

        return [
            'total' => $attachments->count(),
            'missing_files' => $missingFiles,
            'broken_morphs' => $brokenMorphs,
            'document_pdfs' => $documentPdfs,
            'issues' => array_slice($issues, 0, 50),
            'healthy' => $missingFiles === 0 && $brokenMorphs === 0,
        ];
    }
}
