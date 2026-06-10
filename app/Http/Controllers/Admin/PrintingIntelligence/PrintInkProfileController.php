<?php

namespace App\Http\Controllers\Admin\PrintingIntelligence;

use App\Enums\PrintInkType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PrintingIntelligence\StorePrintInkProfileRequest;
use App\Http\Requests\Admin\PrintingIntelligence\UpdatePrintInkProfileRequest;
use App\Models\Inventory\InventoryItem;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Services\PrintingIntelligence\PrintInkProfileManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrintInkProfileController extends Controller
{
    public function __construct(
        protected PrintInkProfileManagementService $profiles,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', PrintInkProfile::class);

        [$companyId] = $this->tenantScope();

        $profiles = PrintInkProfile::query()
            ->where('company_id', $companyId)
            ->with('inventoryItem')
            ->orderBy('name')
            ->get()
            ->map(fn (PrintInkProfile $profile) => $this->profiles->displayRow($profile));

        return view('admin.printing-intelligence.ink-profiles.index', [
            'profiles' => $profiles,
            'inkTypes' => PrintInkType::cases(),
            'inventoryItems' => $this->inventoryItemOptions($companyId),
            'canManage' => auth()->user()?->can('printing.ink-profiles.manage') ?? false,
        ]);
    }

    public function store(StorePrintInkProfileRequest $request): RedirectResponse
    {
        $this->authorize('create', PrintInkProfile::class);

        [$companyId] = $this->tenantScope();
        $this->profiles->create($companyId, $request->payload());

        return redirect()
            ->route('admin.printing-intelligence.ink-profiles.index')
            ->with('status', __('Ink profile created.'));
    }

    public function update(UpdatePrintInkProfileRequest $request, PrintInkProfile $profile): RedirectResponse
    {
        $this->authorize('update', $profile);

        $this->profiles->update($profile, $request->payload());

        return redirect()
            ->route('admin.printing-intelligence.ink-profiles.index')
            ->with('status', __('Ink profile updated.'));
    }

    public function destroy(PrintInkProfile $profile): RedirectResponse
    {
        $this->authorize('delete', $profile);

        if ($this->profiles->isUsedByEstimates($profile)) {
            $this->profiles->deactivate($profile);

            return redirect()
                ->route('admin.printing-intelligence.ink-profiles.index')
                ->with('status', __('Ink profile deactivated because it is referenced by ink estimates.'));
        }

        $profile->delete();

        return redirect()
            ->route('admin.printing-intelligence.ink-profiles.index')
            ->with('status', __('Ink profile removed.'));
    }

    /**
     * @return list<array{id: int, label: string}>
     */
    protected function inventoryItemOptions(int $companyId): array
    {
        return InventoryItem::query()
            ->where('company_id', $companyId)
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'item_code'])
            ->map(fn (InventoryItem $item) => [
                'id' => $item->id,
                'label' => trim($item->item_name.' ('.$item->item_code.')'),
            ])
            ->all();
    }

    /**
     * @return array{0: int}
     */
    protected function tenantScope(): array
    {
        return [(int) (tenant()->companyId() ?? auth()->user()?->company_id)];
    }
}
