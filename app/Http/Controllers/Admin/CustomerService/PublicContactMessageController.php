<?php

namespace App\Http\Controllers\Admin\CustomerService;

use App\Enums\PublicContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Models\PublicContactMessage;
use App\Support\Commercial\PublicLeadsDashboardPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicContactMessageController extends Controller
{
    public function __construct(
        protected PublicLeadsDashboardPresenter $dashboard,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PublicContactMessage::class);

        $filters = $request->only(['q', 'status', 'date_from', 'date_to']);

        $contactMessages = PublicContactMessage::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('subject', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customer-service.contact-messages.index', [
            'contactMessages' => $contactMessages,
            'filters' => $filters,
            'stats' => $this->dashboard->widgets(),
        ]);
    }

    public function show(PublicContactMessage $publicContactMessage): View
    {
        $this->authorize('view', $publicContactMessage);

        if ($publicContactMessage->status === PublicContactMessageStatus::Unread) {
            $publicContactMessage->update(['status' => PublicContactMessageStatus::Read]);
            $publicContactMessage->refresh();
        }

        return view('admin.customer-service.contact-messages.show', [
            'contactMessage' => $publicContactMessage,
        ]);
    }

    public function updateStatus(Request $request, PublicContactMessage $publicContactMessage): RedirectResponse
    {
        $this->authorize('update', $publicContactMessage);

        $data = $request->validate([
            'status' => ['required', Rule::enum(PublicContactMessageStatus::class)],
        ]);

        $publicContactMessage->update([
            'status' => $data['status'],
            'responded_at' => in_array($data['status'], [
                PublicContactMessageStatus::Responded->value,
                PublicContactMessageStatus::Closed->value,
            ], true) ? now() : $publicContactMessage->responded_at,
        ]);

        return back()->with('status', __('Contact message status updated.'));
    }

    public function updateNotes(Request $request, PublicContactMessage $publicContactMessage): RedirectResponse
    {
        $this->authorize('update', $publicContactMessage);

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $publicContactMessage->update($data);

        return back()->with('status', __('Internal notes saved.'));
    }
}
