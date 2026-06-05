<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Enums\IntegrationWebhookEvent;
use App\Enums\IntegrationWebhookStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Integrations\Concerns\ResolvesIntegrationTenant;
use App\Http\Controllers\Controller;
use App\Models\Integrations\IntegrationWebhook;
use App\Models\Integrations\IntegrationWebhookDelivery;
use App\Support\Integrations\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IntegrationWebhookController extends Controller
{
    use ResolvesIntegrationTenant, ScopesToTenant;

    public function __construct(
        protected WebhookService $webhooks,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntegrationWebhook::class);

        $webhooks = $this->scopeToTenant(IntegrationWebhook::query())
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.integrations.webhooks.index', [
            'webhooks' => $webhooks,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', IntegrationWebhook::class);

        return view('admin.integrations.webhooks.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', IntegrationWebhook::class);

        $data = $this->validateWebhook($request);
        ['companyId' => $companyId] = $this->tenantIds($request);

        $webhook = new IntegrationWebhook(['company_id' => $companyId]);
        $this->webhooks->save($webhook, $data, $request->user()->id);

        return redirect()->route('admin.integrations.webhooks.show', $webhook)->with('status', __('Webhook created.'));
    }

    public function show(IntegrationWebhook $webhook): View
    {
        $this->authorize('view', $webhook);

        $deliveries = $webhook->deliveries()->latest()->limit(100)->get();

        return view('admin.integrations.webhooks.show', [
            'webhook' => $webhook,
            'deliveries' => $deliveries,
            'stats' => $this->webhooks->stats($webhook),
        ]);
    }

    public function edit(IntegrationWebhook $webhook): View
    {
        $this->authorize('update', $webhook);

        return view('admin.integrations.webhooks.edit', array_merge($this->formMeta(), ['webhook' => $webhook]));
    }

    public function update(Request $request, IntegrationWebhook $webhook): RedirectResponse
    {
        $this->authorize('update', $webhook);

        $data = $this->validateWebhook($request);
        $this->webhooks->save($webhook, $data, $request->user()->id);

        return redirect()->route('admin.integrations.webhooks.show', $webhook)->with('status', __('Webhook updated.'));
    }

    public function test(Request $request, IntegrationWebhook $webhook): RedirectResponse
    {
        $this->authorize('manage', $webhook);

        $this->webhooks->test($webhook);

        return back()->with('status', __('Test webhook dispatched.'));
    }

    public function retry(Request $request, IntegrationWebhook $webhook, IntegrationWebhookDelivery $delivery): RedirectResponse
    {
        $this->authorize('manage', $webhook);

        abort_unless($delivery->webhook_id === $webhook->id, 404);

        $this->webhooks->retry($delivery);

        return back()->with('status', __('Delivery retried.'));
    }

    public function disable(IntegrationWebhook $webhook): RedirectResponse
    {
        $this->authorize('manage', $webhook);

        $this->webhooks->disable($webhook);

        return back()->with('status', __('Webhook disabled.'));
    }

    public function enable(IntegrationWebhook $webhook): RedirectResponse
    {
        $this->authorize('manage', $webhook);

        $this->webhooks->enable($webhook);

        return back()->with('status', __('Webhook enabled.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return [
            'events' => IntegrationWebhookEvent::cases(),
            'statuses' => IntegrationWebhookStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateWebhook(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'endpoint_url' => ['required', 'url', 'max:500'],
            'secret' => ['nullable', 'string', 'max:500'],
            'event_types' => ['required', 'array', 'min:1'],
            'event_types.*' => [Rule::enum(IntegrationWebhookEvent::class)],
            'status' => ['required', Rule::enum(IntegrationWebhookStatus::class)],
            'retry_count' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        if (empty($validated['secret'])) {
            $validated['secret'] = '__generate__';
        }

        return $validated;
    }
}
