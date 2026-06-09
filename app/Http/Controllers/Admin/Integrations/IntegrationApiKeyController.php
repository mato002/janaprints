<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Enums\IntegrationApiKeyEnvironment;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Integrations\Concerns\ResolvesIntegrationTenant;
use App\Http\Controllers\Controller;
use App\Models\Integrations\IntegrationApiKey;
use App\Support\Integrations\ApiKeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntegrationApiKeyController extends Controller
{
    use ResolvesIntegrationTenant, ScopesToTenant;

    public function __construct(
        protected ApiKeyService $apiKeys,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntegrationApiKey::class);

        $keys = $this->scopeToTenant(IntegrationApiKey::query())
            ->when($request->filled('environment'), fn ($q) => $q->where('environment', $request->string('environment')))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status')->toString() === 'active') {
                    $q->where('is_active', true)->whereNull('revoked_at');
                } elseif ($request->string('status')->toString() === 'disabled') {
                    $q->where('is_active', false)->whereNull('revoked_at');
                } elseif ($request->string('status')->toString() === 'revoked') {
                    $q->whereNotNull('revoked_at');
                }
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.integrations.api-keys.index', [
            'apiKeys' => $keys,
            'filters' => $request->only(['environment', 'status']),
            'environments' => IntegrationApiKeyEnvironment::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', IntegrationApiKey::class);

        return view('admin.integrations.api-keys.create', [
            'environments' => IntegrationApiKeyEnvironment::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', IntegrationApiKey::class);

        $data = $this->validateKey($request);
        ['companyId' => $companyId] = $this->tenantIds($request);

        $result = $this->apiKeys->generate($companyId, $data, $request->user()->id);

        return redirect()
            ->route('admin.integrations.api-keys.show', $result['apiKey'])
            ->with('generated_secret', $result['secret'])
            ->with('status', __('API key generated. Copy the secret now — it will not be shown again.'));
    }

    public function show(IntegrationApiKey $apiKey): View
    {
        $this->authorize('view', $apiKey);

        return view('admin.integrations.api-keys.show', [
            'apiKey' => $apiKey,
            'generatedSecret' => session('generated_secret'),
        ]);
    }

    public function regenerate(Request $request, IntegrationApiKey $apiKey): RedirectResponse
    {
        $this->authorize('update', $apiKey);

        $result = $this->apiKeys->regenerate($apiKey, $request->user()->id);

        return redirect()
            ->route('admin.integrations.api-keys.show', $result['apiKey'])
            ->with('generated_secret', $result['secret'])
            ->with('status', __('API key regenerated. Copy the new secret now.'));
    }

    public function disable(Request $request, IntegrationApiKey $apiKey): RedirectResponse
    {
        $this->authorize('update', $apiKey);

        $this->apiKeys->disable($apiKey, $request->user()->id);

        return back()->with('status', __('API key disabled.'));
    }

    public function enable(Request $request, IntegrationApiKey $apiKey): RedirectResponse
    {
        $this->authorize('update', $apiKey);

        $this->apiKeys->enable($apiKey, $request->user()->id);

        return back()->with('status', __('API key enabled.'));
    }

    public function revoke(Request $request, IntegrationApiKey $apiKey): RedirectResponse
    {
        $this->authorize('delete', $apiKey);

        $this->apiKeys->revoke($apiKey, $request->user()->id);

        return redirect()->route('admin.integrations.api-keys.index')->with('status', __('API key revoked.'));
    }

    public function export(Request $request, string $format = 'csv'): StreamedResponse
    {
        $this->authorize('viewAny', IntegrationApiKey::class);

        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $keys = $this->scopeToTenant(IntegrationApiKey::query())
            ->when($request->filled('environment'), fn ($q) => $q->where('environment', $request->string('environment')))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status')->toString() === 'active') {
                    $q->where('is_active', true)->whereNull('revoked_at');
                } elseif ($request->string('status')->toString() === 'disabled') {
                    $q->where('is_active', false)->whereNull('revoked_at');
                } elseif ($request->string('status')->toString() === 'revoked') {
                    $q->whereNotNull('revoked_at');
                }
            })
            ->orderBy('name')
            ->get();

        $headers = ['Name', 'Key', 'Environment', 'Active', 'Last Used', 'Created'];
        $rows = $keys->map(fn ($key) => [
            $key->name,
            $key->key,
            $key->environment->value,
            $key->is_active ? 'yes' : 'no',
            $key->last_used_at?->toDateTimeString() ?? '',
            $key->created_at?->toDateTimeString() ?? '',
        ]);

        return app(\App\Support\Export\TabularExportWriter::class)->download(
            $format,
            'api-keys-'.now()->format('Y-m-d'),
            $headers,
            $rows,
            __('API Keys'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateKey(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'environment' => ['required', Rule::enum(IntegrationApiKeyEnvironment::class)],
            'allowed_ips' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:80'],
        ]);

        if (! empty($validated['allowed_ips'])) {
            $validated['allowed_ips'] = array_values(array_filter(array_map('trim', explode(',', $validated['allowed_ips']))));
        }

        return $validated;
    }
}
