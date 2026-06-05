<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Models\Operations\SystemBackup;
use App\Operations\BackupsCenter;
use App\Services\Operations\BackupManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupManagementController extends Controller
{
    public function __construct(
        protected BackupManagementService $backups,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BackupsCenter::class);

        $this->backups->syncCatalog();

        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: 'all',
            'status' => $request->string('status')->toString() ?: 'all',
        ];

        return view('admin.operations.backups.index', [
            'backups' => $this->backups->paginate($filters),
            'metrics' => $this->backups->summaryMetrics(),
            'filters' => $filters,
            'typeOptions' => $this->backups->typeOptions(),
            'statusOptions' => $this->backups->statusOptions(),
            'backupService' => $this->backups,
            'canDownload' => $request->user()->can('download', BackupsCenter::class),
            'canManage' => $request->user()->can('manage', BackupsCenter::class),
        ]);
    }

    public function download(SystemBackup $systemBackup): StreamedResponse
    {
        $this->authorize('download', BackupsCenter::class);

        return $this->backups->download($systemBackup);
    }

    public function verify(SystemBackup $systemBackup): RedirectResponse
    {
        $this->authorize('manage', BackupsCenter::class);

        $backup = $this->backups->verify($systemBackup);

        return redirect()
            ->route('admin.operations.backups.index')
            ->with('success', $backup->status === \App\Enums\BackupStatus::Verified
                ? __('Backup verified successfully.')
                : ($backup->verification_message ?? __('Backup verification failed.')));
    }

    public function restoreReadiness(SystemBackup $systemBackup): JsonResponse
    {
        $this->authorize('manage', BackupsCenter::class);

        return response()->json($this->backups->restoreReadiness($systemBackup));
    }

    public function deleteExpired(Request $request): RedirectResponse
    {
        $this->authorize('manage', BackupsCenter::class);

        $count = $this->backups->deleteExpired();

        return redirect()
            ->route('admin.operations.backups.index')
            ->with('success', $count > 0
                ? __('Deleted :count expired backups.', ['count' => $count])
                : __('No expired backups to delete.'));
    }
}
