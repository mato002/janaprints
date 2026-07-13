<?php

namespace App\Http\Controllers\Admin\Communications\Whatsapp;

use App\Http\Controllers\Admin\Communications\Concerns\ResolvesCommunicationsTenant;
use App\Http\Controllers\Controller;
use App\Models\Communications\WhatsappConversation;
use App\Models\Communications\WhatsappTemplate;
use App\Models\Crm\Customer;
use App\Models\Crm\Lead;
use App\Models\Employee;
use App\Models\Procurement\Vendor;
use App\Support\Communications\ContactPickerOptionsBuilder;
use App\Support\Communications\Whatsapp\WhatsappConversationService;
use App\Support\Communications\Whatsapp\WhatsappMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WhatsappConversationController extends Controller
{
    use ResolvesCommunicationsTenant;

    public function __construct(
        protected WhatsappConversationService $conversations,
        protected WhatsappMessageService $messages,
        protected ContactPickerOptionsBuilder $contactPicker,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WhatsappConversation::class);

        $companyId = $this->requireCompanyId();
        $filters = $request->only(['q', 'status', 'assigned_user_id']);
        $conversations = $this->conversations->query($companyId, $filters)->paginate(25)->withQueryString();

        return view('admin.communications.whatsapp.conversations.index', compact('conversations', 'filters'));
    }

    public function create(Request $request): View
    {
        $this->authorize('send', WhatsappConversation::class);

        $companyId = $this->requireCompanyId();
        $picker = $this->contactPicker->forCompany($companyId);

        $contactType = old('contact_type', $request->string('contact_type')->toString() ?: 'customers');
        if (! in_array($contactType, ['customers', 'leads', 'employees', 'suppliers', 'phone'], true)) {
            $contactType = 'customers';
        }

        $selectedId = old('contact_id', $request->integer('customer_id') ?: $request->integer('contact_id') ?: null);
        if ($request->filled('customer_id') && ! $request->filled('contact_type')) {
            $contactType = 'customers';
            $selectedId = $request->integer('customer_id');
        }

        $defaultPhone = old('phone_number', $request->string('phone')->toString());
        if ($defaultPhone === '' && $selectedId && $contactType !== 'phone') {
            $defaultPhone = collect($picker['pickerOptions'][$contactType] ?? [])
                ->firstWhere('id', (int) $selectedId)['phone'] ?? '';
        }

        $templates = WhatsappTemplate::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->with('communicationTemplate')
            ->get();

        return view('admin.communications.whatsapp.conversations.create', [
            'templates' => $templates,
            'branches' => $picker['branches'],
            'departments' => $picker['departments'],
            'pickerOptions' => $picker['pickerOptions'],
            'contactType' => $contactType,
            'selectedId' => $selectedId ? (string) $selectedId : '',
            'defaultPhone' => $defaultPhone,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('send', WhatsappConversation::class);

        $companyId = $this->requireCompanyId();

        $validated = $request->validate([
            'contact_type' => ['required', Rule::in(['customers', 'leads', 'employees', 'suppliers', 'phone'])],
            'contact_id' => ['nullable', 'integer'],
            'phone_number' => ['nullable', 'string', 'max:32'],
            'body' => ['required_without:whatsapp_template_id', 'nullable', 'string', 'max:4096'],
            'whatsapp_template_id' => ['nullable', 'exists:whatsapp_templates,id'],
        ]);

        $type = $validated['contact_type'];
        $contactId = ! empty($validated['contact_id']) ? (int) $validated['contact_id'] : null;

        $customerId = null;
        $leadId = null;
        $employeeId = null;
        $vendorId = null;
        $displayName = null;
        $phone = trim((string) ($validated['phone_number'] ?? ''));

        if ($type === 'customers' && $contactId) {
            $customer = Customer::query()->where('company_id', $companyId)->findOrFail($contactId);
            $customerId = $customer->id;
            $displayName = $customer->company_name;
            if ($phone === '') {
                $phone = trim((string) $customer->phone);
            }
        } elseif ($type === 'leads' && $contactId) {
            $lead = Lead::query()->where('company_id', $companyId)->findOrFail($contactId);
            $leadId = $lead->id;
            $displayName = $lead->lead_name;
            if ($phone === '') {
                $phone = trim((string) $lead->phone);
            }
        } elseif ($type === 'employees' && $contactId) {
            $employee = Employee::query()->where('company_id', $companyId)->findOrFail($contactId);
            $employeeId = $employee->id;
            $displayName = trim("{$employee->first_name} {$employee->last_name}");
            if ($phone === '') {
                $phone = trim((string) $employee->phone);
            }
        } elseif ($type === 'suppliers' && $contactId) {
            $vendor = Vendor::query()->where('company_id', $companyId)->findOrFail($contactId);
            $vendorId = $vendor->id;
            $displayName = $vendor->vendor_name;
            if ($phone === '') {
                $phone = trim((string) $vendor->phone);
            }
        }

        if ($phone === '') {
            throw ValidationException::withMessages([
                'phone_number' => __('Select a person or enter a WhatsApp phone number.'),
            ]);
        }

        if ($type !== 'phone' && ! $contactId) {
            throw ValidationException::withMessages([
                'contact_id' => __('Pick someone from the list, or switch to phone only.'),
            ]);
        }

        $conversation = $this->conversations->findOrCreateForContact(
            $companyId,
            $phone,
            $request->user()->id,
            $customerId,
            $leadId,
            $vendorId,
            $displayName,
            $employeeId,
        );

        if (! empty($validated['whatsapp_template_id'])) {
            $template = WhatsappTemplate::query()
                ->where('company_id', $companyId)
                ->findOrFail($validated['whatsapp_template_id']);
            $this->messages->sendTemplate($conversation, $template, $request->user()->id);
        } else {
            $this->messages->sendManual($conversation, (string) $validated['body'], $request->user()->id);
        }

        return redirect()
            ->route('admin.communications.whatsapp.conversations.show', $conversation)
            ->with('status', __('Message queued.'));
    }

    public function show(WhatsappConversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $conversation->load([
            'messages.creator', 'messages.deliveryEvents', 'messages.communicationTemplate',
            'participants', 'customer', 'account', 'assignee',
        ]);
        $this->conversations->markRead($conversation);

        $templates = WhatsappTemplate::query()
            ->where('company_id', $conversation->company_id)
            ->where('is_active', true)
            ->with('communicationTemplate')
            ->get();

        return view('admin.communications.whatsapp.conversations.show', compact('conversation', 'templates'));
    }

    public function storeMessage(Request $request, WhatsappConversation $conversation): RedirectResponse
    {
        $this->authorize('send', WhatsappConversation::class);
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'body' => ['required_without:whatsapp_template_id', 'string', 'max:4096'],
            'whatsapp_template_id' => ['nullable', 'exists:whatsapp_templates,id'],
        ]);

        if (! empty($validated['whatsapp_template_id'])) {
            $template = WhatsappTemplate::query()
                ->where('company_id', $conversation->company_id)
                ->findOrFail($validated['whatsapp_template_id']);
            $this->messages->sendTemplate($conversation, $template, $request->user()->id);
        } else {
            $this->messages->sendManual($conversation, $validated['body'], $request->user()->id);
        }

        return redirect()
            ->route('admin.communications.whatsapp.conversations.show', $conversation)
            ->with('status', __('Message queued.'));
    }

    public function update(Request $request, WhatsappConversation $conversation): RedirectResponse
    {
        $this->authorize('manage', WhatsappConversation::class);
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'status' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'tags' => ['nullable', 'array'],
        ]);

        $conversation->update(array_filter([
            'status' => $validated['status'] ?? null,
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'tags' => $validated['tags'] ?? null,
        ], fn ($v) => $v !== null));

        return back()->with('status', __('Conversation updated.'));
    }
}
