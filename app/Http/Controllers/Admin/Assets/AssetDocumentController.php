<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetDocumentType;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetDocument;
use App\Models\Assets\FixedAsset;
use App\Policies\AssetDocumentPolicy;
use App\Services\Assets\AssetDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetDocumentController extends Controller
{
    public function __construct(
        protected AssetDocumentService $documents,
    ) {}

    public function index(FixedAsset $asset)
    {
        abort_unless(app(AssetDocumentPolicy::class)->viewAny(auth()->user(), $asset), 403);

        return view('admin.assets.documents.index', [
            'asset' => $asset->load('category'),
            'documents' => $this->documents->listForAsset($asset),
            'types' => AssetDocumentType::cases(),
        ]);
    }

    public function store(Request $request, FixedAsset $asset): RedirectResponse
    {
        abort_unless(app(AssetDocumentPolicy::class)->upload(auth()->user(), $asset), 403);

        $validated = $request->validate([
            'document_type' => ['required', new Enum(AssetDocumentType::class)],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $this->documents->upload(
            $asset,
            $request->file('file'),
            AssetDocumentType::from($validated['document_type']),
            $validated['title'],
            (int) auth()->id(),
        );

        return back()->with('status', __('Document uploaded.'));
    }

    public function download(AssetDocument $document): StreamedResponse
    {
        $this->authorize('view', $document);

        abort_unless(Storage::disk($document->disk)->exists($document->storage_path), 404);

        return Storage::disk($document->disk)->download($document->storage_path, $document->original_name);
    }

    public function archive(AssetDocument $document): RedirectResponse
    {
        $this->authorize('archive', $document);

        $this->documents->archive($document, (int) auth()->id());

        return back()->with('status', __('Document archived.'));
    }
}
