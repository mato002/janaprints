<?php

namespace App\Http\Controllers\Client;

use App\Enums\CustomerArtworkType;
use App\Http\Controllers\Client\Concerns\ResolvesClientCustomer;
use App\Http\Controllers\Controller;
use App\Models\Crm\CustomerArtwork;
use App\Support\Crm\CustomerArtworkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientArtworkLibraryController extends Controller
{
    use ResolvesClientCustomer;

    public function __construct(
        protected CustomerArtworkService $artworks,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $customer = $this->clientCustomer();

        $validated = $request->validate([
            'artwork_name' => ['required', 'string', 'max:255'],
            'artwork_type' => ['nullable', Rule::enum(CustomerArtworkType::class)],
            'file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $this->artworks->uploadVersion(
            $customer,
            $request->file('file'),
            $validated['artwork_name'],
            $validated['artwork_type'] ?? CustomerArtworkType::Other->value,
            (int) $this->clientUser()->id,
        );

        return redirect()
            ->route('client.artwork.index')
            ->with('status', __('Artwork uploaded.'));
    }

    public function preview(CustomerArtwork $libraryArtwork): BinaryFileResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($libraryArtwork, $customer);

        $stream = $this->artworks->streamPreview($libraryArtwork);

        return response()->file($stream['path'], [
            'Content-Type' => $stream['mime'],
            'Content-Disposition' => 'inline; filename="'.addslashes($stream['name']).'"',
        ]);
    }

    public function download(CustomerArtwork $libraryArtwork): StreamedResponse
    {
        $customer = $this->clientCustomer();
        $this->assertClientOwns($libraryArtwork, $customer);

        $stream = $this->artworks->streamPreview($libraryArtwork);

        return response()->streamDownload(
            fn () => readfile($stream['path']),
            $stream['name'],
            ['Content-Type' => $stream['mime']],
        );
    }
}
