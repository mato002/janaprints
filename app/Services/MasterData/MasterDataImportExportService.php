<?php

namespace App\Services\MasterData;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterDataImportExportService
{
    public function __construct(
        protected MasterDataService $masterData,
    ) {}

    public function export(?string $category = null): StreamedResponse
    {
        $rows = $this->masterData->exportCollection($category);

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['category_key', 'code', 'name', 'description', 'sort_order', 'is_active']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->category_key,
                    $row->code,
                    $row->name,
                    $row->description,
                    $row->sort_order,
                    $row->is_active ? '1' : '0',
                ]);
            }

            fclose($handle);
        }, 'master-data-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array{imported: int}
     */
    public function import(UploadedFile $file, int $companyId, ?int $branchId, User $actor): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle) ?: [];
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) < 3) {
                continue;
            }

            $mapped = array_combine(
                array_slice($header, 0, count($line)),
                $line,
            );

            if (! is_array($mapped) || empty($mapped['category_key']) || empty($mapped['code']) || empty($mapped['name'])) {
                continue;
            }

            $rows[] = $mapped;
        }

        fclose($handle);

        return [
            'imported' => $this->masterData->importRows($rows, $companyId, $branchId, $actor),
        ];
    }
}
