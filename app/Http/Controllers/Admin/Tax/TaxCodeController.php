<?php

namespace App\Http\Controllers\Admin\Tax;

use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Admin\Concerns\ResolvesEntityCode;
use App\Http\Controllers\Controller;
use App\Models\Tax\TaxCategory;
use App\Models\Tax\TaxCode;
use App\Support\Tax\TaxCodeManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxCodeController extends Controller
{
    use ResolvesAccountingTenant;
    use ResolvesEntityCode;

    public function __construct(
        protected TaxCodeManagementService $taxCodes,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', TaxCode::class);

        $codes = TaxCode::query()
            ->forTenant()
            ->with(['category', 'rates'])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        return view('admin.tax.codes.index', compact('codes'));
    }

    public function create(): View
    {
        $this->authorize('create', TaxCode::class);

        return view('admin.tax.codes.create', [
            'categories' => TaxCategory::query()->forTenant()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TaxCode::class);

        ['companyId' => $companyId] = $this->tenantIds();
        $validated = $this->validateCode($request, $companyId);

        $code = $this->taxCodes->createCode($companyId, $validated, (int) auth()->id());

        return redirect()
            ->route('admin.tax.codes.show', $code)
            ->with('status', __('Tax code created.'));
    }

    public function show(TaxCode $taxCode): View
    {
        $this->authorize('view', $taxCode);

        $taxCode->load(['category', 'rates']);

        return view('admin.tax.codes.show', compact('taxCode'));
    }

    public function edit(TaxCode $taxCode): View
    {
        $this->authorize('update', $taxCode);

        return view('admin.tax.codes.edit', [
            'taxCode' => $taxCode,
            'categories' => TaxCategory::query()->forTenant()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, TaxCode $taxCode): RedirectResponse
    {
        $this->authorize('update', $taxCode);

        $validated = $this->validateCode($request, $taxCode->company_id, $taxCode);
        $this->taxCodes->updateCode($taxCode, $validated, (int) auth()->id());

        return redirect()
            ->route('admin.tax.codes.show', $taxCode)
            ->with('status', __('Tax code updated.'));
    }

    public function storeRate(Request $request, TaxCode $taxCode): RedirectResponse
    {
        $this->authorize('update', $taxCode);

        $validated = $request->validate([
            'rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'effective_from' => ['required', 'date'],
        ]);

        $this->taxCodes->addRate(
            $taxCode,
            (float) $validated['rate_percent'],
            $validated['effective_from'],
            (int) auth()->id(),
        );

        return back()->with('status', __('Tax rate added.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateCode(Request $request, int $companyId, ?TaxCode $existing = null): array
    {
        $validated = $request->validate([
            'tax_category_id' => [
                'required',
                Rule::exists('tax_categories', 'id')->where('company_id', $companyId),
            ],
            'code' => array_merge(
                $this->nullableCodeRules(30),
                [
                    Rule::unique('tax_codes', 'code')
                        ->where('company_id', $companyId)
                        ->ignore($existing?->id),
                ],
            ),
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'rate_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'effective_from' => ['nullable', 'date', 'required_with:rate_percent'],
        ]);

        $validated['code'] = $this->resolveCompanyScopedCode(
            $request,
            'name',
            TaxCode::class,
            $companyId,
            $existing?->id,
            30,
        );

        return $validated;
    }
}
