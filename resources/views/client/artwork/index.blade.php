<x-layouts.client :title="__('Artwork')" :heading="__('Artwork')">
    <section class="client-panel client-panel--flush">
        <div class="client-panel__head">
            <div class="client-panel__title-wrap">
                <span class="client-panel__icon"><x-client.icon name="palette" class="h-4 w-4" /></span>
                <h2 class="client-panel__title">{{ __('Upload artwork') }}</h2>
            </div>
        </div>
        <form method="POST" action="{{ route('client.artwork-library.store') }}" enctype="multipart/form-data" class="client-upload-form">
            @csrf
            <div class="client-upload-form__grid">
                <div>
                    <label for="artwork_name" class="client-label">{{ __('Name') }}</label>
                    <input id="artwork_name" type="text" name="artwork_name" class="client-input" value="{{ old('artwork_name') }}" required>
                </div>
                <div>
                    <label for="file" class="client-label">{{ __('File') }}</label>
                    <input id="file" type="file" name="file" class="client-input" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                </div>
            </div>
            <button type="submit" class="client-btn">{{ __('Upload') }}</button>
        </form>
    </section>

    <section class="client-panel client-panel--flush">
        <div class="client-panel__head">
            <div class="client-panel__title-wrap">
                <h2 class="client-panel__title">{{ __('Your files') }}</h2>
            </div>
        </div>
        <div class="client-table-wrap">
            <table class="client-table">
                <thead>
                    <tr>
                        <th>{{ __('Artwork') }}</th>
                        <th>{{ __('Version') }}</th>
                        <th>{{ __('Uploaded') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($libraryArtworks as $artwork)
                        <tr>
                            <td>{{ $artwork->artwork_name }}</td>
                            <td>{{ $artwork->versionLabel() }}</td>
                            <td>{{ $artwork->uploaded_at?->format('M j, Y') ?: '—' }}</td>
                            <td class="client-table__actions">
                                @if ($artwork->isPreviewable())
                                    <a href="{{ route('client.artwork-library.preview', $artwork) }}" target="_blank" rel="noopener" class="client-link">{{ __('View') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="client-empty">{{ __('No files yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($requests->isNotEmpty())
        <section class="client-panel client-panel--flush">
            <div class="client-panel__head">
                <div class="client-panel__title-wrap">
                    <h2 class="client-panel__title">{{ __('Artwork requests') }}</h2>
                </div>
            </div>
            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>{{ __('Request') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $request)
                            <tr>
                                <td>{{ $request->request_number }}</td>
                                <td>{{ $request->title }}</td>
                                <td>@include('client.partials.status-badge', ['status' => $request->status])</td>
                                <td><a href="{{ route('client.artwork.show', $request) }}" class="client-link">{{ __('Open') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </section>
    @endif
</x-layouts.client>
