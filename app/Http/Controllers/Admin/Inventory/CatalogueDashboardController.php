<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Admin\Inventory\Concerns\ResolvesInventoryTenant;
use App\Http\Controllers\Controller;
use App\Models\Inventory\Brand;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventorySubcategory;
use App\Models\Inventory\PriceList;
use Illuminate\View\View;

class CatalogueDashboardController extends Controller
{
    use ResolvesInventoryTenant;

    public function __invoke(): View
    {
        $this->authorizeCatalogue('catalogue.view');

        $itemsMissingImages = InventoryItem::query()
            ->forTenant()
            ->whereDoesntHave('images')
            ->count();

        $itemsMissingPrices = InventoryItem::query()
            ->forTenant()
            ->whereDoesntHave('priceListItems')
            ->count();

        return view('admin.inventory.catalogue.dashboard', [
            'stats' => [
                'categories' => InventoryCategory::query()->forTenant()->count(),
                'subcategories' => InventorySubcategory::query()->forTenant()->count(),
                'brands' => Brand::query()->forTenant()->count(),
                'items' => InventoryItem::query()->forTenant()->count(),
                'price_lists' => PriceList::query()->forTenant()->count(),
                'missing_images' => $itemsMissingImages,
                'missing_prices' => $itemsMissingPrices,
            ],
        ]);
    }

    protected function authorizeCatalogue(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403);
    }
}
