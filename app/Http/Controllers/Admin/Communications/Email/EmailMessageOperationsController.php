<?php

namespace App\Http\Controllers\Admin\Communications\Email;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\EmailMessage;
use App\Support\Communications\Email\EmailMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class EmailMessageOperationsController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected EmailMessageService $messages,
    ) {}

    public function show(EmailMessage $emailMessage): JsonResponse
    {
        $this->authorize('view', $emailMessage);
        abort_unless($emailMessage->company_id === $this->requireCompanyId(), 404);

        return response()->json([
            'message' => $this->messages->presentDetail($emailMessage),
        ]);
    }

    public function cancel(EmailMessage $emailMessage): RedirectResponse
    {
        $this->authorize('cancel', $emailMessage);
        abort_unless($emailMessage->company_id === $this->requireCompanyId(), 404);

        $this->messages->cancel($emailMessage, auth()->id());

        return back()->with('status', __('Email cancelled.'));
    }

    public function retry(EmailMessage $emailMessage): RedirectResponse
    {
        $this->authorize('retry', $emailMessage);
        abort_unless($emailMessage->company_id === $this->requireCompanyId(), 404);

        $this->messages->retry($emailMessage, auth()->id());

        return back()->with('status', __('Email queued for retry.'));
    }
}
