<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\CommercialReportExport;
use App\Support\Commercial\Reports\CommercialReportExportService;
use App\Support\Commercial\Reports\Exports\CommercialReportExportReadiness;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommercialReportExportController extends Controller
{
    public function __construct(
        protected CommercialReportExportService $exports,
        protected CommercialReportExportReadiness $readiness,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('commercial.reports.exports.view'), 403);

        $companyId = tenant()->companyId() ?? $request->user()?->company_id;

        $exports = CommercialReportExport::query()
            ->with('user:id,name')
            ->where('company_id', (int) $companyId)
            ->when(
                ! $request->user()->can('commercial.reports.exports.download'),
                fn ($query) => $query->where('user_id', $request->user()->id),
            )
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.reports.exports.index', [
            'title' => __('Export History'),
            'description' => __('Queued commercial report exports with preserved filters and download links.'),
            'exports' => $exports,
            'readiness' => $this->readiness->assess(),
            'framework_ready' => $this->readiness->isReady(),
            'can_download' => $request->user()->can('commercial.reports.exports.download'),
        ]);
    }

    public function download(Request $request, CommercialReportExport $export): StreamedResponse
    {
        $this->exports->authorizeDownload($request->user(), $export);

        return Storage::disk('local')->download(
            $export->storage_path,
            $export->filename,
            ['Content-Type' => $export->mime_type ?? 'application/octet-stream'],
        );
    }

    public function status(Request $request, CommercialReportExport $export): JsonResponse
    {
        $this->exports->authorizeView($request->user(), $export);

        return response()->json([
            'status' => $export->status->value,
            'ready' => $export->isDownloadable(),
            'failed' => $export->status->value === 'failed',
            'expired' => $export->isExpired(),
            'error' => $export->error_message,
            'download_url' => $export->isDownloadable()
                ? route('admin.commercial.reports.exports.download', $export)
                : null,
            'filename' => $export->filename,
            'history_url' => route('admin.commercial.reports.exports.index'),
        ]);
    }
}
