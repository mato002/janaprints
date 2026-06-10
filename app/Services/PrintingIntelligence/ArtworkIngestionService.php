<?php

namespace App\Services\PrintingIntelligence;

use App\Enums\ArtworkAnalysisSource;
use App\Enums\ArtworkAnalysisStatus;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ArtworkIngestionService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function ingest(UploadedFile $file, array $context = []): PrintArtworkAnalysis
    {
        $companyId = (int) ($context['company_id'] ?? tenant()->companyId() ?? auth()->user()?->company_id);
        $branchId = $context['branch_id'] ?? tenant()->branchId() ?? auth()->user()?->default_branch_id;

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?? '');
        $this->assertSupportedExtension($extension);

        $mimeType = $file->getMimeType() ?: $file->getClientMimeType();
        $this->assertSupportedMime($mimeType);

        $maxBytes = (int) config('printing_intelligence.max_artwork_upload_mb', 50) * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            throw ValidationException::withMessages([
                'file' => [__('Artwork file exceeds the maximum allowed size of :size MB.', [
                    'size' => config('printing_intelligence.max_artwork_upload_mb', 50),
                ])],
            ]);
        }

        $hash = hash_file('sha256', $file->getRealPath() ?: $file->path());
        if ($hash === false) {
            throw new RuntimeException('Unable to calculate artwork file hash.');
        }

        $existing = PrintArtworkAnalysis::query()
            ->where('company_id', $companyId)
            ->where('file_hash', $hash)
            ->whereNull('deleted_at')
            ->whereNotIn('analysis_status', [
                ArtworkAnalysisStatus::Failed->value,
            ])
            ->latest('id')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $disk = (string) config('printing_intelligence.storage_disk', 'local');
        $storedFilename = Str::uuid()->toString().'.'.$extension;
        $directory = sprintf(
            'printing-intelligence/artwork/%d/%s/%s',
            $companyId,
            now()->format('Y'),
            now()->format('m'),
        );

        $storedPath = Storage::disk($disk)->putFileAs($directory, $file, $storedFilename);

        if ($storedPath === false) {
            throw new RuntimeException('Unable to store artwork file.');
        }

        $source = $context['analysis_source'] ?? ArtworkAnalysisSource::Upload;
        if (is_string($source)) {
            $source = ArtworkAnalysisSource::tryFrom($source) ?? ArtworkAnalysisSource::Upload;
        }

        return PrintArtworkAnalysis::query()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'quotation_id' => $context['quotation_id'] ?? null,
            'production_job_card_id' => $context['production_job_card_id'] ?? null,
            'uploaded_by' => $context['uploaded_by'] ?? auth()->id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'file_path' => $storedPath,
            'disk' => $disk,
            'mime_type' => $mimeType,
            'file_extension' => $extension,
            'file_size_bytes' => (int) $file->getSize(),
            'file_hash' => $hash,
            'analysis_status' => ArtworkAnalysisStatus::Pending,
            'analysis_source' => $source,
        ]);
    }

    protected function assertSupportedExtension(string $extension): void
    {
        $allowed = array_map('strtolower', config('printing_intelligence.allowed_artwork_extensions', []));

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => [__('Unsupported artwork file type. Allowed: :types.', [
                    'types' => strtoupper(implode(', ', $allowed)),
                ])],
            ]);
        }
    }

    protected function assertSupportedMime(?string $mimeType): void
    {
        if ($mimeType === null) {
            return;
        }

        $allowed = config('printing_intelligence.allowed_artwork_mimes', []);

        if ($allowed !== [] && ! in_array(strtolower($mimeType), array_map('strtolower', $allowed), true)) {
            throw ValidationException::withMessages([
                'file' => [__('Unsupported artwork MIME type (:mime).', ['mime' => $mimeType])],
            ]);
        }
    }
}
