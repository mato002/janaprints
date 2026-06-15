<?php

namespace App\Http\Controllers\Admin\Documents;

use App\Http\Controllers\Controller;
use App\Models\DocumentSetting;
use App\Services\Documents\DocumentContentBaselineService;
use App\Services\Documents\DocumentSettingsService;
use App\Support\Navigation\WorkspaceEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentSettingsController extends Controller
{
    public function __construct(
        protected DocumentSettingsService $settings,
        protected DocumentContentBaselineService $baseline,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', DocumentSetting::class);

        $company = tenant()->company ?? abort(404, __('Select a company context first.'));
        $this->baseline->seed($company->id);

        $records = DocumentSetting::query()
            ->where('company_id', $company->id)
            ->orderBy('key')
            ->get()
            ->keyBy('key');

        return view('admin.documents.settings.form', [
            'title' => __('Commercial Document Settings'),
            'description' => __('Manage company details, payment instructions, terms, and footer text on quotations, invoices, and receipts.'),
            'company' => $company,
            'schema' => $this->settings->schema(),
            'records' => $records,
            'adminTabs' => config('document_cms.settings_admin_tabs', []),
            'updateRoute' => route('admin.documents.settings.update'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', DocumentSetting::class);

        $company = tenant()->company ?? abort(404, __('Select a company context first.'));
        $schema = $this->settings->schema();
        $values = $this->validatedSettings($request, $schema);

        $this->settings->updateSettings($values, $company->id, auth()->id());

        return redirect()
            ->to(WorkspaceEmbed::url(route('admin.documents.settings.index')))
            ->with('status', __('Commercial document settings saved.'));
    }

    public function resetSetting(Request $request, string $key): RedirectResponse
    {
        $this->authorize('update', DocumentSetting::class);

        $schema = config('document_cms.settings', []);

        if (! isset($schema[$key])) {
            abort(404);
        }

        $company = tenant()->company ?? abort(404, __('Select a company context first.'));
        $this->settings->clearValue($key, $company->id);

        return redirect()
            ->to(WorkspaceEmbed::url(route('admin.documents.settings.index')))
            ->with('status', __('Setting reset to config fallback.'));
    }

    /**
     * @param  array<string, array<string, mixed>>  $schema
     * @return array<string, mixed>
     */
    protected function validatedSettings(Request $request, array $schema): array
    {
        $rules = [];

        foreach ($schema as $key => $meta) {
            $field = str_replace('.', '_', $key);
            $optional = (bool) ($meta['optional'] ?? false);

            $rules[$field] = match ($meta['type']) {
                'email' => $optional ? ['nullable', 'email', 'max:255'] : ['required', 'email', 'max:255'],
                'phone' => $optional ? ['nullable', 'string', 'max:30'] : ['required', 'string', 'max:30'],
                'boolean' => ['sometimes', 'boolean'],
                default => $optional ? ['nullable', 'string', 'max:5000'] : ['required', 'string', 'max:5000'],
            };
        }

        $validated = $request->validate($rules);
        $values = [];

        foreach ($schema as $key => $meta) {
            $field = str_replace('.', '_', $key);

            if ($meta['type'] === 'boolean') {
                $values[$key] = $request->boolean($field);

                continue;
            }

            $values[$key] = $validated[$field] ?? '';
        }

        return $values;
    }
}
