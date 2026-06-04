<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryItemImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InventoryItemImageController extends Controller
{
    public function store(Request $request, InventoryItem $item): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($item);

        $validated = $request->validate([
            'images' => ['required', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'primary' => ['nullable', 'boolean'],
        ]);

        foreach ($request->file('images', []) as $image) {
            $path = $image->store("catalogue/items/{$item->id}", 'public');
            $isPrimary = (bool) ($validated['primary'] ?? false) || ! $item->images()->exists();

            if ($isPrimary) {
                $item->images()->update(['is_primary' => false]);
            }

            $item->images()->create([
                'path' => $path,
                'thumbnail_path' => $path,
                'is_primary' => $isPrimary,
            ]);
        }

        return back()->with('status', __('Image uploaded.'));
    }

    public function primary(InventoryItem $item, InventoryItemImage $image): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($item);
        abort_unless($image->inventory_item_id === $item->id, 404);

        $item->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('status', __('Primary image updated.'));
    }

    public function destroy(InventoryItem $item, InventoryItemImage $image): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalogue.edit'), 403);
        $this->ensureTenant($item);
        abort_unless($image->inventory_item_id === $item->id, 404);

        Storage::disk('public')->delete([$image->path, $image->thumbnail_path]);
        $image->delete();

        if (! $item->images()->where('is_primary', true)->exists()) {
            $item->images()->oldest()->first()?->update(['is_primary' => true]);
        }

        return back()->with('status', __('Image removed.'));
    }

    protected function ensureTenant(InventoryItem $item): void
    {
        abort_unless($item->company_id === tenant()->companyId() && $item->branch_id === tenant()->branchId(), 404);
    }
}
