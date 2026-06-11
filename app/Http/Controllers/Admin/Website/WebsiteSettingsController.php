<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSetting;
use App\Services\Website\WebsiteContentBaselineService;
use App\Services\Website\WebsiteSettingsService;
use App\Support\Navigation\WorkspaceEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteSettingsController extends Controller
{
    public function __construct(
        protected WebsiteSettingsService $settings,
        protected WebsiteContentBaselineService $baseline,
    ) {}

    public function footerContact(): View
    {
        $this->authorize('viewAny', WebsiteSetting::class);

        $groups = ['footer', 'contact', 'seo'];
        $this->baseline->seedSettings();
        $records = WebsiteSetting::query()->whereIn('group', $groups)->orderBy('key')->get()->keyBy('key');
        $schema = $this->settings->schemaForGroups($groups, 'footer-contact');

        return view('admin.website.settings.form', [
            'title' => __('Footer & Contact Settings'),
            'description' => __('Manage footer content, contact details, map coordinates, WhatsApp settings, social links, and basic SEO defaults.'),
            'pageKey' => 'footer-contact',
            'groups' => $groups,
            'groupLabels' => config('website_cms.settings_groups', []),
            'adminTabs' => config('website_cms.settings_admin_tabs', []),
            'schema' => $schema,
            'records' => $records,
            'updateRoute' => route('admin.website.settings.footer-contact.update'),
        ]);
    }

    public function updateFooterContact(Request $request): RedirectResponse
    {
        $this->authorize('update', WebsiteSetting::class);

        $groups = ['footer', 'contact', 'seo'];
        $schema = $this->settings->schemaForGroups($groups, 'footer-contact');
        $values = $this->normalizeTechnicalContactLinks(
            $this->validatedSettings($request, $schema),
        );

        $this->settings->updateGroupSettings($groups, $values, auth()->id());

        return redirect()
            ->to(WorkspaceEmbed::url(route('admin.website.settings.footer-contact')))
            ->with('status', __('Footer and contact settings saved.'));
    }

    public function seoGlobal(): View
    {
        $this->authorize('viewAny', WebsiteSetting::class);

        $groups = ['seo'];
        $this->baseline->seedSettings();
        $records = WebsiteSetting::query()->whereIn('group', $groups)->orderBy('key')->get()->keyBy('key');
        $schema = $this->settings->schemaForGroups($groups, 'seo-global');

        return view('admin.website.settings.form', [
            'title' => __('SEO & Global Settings'),
            'description' => __('Manage site name, tagline, and default SEO metadata for the public storefront.'),
            'pageKey' => 'seo-global',
            'groups' => $groups,
            'groupLabels' => config('website_cms.settings_groups', []),
            'adminTabs' => [],
            'schema' => $schema,
            'records' => $records,
            'updateRoute' => route('admin.website.settings.seo-global.update'),
        ]);
    }

    public function updateSeoGlobal(Request $request): RedirectResponse
    {
        $this->authorize('update', WebsiteSetting::class);

        $groups = ['seo'];
        $schema = $this->settings->schemaForGroups($groups, 'seo-global');
        $values = $this->validatedSettings($request, $schema);

        $this->settings->updateGroupSettings($groups, $values, auth()->id());

        return redirect()
            ->to(WorkspaceEmbed::url(route('admin.website.settings.seo-global')))
            ->with('status', __('SEO and global settings saved.'));
    }

    public function resetSetting(Request $request, string $key): RedirectResponse
    {
        $this->authorize('update', WebsiteSetting::class);

        $schema = config('website_cms.settings', []);

        if (! isset($schema[$key])) {
            abort(404);
        }

        $this->settings->clearValue($key);

        $group = $schema[$key]['group'] ?? '';
        $redirectRoute = in_array($group, ['footer', 'contact'], true)
            ? route('admin.website.settings.footer-contact')
            : route('admin.website.settings.seo-global');

        return redirect()
            ->to(WorkspaceEmbed::url($redirectRoute))
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
                'url' => $optional ? ['nullable', 'url', 'max:2000'] : ['required', 'url', 'max:2000'],
                'email' => $optional ? ['nullable', 'email', 'max:255'] : ['required', 'email', 'max:255'],
                'phone' => $optional ? ['nullable', 'string', 'max:30'] : ['required', 'string', 'max:30'],
                'json' => in_array($key, ['footer.nav', 'footer.trust_badges'], true)
                    ? ['required', 'array']
                    : ($key === 'footer.social'
                        ? ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                            if ($value === null || $value === '') {
                                return;
                            }
                            try {
                                json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
                            } catch (\JsonException) {
                                $fail(__('The :attribute field must contain valid JSON.'));
                            }
                        }]
                        : ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                            try {
                                json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
                            } catch (\JsonException) {
                                $fail(__('The :attribute field must contain valid JSON.'));
                            }
                        }]),
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

            $raw = $validated[$field] ?? null;

            if ($meta['type'] === 'json') {
                $input = $request->input($field);

                if ($key === 'footer.social' && ($input === null || $input === '')) {
                    continue;
                }

                $values[$key] = is_array($input)
                    ? $input
                    : json_decode((string) $raw, true, 512, JSON_THROW_ON_ERROR);
            } else {
                $values[$key] = $raw ?? '';
            }
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    protected function normalizeTechnicalContactLinks(array $values): array
    {
        $phone = trim((string) ($values['contact.phone'] ?? ''));

        if ($phone !== '') {
            $phoneHref = trim((string) ($values['contact.phone_href'] ?? ''));

            if ($phoneHref === '') {
                $digits = preg_replace('/[^\d+]/', '', $phone) ?? '';
                $values['contact.phone_href'] = $digits !== '' ? 'tel:'.ltrim($digits, '+') : '';
            }
        }

        $email = trim((string) ($values['contact.email'] ?? ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailHref = trim((string) ($values['contact.email_href'] ?? ''));

            if ($emailHref === '') {
                $values['contact.email_href'] = 'mailto:'.$email;
            }
        }

        return $values;
    }
}
