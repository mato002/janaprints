<?php

namespace App\Support\Hr;

use App\Models\Hr\LeaveRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveExportService
{
    public function __construct(
        protected LeaveRequestService $leaveRequests,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function export(string $format, int $companyId, array $filters = []): StreamedResponse
    {
        $filename = 'leave-requests-'.now()->format('Y-m-d');

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

            foreach ($this->leaveRequests->exportRows($companyId, $filters) as $request) {
                fputcsv($handle, $this->row($request));
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

            foreach ($this->leaveRequests->exportRows($companyId, $filters) as $request) {
                echo '<tr>';
                foreach ($this->row($request) as $cell) {
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
        $requests = $this->leaveRequests->exportRows($companyId, $filters);

        $html = view('admin.hr.leave.exports.pdf', [
            'requests' => $requests,
            'generatedAt' => now(),
        ])->render();

        return response()->streamDownload(fn () => print($html), "{$filename}.html", [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * @return list<string>
     */
    protected function headers(): array
    {
        return [
            'Reference', 'Employee', 'Leave Type', 'Start', 'End', 'Days',
            'Status', 'Branch', 'Department', 'Reason',
        ];
    }

    /**
     * @return list<string|null>
     */
    protected function row(LeaveRequest $request): array
    {
        return [
            $request->reference,
            $request->employee?->full_name,
            $request->leaveType?->name,
            $request->start_date?->format('Y-m-d'),
            $request->end_date?->format('Y-m-d'),
            $request->days_requested,
            $request->status?->label(),
            $request->branch?->name,
            $request->department?->name,
            $request->reason,
        ];
    }
}
