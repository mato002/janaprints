<?php

namespace App\Http\Controllers\Admin\Executive;

use App\Http\Controllers\Controller;
use App\Support\Dashboard\ExecutiveApprovalActionService;
use App\Support\Dashboard\ExecutiveApprovalQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutiveApprovalQueueController extends Controller
{
    public function __construct(
        protected ExecutiveApprovalQueueService $queue,
        protected ExecutiveApprovalActionService $actions,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $this->queue->canView($user), 403);

        return view('admin.executive.approvals.index', [
            'queue' => $this->queue->build($user),
        ]);
    }

    public function approve(Request $request, string $kind, int $subjectId): RedirectResponse
    {
        $this->actions->approve(
            $request->user(),
            $kind,
            $subjectId,
            $request->input('notes'),
        );

        return back()->with('status', __('Approval recorded.'));
    }

    public function reject(Request $request, string $kind, int $subjectId): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->actions->reject(
            $request->user(),
            $kind,
            $subjectId,
            $validated['reason'],
        );

        return back()->with('status', __('Rejection recorded.'));
    }

    public function escalate(Request $request, string $kind, int $subjectId): RedirectResponse
    {
        $this->actions->escalate(
            $request->user(),
            $kind,
            $subjectId,
            $request->integer('chain_run_id') ?: null,
        );

        return back()->with('status', __('Approval escalated.'));
    }

    public function delegate(Request $request, string $kind, int $subjectId): RedirectResponse
    {
        return $this->actions->delegateRedirect($request->user(), $kind, $subjectId);
    }
}
