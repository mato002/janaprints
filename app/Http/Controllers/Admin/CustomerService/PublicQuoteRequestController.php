<?php

namespace App\Http\Controllers\Admin\CustomerService;

use App\Enums\PublicQuoteRequestPriority;
use App\Enums\PublicQuoteRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\PublicQuoteRequest;
use App\Models\PublicQuoteRequestNote;
use App\Services\Commercial\PublicQuoteRequestNotificationService;
use App\Services\Storefront\PublicQuoteRequestService;
use App\Support\Commercial\PublicLeadsDashboardPresenter;
use App\Support\Commercial\PublicQuoteRequestWorkspacePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicQuoteRequestController extends Controller
{
    public function __construct(
        protected PublicQuoteRequestService $service,
        protected PublicLeadsDashboardPresenter $dashboard,
        protected PublicQuoteRequestWorkspacePresenter $workspace,
        protected PublicQuoteRequestNotificationService $quoteNotifications,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PublicQuoteRequest::class);

        $filters = $request->only(['q', 'status', 'service_needed', 'date_from', 'date_to']);

        $quoteRequests = PublicQuoteRequest::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('service_needed', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('service_needed'), fn ($q) => $q->where('service_needed', $request->string('service_needed')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $services = PublicQuoteRequest::query()
            ->select('service_needed')
            ->distinct()
            ->orderBy('service_needed')
            ->pluck('service_needed');

        return view('admin.customer-service.quote-requests.index', [
            'quoteRequests' => $quoteRequests,
            'filters' => $filters,
            'services' => $services,
            'stats' => $this->dashboard->widgets(),
        ]);
    }

    public function show(PublicQuoteRequest $publicQuoteRequest): View
    {
        $this->authorize('view', $publicQuoteRequest);

        if ($user = auth()->user()) {
            $this->quoteNotifications->markRelatedRead($user, $publicQuoteRequest);
        }

        return view('admin.customer-service.quote-requests.show', [
            'quoteRequest' => $publicQuoteRequest,
            'workspace' => $this->workspace->build($publicQuoteRequest),
        ]);
    }

    public function updateStatus(Request $request, PublicQuoteRequest $publicQuoteRequest): RedirectResponse
    {
        $this->authorize('update', $publicQuoteRequest);

        $data = $request->validate([
            'status' => ['required', Rule::enum(PublicQuoteRequestStatus::class)],
        ]);

        $publicQuoteRequest->update([
            'status' => $data['status'],
            'responded_at' => in_array($data['status'], [
                PublicQuoteRequestStatus::Quoted->value,
                PublicQuoteRequestStatus::Closed->value,
            ], true) ? now() : $publicQuoteRequest->responded_at,
        ]);

        return back()->with('status', __('Quote request status updated.'));
    }

    public function updateReview(Request $request, PublicQuoteRequest $publicQuoteRequest): RedirectResponse
    {
        $this->authorize('update', $publicQuoteRequest);

        $data = $request->validate([
            'status' => ['required', Rule::enum(PublicQuoteRequestStatus::class)],
            'priority' => ['nullable', Rule::enum(PublicQuoteRequestPriority::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'expected_value' => ['nullable', 'numeric', 'min:0'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'target_follow_up_at' => ['nullable', 'date'],
        ]);

        $publicQuoteRequest->update([
            'status' => $data['status'],
            'priority' => $data['priority'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'expected_value' => $data['expected_value'] ?? null,
            'probability' => $data['probability'] ?? null,
            'target_follow_up_at' => $data['target_follow_up_at'] ?? null,
            'responded_at' => in_array($data['status'], [
                PublicQuoteRequestStatus::Quoted->value,
                PublicQuoteRequestStatus::Closed->value,
            ], true) ? now() : $publicQuoteRequest->responded_at,
        ]);

        return back()->with('status', __('Commercial review saved.'));
    }

    public function storeNote(Request $request, PublicQuoteRequest $publicQuoteRequest): RedirectResponse
    {
        $this->authorize('update', $publicQuoteRequest);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        PublicQuoteRequestNote::query()->create([
            'public_quote_request_id' => $publicQuoteRequest->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return back()->with('status', __('Note added.'));
    }

    public function updateNotes(Request $request, PublicQuoteRequest $publicQuoteRequest): RedirectResponse
    {
        $this->authorize('update', $publicQuoteRequest);

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $publicQuoteRequest->update($data);

        return back()->with('status', __('Internal notes saved.'));
    }

    public function previewArtwork(PublicQuoteRequest $publicQuoteRequest)
    {
        $this->authorize('view', $publicQuoteRequest);

        abort_unless($publicQuoteRequest->artwork_path, 404);

        $disk = config('leads.artwork.disk', 'public');

        abort_unless(Storage::disk($disk)->exists($publicQuoteRequest->artwork_path), 404);

        return Storage::disk($disk)->response(
            $publicQuoteRequest->artwork_path,
            $publicQuoteRequest->artwork_original_name ?? 'artwork',
            ['Content-Disposition' => 'inline'],
        );
    }

    public function downloadArtwork(PublicQuoteRequest $publicQuoteRequest): StreamedResponse
    {
        $this->authorize('view', $publicQuoteRequest);

        abort_unless($publicQuoteRequest->artwork_path, 404);

        $disk = config('leads.artwork.disk', 'public');

        abort_unless(Storage::disk($disk)->exists($publicQuoteRequest->artwork_path), 404);

        return Storage::disk($disk)->download(
            $publicQuoteRequest->artwork_path,
            $publicQuoteRequest->artwork_original_name ?? 'artwork',
        );
    }
}
