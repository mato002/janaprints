<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Production\PrintProductTemplate;
use App\Models\Production\ProductQcChecklist;
use App\Models\Production\WorkCenter;
use App\Support\Export\TabularExportWriter;
use App\Support\Production\PrintProductTemplatePresenter;
use App\Support\Production\PrintProductTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrintProductTemplateController extends Controller
{
    use ScopesToTenant;

    public function __construct(
        protected PrintProductTemplateService $templates,
        protected PrintProductTemplatePresenter $presenter,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PrintProductTemplate::class);

        return view('admin.production.print-templates.index', [
            'templates' => $this->templates->paginate($request),
            'categories' => \App\Enums\PrintProductTemplateCategory::cases(),
            'filters' => [
                'search' => $request->query('search'),
                'category' => $request->query('category'),
                'active' => $request->query('active'),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PrintProductTemplate::class);

        return view('admin.production.print-templates.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PrintProductTemplate::class);

        ['companyId' => $companyId, 'branchId' => $branchId] = $this->resolveTenantIds();
        $validated = $request->validate($this->templates->validationRules());

        $template = $this->templates->create($companyId, $branchId, $request->user(), $validated);

        return redirect()
            ->route('admin.production.print-templates.show', $template)
            ->with('status', __('Print product template created.'));
    }

    public function show(PrintProductTemplate $printTemplate): View
    {
        $this->authorize('view', $printTemplate);

        $printTemplate->load([
            'defaultPaperInventoryItem:id,item_name,sku',
            'defaultMaterialInventoryItem:id,item_name,sku',
            'preferredWorkCenter:id,name,code',
            'preferredMachineAsset:id,asset_name,asset_number',
        ]);

        return view('admin.production.print-templates.show', [
            'template' => $printTemplate,
            'preview' => $this->presenter->present($printTemplate),
        ]);
    }

    public function edit(PrintProductTemplate $printTemplate): View
    {
        $this->authorize('update', $printTemplate);

        return view('admin.production.print-templates.edit', [
            'template' => $printTemplate,
            ...$this->formMeta(),
        ]);
    }

    public function update(Request $request, PrintProductTemplate $printTemplate): RedirectResponse
    {
        $this->authorize('update', $printTemplate);

        $validated = $request->validate($this->templates->validationRules($printTemplate));
        $this->templates->update($printTemplate, $validated, $request->user());

        return redirect()
            ->route('admin.production.print-templates.show', $printTemplate)
            ->with('status', __('Print product template updated.'));
    }

    public function duplicate(Request $request, PrintProductTemplate $printTemplate): RedirectResponse
    {
        $this->authorize('duplicate', $printTemplate);

        $copy = $this->templates->duplicate($printTemplate, $request->user());

        return redirect()
            ->route('admin.production.print-templates.edit', $copy)
            ->with('status', __('Template duplicated. Review and activate when ready.'));
    }

    public function toggleActive(Request $request, PrintProductTemplate $printTemplate): RedirectResponse
    {
        $this->authorize('update', $printTemplate);

        $this->templates->toggleActive($printTemplate, $request->user());

        return back()->with('status', $printTemplate->fresh()->is_active
            ? __('Template activated.')
            : __('Template deactivated.'));
    }

    public function export(Request $request, TabularExportWriter $writer): StreamedResponse
    {
        $this->authorize('viewAny', PrintProductTemplate::class);

        $rows = collect($this->templates->exportRows($request));

        return $writer->download(
            $request->input('format', 'csv'),
            'print-product-templates-'.now()->format('Y-m-d'),
            [__('Code'), __('Name'), __('Category'), __('Production type'), __('GSM'), __('Finished size'), __('Active')],
            $rows,
            __('Print Product Templates'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(): array
    {
        return [
            'categories' => \App\Enums\PrintProductTemplateCategory::cases(),
            'productionTypes' => \App\Enums\ProductionType::cases(),
            'paperItems' => \App\Models\Inventory\InventoryItem::query()
                ->forTenant()->where('is_active', true)
                ->whereHas('category', fn ($q) => $q->where('code', 'PAPER'))
                ->orderBy('item_name')->get(['id', 'item_name', 'sku']),
            'materialItems' => \App\Models\Inventory\InventoryItem::query()
                ->forTenant()->where('is_active', true)
                ->orderBy('item_name')->limit(200)->get(['id', 'item_name', 'sku']),
            'workCenters' => WorkCenter::query()->forTenant()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'qcChecklists' => ProductQcChecklist::query()->forTenant()->where('is_active', true)->with('finishedItem:id,item_name')->get(),
        ];
    }

    /**
     * @return array{companyId: int, branchId: int}
     */
    protected function resolveTenantIds(): array
    {
        return [
            'companyId' => (int) tenant()->companyId(),
            'branchId' => (int) tenant()->branchId(),
        ];
    }
}
