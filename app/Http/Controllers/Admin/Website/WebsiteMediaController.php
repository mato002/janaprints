<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\WebsiteMediaItem;
use App\Services\Website\WebsiteMediaResolver;
use App\Services\Website\WebsiteMediaService;
use App\Support\Website\WebsiteMediaSlotUsage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteMediaController extends Controller
{
    public function __construct(
        protected WebsiteMediaService $media,
        protected WebsiteMediaResolver $resolver,
        protected WebsiteMediaSlotUsage $usage,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WebsiteMediaItem::class);

        $items = $this->media->syncRegistrySlots();
        $sections = $this->media->sectionLabels();
        $filters = $request->only(['q', 'section', 'status', 'source']);
        $activeSection = $request->string('section')->toString();

        if ($activeSection !== '') {
            $items = $this->media->filterBySection($items, $activeSection);
        }

        $items = $this->media->filterItems($items, $filters);

        return view('admin.website.media.index', [
            'items' => $items,
            'sections' => $sections,
            'filters' => $filters,
            'activeSection' => $activeSection,
            'summary' => $this->media->summaryCounts($items),
            'usage' => $this->usage,
        ]);
    }

    public function edit(WebsiteMediaItem $websiteMediaItem): View
    {
        $this->authorize('update', $websiteMediaItem);

        return view('admin.website.media.form', [
            'item' => $websiteMediaItem,
            'sections' => $this->media->sectionLabels(),
            'usageLabel' => $this->usage->labelFor($websiteMediaItem->slot_key, $websiteMediaItem->section),
            'sourceStatus' => $websiteMediaItem->sourceStatus(),
        ]);
    }

    public function update(Request $request, WebsiteMediaItem $websiteMediaItem): RedirectResponse
    {
        $this->authorize('update', $websiteMediaItem);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:160'],
            'alt_text' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $data['sort_order'] = (int) ($data['sort_order'] ?? $websiteMediaItem->sort_order);
        $data['is_active'] = $request->boolean('is_active', $websiteMediaItem->is_active);
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $this->media->deleteStoredImage($websiteMediaItem->image_path);
            $data['image_path'] = $this->media->storeImage($request->file('image'));
        }

        $websiteMediaItem->update($data);
        $this->resolver->clearCache();

        return redirect()
            ->route('admin.website.media.index', ['section' => $websiteMediaItem->section])
            ->with('status', __('Media slot updated.'));
    }

    public function toggleActive(WebsiteMediaItem $websiteMediaItem): RedirectResponse
    {
        $this->authorize('update', $websiteMediaItem);

        $websiteMediaItem->update([
            'is_active' => ! $websiteMediaItem->is_active,
            'updated_by' => auth()->id(),
        ]);
        $this->resolver->clearCache();

        return redirect()
            ->route('admin.website.media.index', ['section' => $websiteMediaItem->section])
            ->with('status', $websiteMediaItem->is_active
                ? __('Media slot activated.')
                : __('Media slot deactivated — storefront will use config fallback.'));
    }

    public function resetImage(WebsiteMediaItem $websiteMediaItem): RedirectResponse
    {
        $this->authorize('update', $websiteMediaItem);

        if ($websiteMediaItem->image_path) {
            $this->media->deleteStoredImage($websiteMediaItem->image_path);
            $websiteMediaItem->update([
                'image_path' => null,
                'updated_by' => auth()->id(),
            ]);
            $this->resolver->clearCache();
        }

        return redirect()
            ->back()
            ->with('status', __('Uploaded image removed. Storefront will use the config fallback for this slot.'));
    }

    public function removeImage(Request $request, WebsiteMediaItem $websiteMediaItem): RedirectResponse
    {
        $this->authorize('update', $websiteMediaItem);

        $request->validate([
            'confirm' => ['accepted'],
        ]);

        return $this->resetImage($websiteMediaItem);
    }
}
