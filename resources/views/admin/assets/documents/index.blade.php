<x-admin-layout
    :title="$asset->asset_number.' — '.__('Documents')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Register'), 'url' => route('admin.assets.index')],
        ['label' => $asset->asset_number, 'url' => route('admin.assets.show', $asset)],
        ['label' => __('Documents')],
    ]"
>
    <x-admin.page-header :title="__('Asset Documents')" :description="$asset->asset_name">
        <x-slot name="actions">
            <a href="{{ route('admin.assets.360.show', $asset) }}" class="erp-btn-secondary">{{ __('Asset 360') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if(auth()->user()?->can('assets.edit') || auth()->user()?->can('assets.manage'))
        <x-admin.card class="mb-4">
            <form method="POST" action="{{ route('admin.assets.documents.store', $asset) }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-4">
                @csrf
                <div>
                    <label class="text-sm text-slate-600">{{ __('Type') }}</label>
                    <select name="document_type" class="erp-input w-full" required>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-slate-600">{{ __('Title') }}</label>
                    <input type="text" name="title" class="erp-input w-full" required>
                </div>
                <div>
                    <label class="text-sm text-slate-600">{{ __('File') }}</label>
                    <input type="file" name="file" class="erp-input w-full" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="erp-btn-primary">{{ __('Upload') }}</button>
                </div>
            </form>
        </x-admin.card>
    @endif

    <x-admin.card>
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Uploaded') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $doc)
                    <tr>
                        <td>{{ $doc->title }}</td>
                        <td>{{ $doc->document_type->label() }}</td>
                        <td>{{ $doc->created_at?->format('Y-m-d') }} — {{ $doc->uploader?->name }}</td>
                        <td>{{ number_format($doc->size / 1024, 1) }} KB</td>
                        <td class="text-right">
                            <a href="{{ route('admin.assets.documents.download', $doc) }}" class="erp-link">{{ __('Download') }}</a>
                            @can('archive', $doc)
                                <form method="POST" action="{{ route('admin.assets.documents.archive', $doc) }}" class="inline">@csrf<button type="submit" class="erp-link text-red-600">{{ __('Archive') }}</button></form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-slate-500">{{ __('No documents uploaded yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
