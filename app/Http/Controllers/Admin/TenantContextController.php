<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $user = $request->user();
        $company = Company::query()->findOrFail($validated['company_id']);

        if (! $user->hasRole('Super Admin') && $user->company_id !== $company->id) {
            abort(403);
        }

        $branch = null;
        if (! empty($validated['branch_id'])) {
            $branch = Branch::query()
                ->where('id', $validated['branch_id'])
                ->where('company_id', $company->id)
                ->firstOrFail();
        }

        session([
            'active_company_id' => $company->id,
            'active_branch_id' => $branch?->id,
        ]);

        return back()->with('status', __('Company context updated.'));
    }
}
