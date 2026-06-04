<?php

namespace App\Http\Controllers\Admin\Communications;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Communications\CommunicationLog;
use App\Support\Communications\CommunicationLogService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommunicationLogController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected CommunicationLogService $logs,
    ) {}

    public function dashboard(): View
    {
        $this->authorize('viewAny', CommunicationLog::class);

        $companyId = $this->requireCompanyId();
        $analytics = $this->logs->analytics($companyId);
        $recent = $this->logs->query($companyId, ['sort' => 'newest'])->limit(10)->get();

        return view('admin.communications.logs.dashboard', compact('analytics', 'recent'));
    }

    public function timeline(Request $request): View
    {
        $this->authorize('viewAny', CommunicationLog::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['channel', 'status', 'communication_type', 'priority', 'branch_id', 'date_from', 'date_to', 'q', 'sort']);
        $logs = $this->logs->query($companyId, $filters)->paginate(25)->withQueryString();
        $branches = Branch::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('admin.communications.logs.timeline', compact('logs', 'filters', 'branches'));
    }

    public function search(Request $request): View
    {
        $this->authorize('viewAny', CommunicationLog::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['q', 'channel', 'status', 'date_from', 'date_to', 'reference_number']);
        $logs = $this->logs->query($companyId, $filters)->paginate(25)->withQueryString();

        return view('admin.communications.logs.search', compact('logs', 'filters'));
    }

    public function analytics(): View
    {
        $this->authorize('viewAny', CommunicationLog::class);

        $analytics = $this->logs->analytics($this->requireCompanyId());

        return view('admin.communications.logs.analytics', compact('analytics'));
    }

    public function failures(Request $request): View
    {
        $this->authorize('viewAny', CommunicationLog::class);

        $companyId = $this->requireCompanyId();
        $logs = $this->logs->query($companyId, ['view' => 'failures'])->paginate(25);

        return view('admin.communications.logs.failures', compact('logs'));
    }

    public function show(CommunicationLog $communicationLog): View
    {
        $this->authorize('view', $communicationLog);

        $communicationLog->load([
            'recipients', 'attachments.attachable', 'deliveryEvents.creator',
            'creator', 'sender', 'sentByUser', 'template', 'branch', 'campaign',
        ]);

        return view('admin.communications.logs.show', ['log' => $communicationLog]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', CommunicationLog::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['channel', 'status', 'date_from', 'date_to', 'q']);

        return response()->streamDownload(function () use ($companyId, $filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reference', 'Channel', 'Type', 'Status', 'Subject', 'Recipient', 'Sent at', 'Created at']);

            $this->logs->query($companyId, $filters)->chunk(200, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    $recipient = $log->recipients->first();
                    fputcsv($handle, [
                        $log->reference_number,
                        $log->channel->value,
                        $log->communication_type->value,
                        $log->status->value,
                        $log->subject,
                        $recipient?->display_name ?? $recipient?->phone ?? $recipient?->email,
                        $log->sent_at?->toDateTimeString(),
                        $log->created_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, 'communication-logs-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
