<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Enums\IntegrationEmailProvider;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Integrations\Concerns\ResolvesIntegrationTenant;
use App\Http\Controllers\Controller;
use App\Models\Integrations\IntegrationEmailSetting;
use App\Support\Integrations\EmailSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IntegrationEmailController extends Controller
{
    use ResolvesIntegrationTenant, ScopesToTenant;

    public function __construct(
        protected EmailSettingsService $email,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntegrationEmailSetting::class);

        $settings = $this->scopeToTenant(IntegrationEmailSetting::query())
            ->when($request->filled('provider'), fn ($q) => $q->where('provider', $request->string('provider')))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->orderByDesc('is_active')
            ->orderBy('provider')
            ->paginate(20)
            ->withQueryString();

        return view('admin.integrations.email.index', [
            'settings' => $settings,
            'filters' => $request->only(['provider', 'active']),
            'providers' => IntegrationEmailProvider::cases(),
            'activeProvider' => $settings->firstWhere('is_active', true),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', IntegrationEmailSetting::class);

        return view('admin.integrations.email.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', IntegrationEmailSetting::class);

        $data = $this->validateSetting($request);
        ['companyId' => $companyId] = $this->tenantIds($request);

        $setting = new IntegrationEmailSetting(['company_id' => $companyId]);
        $this->email->save($setting, $data, $request->user()->id);

        return redirect()->route('admin.integrations.email.show', $setting)->with('status', __('Email settings saved.'));
    }

    public function show(IntegrationEmailSetting $emailSetting): View
    {
        $this->authorize('view', $emailSetting);

        return view('admin.integrations.email.show', [
            'setting' => $emailSetting,
        ]);
    }

    public function edit(IntegrationEmailSetting $emailSetting): View
    {
        $this->authorize('update', $emailSetting);

        return view('admin.integrations.email.edit', array_merge($this->formMeta(), ['setting' => $emailSetting]));
    }

    public function update(Request $request, IntegrationEmailSetting $emailSetting): RedirectResponse
    {
        $this->authorize('update', $emailSetting);

        $data = $this->validateSetting($request);
        $this->email->save($emailSetting, $data, $request->user()->id);

        return redirect()->route('admin.integrations.email.show', $emailSetting)->with('status', __('Email settings updated.'));
    }

    public function testConnection(Request $request, IntegrationEmailSetting $emailSetting): RedirectResponse
    {
        $this->authorize('manage', $emailSetting);

        $result = $this->email->testConnection($emailSetting);

        return back()->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    public function sendTestEmail(Request $request, IntegrationEmailSetting $emailSetting): RedirectResponse
    {
        $this->authorize('manage', $emailSetting);

        $request->validate(['recipient' => ['required', 'email']]);
        $result = $this->email->sendTestEmail($emailSetting, $request->string('recipient')->toString());

        return back()->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    public function activate(Request $request, IntegrationEmailSetting $emailSetting): RedirectResponse
    {
        $this->authorize('manage', $emailSetting);

        $this->email->activate($emailSetting, $request->user()->id);

        return back()->with('status', __('Email provider activated.'));
    }

    public function deactivate(Request $request, IntegrationEmailSetting $emailSetting): RedirectResponse
    {
        $this->authorize('manage', $emailSetting);

        $this->email->deactivate($emailSetting, $request->user()->id);

        return back()->with('status', __('Email provider deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return ['providers' => IntegrationEmailProvider::cases()];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSetting(Request $request): array
    {
        return $request->validate([
            'provider' => ['required', Rule::enum(IntegrationEmailProvider::class)],
            'from_name' => ['nullable', 'string', 'max:120'],
            'from_email' => ['nullable', 'email', 'max:190'],
            'reply_to_email' => ['nullable', 'email', 'max:190'],
            'smtp_host' => ['nullable', 'string', 'max:190'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', 'string', 'in:tls,ssl,null'],
            'smtp_username' => ['nullable', 'string', 'max:190'],
            'smtp_password' => ['nullable', 'string', 'max:500'],
            'mailgun_domain' => ['nullable', 'string', 'max:190'],
            'mailgun_api_key' => ['nullable', 'string', 'max:500'],
            'sendgrid_api_key' => ['nullable', 'string', 'max:500'],
            'ses_access_key' => ['nullable', 'string', 'max:190'],
            'ses_secret_key' => ['nullable', 'string', 'max:500'],
            'ses_region' => ['nullable', 'string', 'max:30'],
        ]);
    }
}
