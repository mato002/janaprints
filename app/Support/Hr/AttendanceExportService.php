<?php

namespace App\Support\Hr;

use App\Models\Hr\AttendanceRecord;
use App\Support\Export\PdfExportService;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceExportService
{
    public function __construct(
        protected AttendanceService $attendance,
        protected PdfExportService $pdfExports,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(string $format, int $companyId, array $filters = []): StreamedResponse
    {
        $filename = 'attendance-register-'.now()->format('Y-m-d');

        return match ($format) {
            'excel' => $this->exportExcel($filename, $companyId, $filters),
            'pdf' => $this->exportPdf($filename, $companyId, $filters),
            default => $this->exportCsv($filename, $companyId, $filters),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportCsv(string $filename, int $companyId, array $filters): StreamedResponse
    {
        return response()->streamDownload(function () use ($companyId, $filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headers());

            foreach ($this->attendance->exportRows($companyId, $filters) as $record) {
                fputcsv($handle, $this->row($record));
            }

            fclose($handle);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportExcel(string $filename, int $companyId, array $filters): StreamedResponse
    {
        return response()->streamDownload(function () use ($companyId, $filters) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1"><thead><tr>';

            foreach ($this->headers() as $header) {
                echo '<th>'.e($header).'</th>';
            }

            echo '</tr></thead><tbody>';

            foreach ($this->attendance->exportRows($companyId, $filters) as $record) {
                echo '<tr>';
                foreach ($this->row($record) as $cell) {
                    echo '<td>'.e((string) $cell).'</td>';
                }
                echo '</tr>';
            }

            echo '</tbody></table>';
        }, "{$filename}.xls", ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function exportPdf(string $filename, int $companyId, array $filters): StreamedResponse
    {
        $records = $this->attendance->exportRows($companyId, $filters);

        return $this->pdfExports->downloadHtml(
            $filename,
            view('admin.hr.attendance.exports.pdf', [
                'records' => $records,
                'generatedAt' => now(),
                'filters' => $filters,
            ])->render(),
            'landscape',
        );
    }

    /**
     * @return list<string>
     */
    protected function headers(): array
    {
        return [
            'Date',
            'Employee',
            'Employee Number',
            'Department',
            'Branch',
            'Shift',
            'Clock In',
            'Clock Out',
            'Hours Worked',
            'Overtime',
            'Late (min)',
            'Status',
            'Method',
        ];
    }

    /**
     * @return list<string|float|null>
     */
    protected function row(AttendanceRecord $record): array
    {
        return [
            $record->attendance_date?->format('Y-m-d'),
            $record->employee?->full_name,
            $record->employee?->employee_number,
            $record->department?->name,
            $record->branch?->name,
            $record->shift?->name,
            $record->clock_in_at?->format('Y-m-d H:i'),
            $record->clock_out_at?->format('Y-m-d H:i'),
            $record->actual_hours,
            $record->overtime_hours,
            $record->late_minutes,
            $record->status?->label(),
            $record->method?->label(),
        ];
    }
}
