<?php

namespace App\Support\Crm;

use App\Enums\CustomerArtworkStatus;
use App\Enums\CustomerArtworkType;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
use App\Models\Crm\CustomerPrintSpecification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerArtworkService
{
    public function uploadVersion(
        Customer $customer,
        UploadedFile $file,
        string $artworkName,
        string $artworkType,
        int $uploadedBy,
        ?CustomerPrintSpecification $specification = null,
        ?string $changeNotes = null,
    ): CustomerArtwork {
        if ($specification !== null) {
            return $this->uploadVersionForSpecification(
                $specification,
                $file,
                $uploadedBy,
                $changeNotes,
                $artworkType,
            );
        }

        return DB::transaction(function () use ($customer, $file, $artworkName, $artworkType, $uploadedBy) {
            $latestVersion = CustomerArtwork::query()
                ->where('customer_id', $customer->id)
                ->where('artwork_name', $artworkName)
                ->max('version_number');

            $versionNumber = ((int) $latestVersion) + 1;

            CustomerArtwork::query()
                ->where('customer_id', $customer->id)
                ->where('artwork_name', $artworkName)
                ->where('is_active_version', true)
                ->update([
                    'is_active_version' => false,
                    'status' => CustomerArtworkStatus::Superseded,
                ]);

            $path = $file->store(
                'customer-artworks/'.$customer->company_id.'/'.$customer->id,
                'local',
            );

            return CustomerArtwork::query()->create([
                'company_id' => $customer->company_id,
                'branch_id' => $customer->branch_id,
                'customer_id' => $customer->id,
                'artwork_name' => $artworkName,
                'artwork_type' => $artworkType,
                'version_number' => $versionNumber,
                'is_active_version' => true,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'original_file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'status' => CustomerArtworkStatus::Active,
                'uploaded_by' => $uploadedBy,
                'uploaded_at' => now(),
            ]);
        });
    }

    public function uploadVersionForSpecification(
        CustomerPrintSpecification $specification,
        UploadedFile $file,
        int $uploadedBy,
        ?string $changeNotes = null,
        ?string $artworkType = null,
    ): CustomerArtwork {
        return DB::transaction(function () use ($specification, $file, $uploadedBy, $changeNotes, $artworkType) {
            $specification->loadMissing('customer');

            $latestVersion = CustomerArtwork::query()
                ->where('customer_print_specification_id', $specification->id)
                ->max('version_number');

            $versionNumber = ((int) $latestVersion) + 1;

            CustomerArtwork::query()
                ->where('customer_print_specification_id', $specification->id)
                ->where('is_active_version', true)
                ->update([
                    'is_active_version' => false,
                    'status' => CustomerArtworkStatus::Superseded,
                ]);

            $path = $file->store(
                'customer-artworks/'.$specification->company_id.'/'.$specification->customer_id,
                'local',
            );

            $type = $artworkType ?? CustomerArtworkType::Layout->value;

            return CustomerArtwork::query()->create([
                'company_id' => $specification->company_id,
                'branch_id' => $specification->branch_id,
                'customer_id' => $specification->customer_id,
                'customer_print_specification_id' => $specification->id,
                'artwork_name' => $specification->name,
                'artwork_type' => $type,
                'version_number' => $versionNumber,
                'is_active_version' => true,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'original_file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'status' => CustomerArtworkStatus::Active,
                'change_notes' => $changeNotes,
                'uploaded_by' => $uploadedBy,
                'uploaded_at' => now(),
            ]);
        });
    }

    public function streamPreview(CustomerArtwork $artwork): array
    {
        abort_unless(Storage::disk('local')->exists($artwork->file_path), 404);

        return [
            'path' => Storage::disk('local')->path($artwork->file_path),
            'mime' => $artwork->mime_type ?? 'application/octet-stream',
            'name' => $artwork->originalFileName(),
        ];
    }
}
