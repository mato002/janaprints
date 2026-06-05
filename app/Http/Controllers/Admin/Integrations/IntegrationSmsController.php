<?php

namespace App\Http\Controllers\Admin\Integrations;

use App\Enums\IntegrationSmsProvider;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Admin\Integrations\Concerns\ResolvesIntegrationTenant;
use App\Http\Controllers\Controller;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Support\Integrations\SmsSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IntegrationSmsController extends Controller
{
    use ResolvesIntegrationTenant, ScopesToTenant;

    public function __construct(
        protected SmsSettingsService $sms,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntegrationSmsSetting::class);

        $settings = $this->scopeToTenant(IntegrationSmsSetting::query())
            ->when($request->filled('provider'), fn ($q) => $q->where('provider', $request->string('provider')))
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->boolean('active')))
            ->orderByDesc('is_active')
            ->orderBy('provider')
            ->paginate(20)
            ->withQueryString();

        return view('admin.integrations.sms.index', [
            'settings' => $settings,
            'filters' => $request->only(['provider', 'active']),
            'providers' => IntegrationSmsProvider::cases(),
            'activeProvider' => $settings->firstWhere('is_active', true),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', IntegrationSmsSetting::class);

        return view('admin.integrations.sms.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', IntegrationSmsSetting::class);

        $data = $this->validateSetting($request);
        ['companyId' => $companyId] = $this->tenantIds($request);

        $setting = new IntegrationSmsSetting(['company_id' => $companyId]);
        $this->sms->save($setting, $data, $request->user()->id);

        return redirect()->route('admin.integrations.sms.show', $setting)->with('status', __('SMS settings saved.'));
    }

    public function show(IntegrationSmsSetting $smsSetting): View
    {
        $this->authorize('view', $smsSetting);

        return view('admin.integrations.sms.show', ['setting' => $smsSetting]);
    }

    public function edit(IntegrationSmsSetting $smsSetting): View
    {
        $this->authorize('update', $smsSetting);

        return view('admin.integrations.sms.edit', array_merge($this->formMeta(), ['setting' => $smsSetting]));
    }

    public function update(Request $request, IntegrationSmsSetting $smsSetting): RedirectResponse
    {
        $this->authorize('update', $smsSetting);

        $data = $this->validateSetting($request);
        $this->sms->save($smsSetting, $data, $request->user()->id);

        return redirect()->route('admin.integrations.sms.show', $smsSetting)->with('status', __('SMS settings updated.'));
    }

    public function verify(Request $request, IntegrationSmsSetting $smsSetting): RedirectResponse
    {
        $this->authorize('manage', $smsSetting);

        $result = $this->sms->verifyCredentials($smsSetting);

        return back()->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    public function sendTest(Request $request, IntegrationSmsSetting $smsSetting): RedirectResponse
    {
        $this->authorize('manage', $smsSetting);

        $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $result = $this->sms->sendTestSms($smsSetting, $request->string('phone')->toString());

        return back()->with($result['success'] ? 'status' : 'error', $result['message']);
    }

    public function activate(Request $request, IntegrationSmsSetting $smsSetting): RedirectResponse
    {
        $this->authorize('manage', $smsSetting);

        $this->sms->activate($smsSetting, $request->user()->id);

        return back()->with('status', __('SMS provider activated.'));
    }

    public function deactivate(Request $request, IntegrationSmsSetting $smsSetting): RedirectResponse
    {
        $this->authorize('manage', $smsSetting);

        $this->sms->deactivate($smsSetting, $request->user()->id);

        return back()->with('status', __('SMS provider deactivated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return ['providers' => IntegrationSmsProvider::cases()];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateSetting(Request $request): array
    {
        return $request->validate([
            'provider' => ['required', Rule::enum(IntegrationSmsProvider::class)],
            'api_url' => ['nullable', 'url', 'max:500'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'sender_id' => ['nullable', 'string', 'max:20'],
            'username' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'max:500'],
            'callback_url' => ['nullable', 'url', 'max:500'],
        ]);
    }
}
