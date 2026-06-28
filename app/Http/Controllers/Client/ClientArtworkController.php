<?php

namespace App\Http\Controllers\Client;

use App\Enums\ArtworkApprovalDecision;
use App\Enums\ArtworkRequestStatus;
use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Artwork\ArtworkApproval;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Crm\CustomerArtwork;
use App\Support\Governance\WorkflowRulesService;
use App\Enums\WorkflowRuleTrigger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientArtworkController extends Controller
{
    use ResolvesClientCustomer;

    public function index(): View
    {
        $customer = $this->clientCustomer();

        $libraryArtworks = CustomerArtwork::query()
            ->where('customer_id', $customer->id)
            ->where('is_active_version', true)
            ->latest('uploaded_at')
            ->get();

        $requests = ArtworkRequest::query()
            ->where('customer_id', $customer->id)
            ->latest('updated_at')
            ->paginate(12);

        return view('client.artwork.index', compact('customer', 'libraryArtworks', 'requests'));
    }

    public function show(ArtworkRequest $artwork): View
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($artwork, $customer);

        $artwork->load(['versions', 'approvals.approver']);

        $version = $artwork->currentVersionRecord();
        $previewUrl = $version && Storage::disk('local')->exists($version->file_path)
            ? route('client.artwork.file', $artwork)
            : null;
        $previewIsImage = $version && str_starts_with((string) $version->mime_type, 'image/');

        return view('client.artwork.show', [
            'customer' => $customer,
            'artwork' => $artwork,
            'canReview' => $artwork->status === ArtworkRequestStatus::Submitted,
            'previewUrl' => $previewUrl,
            'previewIsImage' => $previewIsImage,
        ]);
    }

    public function file(ArtworkRequest $artwork): BinaryFileResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($artwork, $customer);

        $version = $artwork->currentVersionRecord();
        abort_unless($version && Storage::disk('local')->exists($version->file_path), 404);

        return response()->file(Storage::disk('local')->path($version->file_path), [
            'Content-Type' => $version->mime_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($version->original_name).'"',
        ]);
    }

    public function review(Request $request, ArtworkRequest $artwork): RedirectResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($artwork, $customer);

        abort_unless($artwork->status === ArtworkRequestStatus::Submitted, 403);

        $validated = $request->validate([
            'decision' => ['required', Rule::enum(ArtworkApprovalDecision::class)],
            'comments' => ['nullable', 'string', 'max:5000'],
        ]);

        $decision = ArtworkApprovalDecision::from($validated['decision']);
        $version = $artwork->currentVersionRecord();

        abort_unless($version, 422, __('No artwork version is available for review.'));

        ArtworkApproval::query()->create([
            'company_id' => $artwork->company_id,
            'branch_id' => $artwork->branch_id,
            'artwork_request_id' => $artwork->id,
            'artwork_version_id' => $version->id,
            'approved_by' => $this->clientUser()->id,
            'decision' => $decision,
            'comments' => $validated['comments'] ?? null,
        ]);

        $newStatus = match ($decision) {
            ArtworkApprovalDecision::Approved => ArtworkRequestStatus::Approved,
            ArtworkApprovalDecision::Rejected => ArtworkRequestStatus::Rejected,
            ArtworkApprovalDecision::RevisionRequested => ArtworkRequestStatus::RevisionRequested,
        };

        $artwork->transitionTo($newStatus);

        $trigger = match ($decision) {
            ArtworkApprovalDecision::Approved => WorkflowRuleTrigger::Approved,
            ArtworkApprovalDecision::Rejected, ArtworkApprovalDecision::RevisionRequested => WorkflowRuleTrigger::Rejected,
        };

        app(WorkflowRulesService::class)->dispatch($trigger, $artwork->fresh(), $this->clientUser());

        return redirect()
            ->route('client.artwork.show', $artwork)
            ->with('status', __('Your artwork feedback has been submitted.'));
    }
}
