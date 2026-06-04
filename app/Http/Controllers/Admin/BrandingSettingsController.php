<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform\SettingsGovernance;
use App\Support\Branding\BrandingAssets;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandingSettingsController extends Controller
{
    public function __construct(
        protected BrandingAssets $assets,
    ) {}

    public function edit(): View
    {
        $this->authorize('viewAny', SettingsGovernance::class);

        $company = tenant()->company ?? abort(404, __('Select a company context first.'));

        return view('admin.settings.branding', [
            'company' => $company,
            'logoUrl' => $this->assets->url($company->logo),
            'faviconUrl' => $this->assets->url($company->favicon_path),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('update', new SettingsGovernance());

        $company = tenant()->company ?? abort(404);

        $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,svg', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_logo')) {
            $this->assets->delete($company->logo);
            $company->logo = null;
        }

        if ($request->boolean('remove_favicon')) {
            $this->assets->delete($company->favicon_path);
            $company->favicon_path = null;
        }

        if ($request->hasFile('logo')) {
            $company->logo = $this->assets->storeCompanyLogo($company, $request->file('logo'));
        }

        if ($request->hasFile('favicon')) {
            $company->favicon_path = $this->assets->storeCompanyFavicon($company, $request->file('favicon'));
        }

        $company->save();

        return redirect()
            ->route('admin.settings.branding.edit')
            ->with('status', __('Branding assets updated.'));
    }
}
