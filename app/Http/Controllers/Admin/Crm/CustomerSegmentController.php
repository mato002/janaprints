<?php

namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Crm\CustomerSegment;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerSegmentController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', CustomerSegment::class);

        $segments = $this->scopeToTenant(CustomerSegment::query())->latest()->paginate(15);

        return view('admin.crm.segments.index', compact('segments'));
    }

    public function create(): View
    {
        $this->authorize('create', CustomerSegment::class);

        return view('admin.crm.segments.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CustomerSegment::class);

        $companyId = auth()->user()->hasRole('Super Admin')
            ? (int) $request->input('company_id')
            : (int) auth()->user()->company_id;

        if (! auth()->user()->hasRole('Super Admin') && (int) $request->input('company_id') !== $companyId) {
            abort(403);
        }

        $branchId = tenant()->branchId();

        $data = $this->formSettings->validateRequest($request, 'segment.create', [
            'company_id' => ['exists:companies,id'],
            'name' => ['string', 'max:255'],
            'code' => [
                'string', 'max:50',
                Rule::unique('customer_segments', 'code')->where('company_id', $companyId),
            ],
            'description' => ['string'],
            'is_active' => ['boolean'],
        ], $companyId, $branchId, serverProvidedFields: ['company_id']);

        CustomerSegment::query()->create([
            ...$data,
            'company_id' => $companyId,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.crm.segments.index')->with('status', __('Segment created.'));
    }

    public function edit(CustomerSegment $segment): View
    {
        $this->authorize('update', $segment);

        return view('admin.crm.segments.edit', array_merge(
            ['segment' => $segment],
            $this->formMeta($segment),
        ));
    }

    public function update(Request $request, CustomerSegment $segment): RedirectResponse
    {
        $this->authorize('update', $segment);

        $data = $this->formSettings->validateRequest($request, 'segment.create', [
            'name' => ['string', 'max:255'],
            'code' => [
                'string', 'max:50',
                Rule::unique('customer_segments', 'code')->where('company_id', $segment->company_id)->ignore($segment->id),
            ],
            'description' => ['string'],
            'is_active' => ['boolean'],
        ], $segment->company_id, tenant()->branchId());

        $segment->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.crm.segments.index')->with('status', __('Segment updated.'));
    }

    public function destroy(CustomerSegment $segment): RedirectResponse
    {
        $this->authorize('delete', $segment);

        $segment->delete();

        return redirect()->route('admin.crm.segments.index')->with('status', __('Segment deleted.'));
    }

    protected function companies()
    {
        if (auth()->user()->hasRole('Super Admin')) {
            return Company::query()->where('is_active', true)->orderBy('name')->get();
        }

        return Company::query()->where('id', auth()->user()->company_id)->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(?CustomerSegment $segment = null): array
    {
        $companyId = $segment?->company_id ?? tenant()->companyId() ?? auth()->user()->company_id;
        $branchId = tenant()->branchId();

        return [
            'formFields' => $this->formSettings->resolvedFields('segment.create', $companyId, $branchId, $segment),
            'companies' => $this->companies(),
        ];
    }
}
