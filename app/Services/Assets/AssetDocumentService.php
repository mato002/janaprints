<?php

namespace App\Services\Assets;

use App\Enums\AssetDocumentType;
use App\Models\Assets\AssetDocument;
use App\Models\Assets\FixedAsset;
use App\Support\ActivityLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class AssetDocumentService
{
    /**
     * @return Collection<int, AssetDocument>
     */
    public function listForAsset(FixedAsset $asset, bool $includeArchived = false): Collection
    {
        return AssetDocument::query()
            ->where('fixed_asset_id', $asset->id)
            ->when(! $includeArchived, fn ($q) => $q->whereNull('archived_at'))
            ->with('uploader:id,name')
            ->latest()
            ->get();
    }

    public function upload(
        FixedAsset $asset,
        UploadedFile $file,
        AssetDocumentType $type,
        string $title,
        int $userId,
    ): AssetDocument {
        $path = $file->store("asset-documents/{$asset->company_id}/{$asset->id}", 'local');

        $document = AssetDocument::query()->create([
            'company_id' => $asset->company_id,
            'branch_id' => $asset->branch_id,
            'fixed_asset_id' => $asset->id,
            'document_type' => $type,
            'title' => $title,
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'disk' => 'local',
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $userId,
        ]);

        ActivityLogger::log('document_uploaded', $asset, $userId, [
            'document_id' => $document->id,
            'document_type' => $type->value,
            'title' => $title,
        ]);

        return $document;
    }

    public function archive(AssetDocument $document, int $userId): AssetDocument
    {
        $document->update([
            'archived_at' => now(),
            'archived_by' => $userId,
        ]);

        ActivityLogger::log('document_archived', $document->asset, $userId, [
            'document_id' => $document->id,
            'title' => $document->title,
        ]);

        return $document->fresh();
    }
}
