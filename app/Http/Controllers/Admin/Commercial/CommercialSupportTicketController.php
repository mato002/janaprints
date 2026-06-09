<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Enums\CommercialTicketChannel;
use App\Enums\CommercialTicketCommentVisibility;
use App\Enums\CommercialTicketPriority;
use App\Enums\CommercialTicketStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Crm\Concerns\ResolvesCrmTenant;
use App\Http\Controllers\Controller;
use App\Models\Commercial\CommercialSupportTicket;
use App\Models\Crm\Customer;
use App\Models\User;
use App\Support\Commercial\SupportTicketService;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommercialSupportTicketController extends Controller
{
    use ResolvesCrmTenant, ScopesToTenant;

    public function __construct(
        protected SupportTicketService $tickets,
        protected FormSettingsService $formSettings,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CommercialSupportTicket::class);

        $tickets = $this->scopeToTenant(
            CommercialSupportTicket::query()
                ->with(['customer:id,company_name', 'assignee:id,name', 'branch:id,name'])
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commercial.support-tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'customer_id']),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->limit(100)->get(['id', 'company_name']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CommercialSupportTicket::class);

        return view('admin.commercial.support-tickets.create', $this->formMeta($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CommercialSupportTicket::class);

        $data = $this->validateTicket($request);
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $priority = CommercialTicketPriority::from($data['priority']);

        $ticket = CommercialSupportTicket::query()->create([
            ...$data,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'ticket_number' => $this->tickets->nextTicketNumber($companyId),
            'created_by' => $request->user()->id,
            'due_at' => $this->tickets->defaultDueAt($priority),
            'status' => CommercialTicketStatus::Open,
        ]);

        $this->tickets->recordSlaEvent($ticket, 'created', __('Ticket created.'), (int) $request->user()->id);

        return redirect()->route('admin.commercial.support-tickets.show', $ticket)->with('status', __('Support ticket created.'));
    }

    public function show(CommercialSupportTicket $supportTicket): View
    {
        $this->authorize('view', $supportTicket);

        $supportTicket->load(['customer', 'assignee', 'creator', 'branch', 'comments.user', 'slaEvents.creator']);

        return view('admin.commercial.support-tickets.show', [
            'ticket' => $supportTicket,
            'users' => $this->assignableUsers(),
        ]);
    }

    public function edit(CommercialSupportTicket $supportTicket): View
    {
        $this->authorize('update', $supportTicket);

        return view('admin.commercial.support-tickets.edit', array_merge(
            ['ticket' => $supportTicket],
            $this->formMeta(request()),
        ));
    }

    public function update(Request $request, CommercialSupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        $supportTicket->update($this->validateTicket($request, false));

        return redirect()->route('admin.commercial.support-tickets.show', $supportTicket)->with('status', __('Ticket updated.'));
    }

    public function assign(Request $request, CommercialSupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('assign', $supportTicket);

        $validated = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $this->tickets->assign($supportTicket, (int) $validated['assigned_to']);

        return back()->with('status', __('Ticket assigned.'));
    }

    public function comment(Request $request, CommercialSupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
            'visibility' => ['required', Rule::enum(CommercialTicketCommentVisibility::class)],
        ]);

        $this->tickets->addComment(
            $supportTicket,
            (int) $request->user()->id,
            $validated['comment'],
            $validated['visibility'],
        );

        return back()->with('status', __('Comment added.'));
    }

    public function resolve(CommercialSupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('resolve', $supportTicket);

        $this->tickets->transition($supportTicket, CommercialTicketStatus::Resolved);

        return back()->with('status', __('Ticket resolved.'));
    }

    public function close(CommercialSupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('resolve', $supportTicket);

        $this->tickets->transition($supportTicket, CommercialTicketStatus::Closed);

        return back()->with('status', __('Ticket closed.'));
    }

    public function reopen(CommercialSupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('resolve', $supportTicket);

        $this->tickets->transition($supportTicket, CommercialTicketStatus::Reopened);

        return back()->with('status', __('Ticket reopened.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateTicket(Request $request, bool $requireSubject = true): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        $rules = $this->formSettings->mergeValidationRules('commercial_support_ticket.create', [
            'customer_id' => ['exists:customers,id'],
            'subject' => ['string', 'max:255'],
            'description' => ['string', 'max:10000'],
            'channel' => [Rule::enum(CommercialTicketChannel::class)],
            'priority' => [Rule::enum(CommercialTicketPriority::class)],
            'status' => ['sometimes', Rule::enum(CommercialTicketStatus::class)],
        ], $companyId, $branchId);

        if (! $requireSubject) {
            foreach (['subject', 'description'] as $field) {
                if (isset($rules[$field])) {
                    $rules[$field] = array_map(
                        fn ($rule) => $rule === 'required' ? 'sometimes' : $rule,
                        $rules[$field],
                    );
                }
            }
        }

        return $request->validate($rules);
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(Request $request): array
    {
        ['companyId' => $companyId, 'branchId' => $branchId] = $this->tenantIds($request);

        return [
            'formFields' => $this->formSettings->resolvedFields('commercial_support_ticket.create', $companyId, $branchId),
            'customers' => Customer::query()->forTenant()->orderBy('company_name')->get(['id', 'company_name']),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    protected function assignableUsers()
    {
        ['companyId' => $companyId] = $this->tenantIds(request());

        return User::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
