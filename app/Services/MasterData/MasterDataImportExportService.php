<?php

namespace App\Services\MasterData;

use App\Models\User;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterDataImportExportService
{
    public function __construct(
        protected MasterDataService $masterData,
        protected TabularExportWriter $writer,
    ) {}

    public function export(?string $category, string $format = 'csv'): StreamedResponse
    {
        $rows = $this->masterData->exportCollection($category);
        $headers = ['category_key', 'code', 'name', 'description', 'sort_order', 'is_active'];
        $mappedRows = $rows->map(fn ($row) => [
            $row->category_key,
            $row->code,
            $row->name,
            $row->description,
            $row->sort_order,
            $row->is_active ? '1' : '0',
        ]);

        return $this->writer->download(
            $format,
            'master-data-'.now()->format('Y-m-d'),
            $headers,
            $mappedRows,
            __('Master Data'),
        );
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
