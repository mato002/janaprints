<?php

namespace App\Http\Controllers\Admin\Website;

use App\Enums\WebsiteGalleryCategory;
use App\Http\Controllers\Controller;
use App\Models\WebsiteGalleryItem;
use App\Services\Storefront\WebsiteGalleryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebsiteGalleryController extends Controller
{
    public function __construct(
        protected WebsiteGalleryService $gallery,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', WebsiteGalleryItem::class);

        $items = WebsiteGalleryItem::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('location', 'like', $term);
                });
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('published'), function ($q) use ($request) {
                $q->where('is_published', $request->string('published') === '1');
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.website.gallery.index', [
            'items' => $items,
            'filters' => $request->only(['q', 'category', 'published']),
            'categories' => WebsiteGalleryCategory::options(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', WebsiteGalleryItem::class);

        return view('admin.website.gallery.form', [
            'item' => new WebsiteGalleryItem([
                'is_published' => true,
                'is_featured' => false,
                'sort_order' => 0,
            ]),
            'categories' => WebsiteGalleryCategory::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WebsiteGalleryItem::class);

        $data = $this->validated($request);
        $data['slug'] = $this->gallery->uniqueSlug($data['title']);
        $data['image_path'] = $this->gallery->storeImage($request->file('image'));
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        WebsiteGalleryItem::query()->create($data);

        return redirect()
            ->route('admin.website.gallery.index')
            ->with('status', __('Gallery item created.'));
    }

    public function edit(WebsiteGalleryItem $websiteGalleryItem): View
    {
        $this->authorize('update', $websiteGalleryItem);

        return view('admin.website.gallery.form', [
            'item' => $websiteGalleryItem,
            'categories' => WebsiteGalleryCategory::options(),
        ]);
    }

    public function update(Request $request, WebsiteGalleryItem $websiteGalleryItem): RedirectResponse
    {
        $this->authorize('update', $websiteGalleryItem);

        $data = $this->validated($request, $websiteGalleryItem);
        $data['slug'] = $this->gallery->uniqueSlug($data['title'], $websiteGalleryItem->id);
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $this->gallery->deleteStoredImage($websiteGalleryItem->image_path);
            $data['image_path'] = $this->gallery->storeImage($request->file('image'));
        }

        $websiteGalleryItem->update($data);

        return redirect()
            ->route('admin.website.gallery.index')
            ->with('status', __('Gallery item updated.'));
    }

    public function destroy(WebsiteGalleryItem $websiteGalleryItem): RedirectResponse
    {
        $this->authorize('delete', $websiteGalleryItem);

        $this->gallery->deleteStoredImage($websiteGalleryItem->image_path);
        $websiteGalleryItem->delete();

        return redirect()
            ->route('admin.website.gallery.index')
            ->with('status', __('Gallery item deleted.'));
    }

    public function reorder(Request $request): RedirectResponse
    {
        $this->authorize('update', WebsiteGalleryItem::class);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:website_gallery_items,id'],
        ]);

        foreach ($request->input('order', []) as $position => $id) {
            WebsiteGalleryItem::query()
                ->whereKey($id)
                ->update(['sort_order' => $position + 1]);
        }

        return redirect()
            ->route('admin.website.gallery.index')
            ->with('status', __('Featured order updated.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?WebsiteGalleryItem $item = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', Rule::enum(WebsiteGalleryCategory::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:120'],
            'quantity_label' => ['nullable', 'string', 'max:120'],
            'timeline_label' => ['nullable', 'string', 'max:120'],
            'materials_label' => ['nullable', 'string', 'max:255'],
            'outcome' => ['nullable', 'string', 'max:2000'],
            'alt_text' => ['required', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => [$item ? 'nullable' : 'required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];

        $data = $request->validate($rules);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
