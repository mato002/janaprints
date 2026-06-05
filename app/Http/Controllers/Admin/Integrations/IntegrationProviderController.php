<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Integrations\Concerns\ResolvesIntegrationTenant;
use App\Http\Controllers\Controller;
use App\Models\Integrations\IntegrationProvider;
use App\Support\Integrations\IntegrationProviderCatalog;
use App\Support\Integrations\ProviderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationProviderController extends Controller
{
    use ResolvesIntegrationTenant, ScopesToTenant;

    public function __construct(
        protected ProviderService $providers,
        protected IntegrationProviderCatalog $catalog,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntegrationProvider::class);

        ['companyId' => $companyId] = $this->tenantIds($request);
        $this->catalog->ensureForCompany($companyId);

        $providers = $this->scopeToTenant(IntegrationProvider::query())
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        return view('admin.integrations.providers.index', [
            'providers' => $providers,
            'filters' => $request->only(['category', 'status']),
            'catalog' => IntegrationProviderCatalog::definitions(),
        ]);
    }

    public function show(IntegrationProvider $provider): View
    {
        $this->authorize('view', $provider);

        $provider->load(['logs' => fn ($q) => $q->latest()->limit(20), 'syncLogs' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.integrations.providers.show', [
            'provider' => $provider,
            'definition' => collect(IntegrationProviderCatalog::definitions())->firstWhere('provider_key', $provider->provider_key),
        ]);
    }

    public function connect(Request $request, IntegrationProvider $provider): RedirectResponse
    {
        $this->authorize('manage', $provider);

        $data = $request->validate([
            'client_id' => ['nullable', 'string', 'max:190'],
            'client_secret' => ['nullable', 'string', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'webhook_url' => ['nullable', 'url', 'max:500'],
        ]);

        $this->providers->connect($provider, array_filter($data), $request->user()->id);

        return back()->with('status', __('Provider connected.'));
    }

    public function disconnect(Request $request, IntegrationProvider $provider): RedirectResponse
    {
        $this->authorize('manage', $provider);

        $this->providers->disconnect($provider, $request->user()->id);

        return back()->with('status', __('Provider disconnected.'));
    }

    public function healthCheck(Request $request, IntegrationProvider $provider): RedirectResponse
    {
        $this->authorize('manage', $provider);

        $result = $this->providers->healthCheck($provider, $request->user()->id);

        return back()->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    public function sync(Request $request, IntegrationProvider $provider): RedirectResponse
    {
        $this->authorize('manage', $provider);

        $this->providers->sync($provider, $request->user()->id);

        return back()->with('status', __('Sync initiated.'));
    }
}
