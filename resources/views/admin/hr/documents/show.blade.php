<x-admin-layout :title="$document->title" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Documents'), 'url' => route('admin.hr.documents.dashboard')], ['label' => $document->title]]">
    <x-admin.page-header :title="$document->title" :description="$document->employee->full_name.' · '.$document->category->label()">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.documents.download', $document) }}" class="erp-btn-primary">{{ __('Download current') }}</a>
            <a href="{{ route('admin.hr.documents.index') }}" class="erp-btn-secondary">{{ __('Back') }}</a>
            @can('delete', $document)
                <form method="POST" action="{{ route('admin.hr.documents.destroy', $document) }}" onsubmit="return confirm(@js(__('Delete this document and all versions?')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="erp-btn-secondary text-rose-700">{{ __('Delete') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-1" :title="__('Details')">
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Employee') }}</dt>
                    <dd class="font-medium">{{ $document->employee->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Category') }}</dt>
                    <dd>{{ $document->category->label() }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Current Version') }}</dt>
                    <dd>v{{ $document->current_version }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Expiry') }}</dt>
                    <dd>
                        @if ($document->expires_at)
                            {{ $document->expires_at->format('Y-m-d') }}
                            @if ($document->isExpired())
                                <span class="ml-1 rounded-full bg-rose-100 px-2 py-0.5 text-xs text-rose-700">{{ __('Expired') }}</span>
                            @elseif ($document->isExpiringSoon())
                                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">{{ __('Renewal due') }}</span>
                            @endif
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if ($document->description)
                    <div>
                        <dt class="text-slate-500">{{ __('Description') }}</dt>
                        <dd>{{ $document->description }}</dd>
                    </div>
                @endif
            </dl>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2" :title="__('Version History')">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3">{{ __('Version') }}</th>
                            <th class="py-2 pr-3">{{ __('File') }}</th>
                            <th class="py-2 pr-3">{{ __('Uploaded By') }}</th>
                            <th class="py-2 pr-3">{{ __('Date') }}</th>
                            <th class="py-2">{{ __('Notes') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($document->versions as $version)
                            <tr class="border-b border-slate-100">
                                <td class="py-2 pr-3">
                                    v{{ $version->version_number }}
                                    @if ($version->version_number === $document->current_version)
                                        <span class="ml-1 text-xs text-emerald-600">({{ __('current') }})</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-3">{{ $version->original_name }}</td>
                                <td class="py-2 pr-3">{{ $version->uploadedBy?->name ?? '—' }}</td>
                                <td class="py-2 pr-3">{{ $version->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="py-2 pr-3">{{ $version->notes ?? '—' }}</td>
                                <td class="py-2 text-right">
                                    <a href="{{ route('admin.hr.documents.version.download', ['employeeDocument' => $document, 'employeeDocumentVersion' => $version]) }}" class="erp-btn-secondary text-xs">{{ __('Download') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    @can('upload', $document)
        <x-admin.card class="mt-4" :title="__('Upload New Version')">
            <form method="POST" action="{{ route('admin.hr.documents.upload', $document) }}" enctype="multipart/form-data" class="max-w-2xl">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="erp-label" for="file">{{ __('File') }}</label>
                        <input id="file" type="file" name="file" class="erp-input w-full" required>
                        @error('file')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="erp-label" for="notes">{{ __('Version Notes') }}</label>
                        <input id="notes" type="text" name="notes" class="erp-input w-full" placeholder="{{ __('What changed in this version?') }}">
                    </div>
                </div>
                <button type="submit" class="erp-btn-primary mt-4">{{ __('Upload version') }}</button>
            </form>
        </x-admin.card>
    @endcan
</x-admin-layout>
