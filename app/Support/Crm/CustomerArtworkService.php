<?php

namespace App\Support\Crm;

use App\Enums\CustomerArtworkStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerArtwork;
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
    ): CustomerArtwork {
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
                'mime_type' => $file->getMimeType(),
                'status' => CustomerArtworkStatus::Active,
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
            'name' => $artwork->file_name,
        ];
    }
}
